<?php
require_once __DIR__ . '/ExporterInterface.php';
require_once __DIR__ . '/JsonExporter.php';

class CsvFromJsonAdapter implements ExporterInterface {
    /**
     * Cabeçalho padrão usado quando recebemos uma lista simples (ex.: [1,2,3]).
     */
    private const DEFAULT_SCALAR_KEY = 'valor';

    private $jsonExporter;

    public function __construct(JsonExporter $jsonExporter) {
        $this->jsonExporter = $jsonExporter;
    }

    public function export(array $data) {
        // Convertemos os dados para JSON utilizando o exportador existente...
        $json = $this->jsonExporter->export($data);
        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            $decoded = [];
        }

        // ...e então normalizamos para uma matriz tabular pronta para o CSV.
        $sections = $this->normalize($decoded);
        if (empty($sections)) {
            return '';
        }

        $fp = fopen('php://temp', 'r+');
        fwrite($fp, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM

        // Cada seção possui seu próprio conjunto de colunas. Reiniciamos o cabeçalho
        // para evitar "empurrar" dados de outra entidade para a direita.
        foreach ($sections as $section) {
            if (empty($section['rows'])) { continue; }
            $headers = $section['headers'];
            fputcsv($fp, $headers, ';');
            foreach ($section['rows'] as $row) {
                $ordered = [];
                foreach ($headers as $h) {
                    $value = $row[$h] ?? '';
                    if (is_array($value) || is_object($value)) {
                        $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                    }
                    $ordered[] = $value;
                }
                fputcsv($fp, $ordered, ';');
            }
            // Adicionamos uma linha em branco simples (quebra de linha) para separar seções.
            fwrite($fp, "\n");
        }

        rewind($fp);
        $csv = stream_get_contents($fp);
        fclose($fp);

        if ($csv === false) {
            return '';
        }

        // Excel no Windows espera \r\n ao final de cada linha; ajustamos as que
        // ficaram apenas com \n para garantir que cada registro abra na linha correta.
        $csv = preg_replace("/(?<!\r)\n/", "\r\n", $csv);

        return $csv ?: '';
    }

    public function contentType() { return 'text/csv; charset=utf-8'; }
    public function extension() { return 'csv'; }

    /**
     * Normaliza o array decodificado para uma estrutura bidimensional, mantendo
     * um registro por linha do CSV.
     */
    private function normalize($decoded) {
        if (!is_array($decoded)) {
            $decoded = [$decoded];
        }

        // Quando o JSON é um objeto na raiz, empacotamos em um array para
        // processar como uma única linha.
        if (!$this->isList($decoded)) {
            $decoded = [$decoded];
        }

        $flatRows = [];
        foreach ($decoded as $row) {
            $this->expandRow($row, $flatRows);
        }

        if (empty($flatRows)) {
            return [];
        }

        // Agrupamos por assinatura de colunas (ordem definida alfabeticamente) para
        // permitir múltiplos blocos independentes no CSV final.
        $groups = [];
        foreach ($flatRows as $r) {
            ksort($r);
            $signature = implode('|', array_keys($r));
            if (!isset($groups[$signature])) {
                $groups[$signature] = [
                    'headers' => array_keys($r),
                    'rows' => []
                ];
            }
            $groups[$signature]['rows'][] = $r;
        }

        // Reordenar grupos para estabilidade (opcional: manter ordem de descoberta)
        return array_values($groups);
    }

    /**
     * Expande uma estrutura possivelmente aninhada em múltiplas linhas lógicas.
     * Regras:
     * 1. Lista de escalares => uma coluna, várias linhas.
     * 2. Lista de objetos homogêneos => cada objeto vira uma linha (colunas combinadas).
     * 3. Objeto com propriedades escalares + listas => produto cartesiano NÃO é gerado;
     *    cada lista homogênea de objetos gera suas próprias linhas mesclando os campos de nível superior.
     * 4. Objeto simples => uma linha (flatten).
     */
    private function expandRow($row, array &$collector, $context = []) {
        // Caso escalar direto.
        if (!is_array($row) && !is_object($row)) {
            $collector[] = $context + [self::DEFAULT_SCALAR_KEY => $row];
            return;
        }

        $row = (array)$row;

        // Se for lista homogênea
        if ($this->isList($row)) {
            // Lista de escalares
            if ($this->allScalars($row)) {
                foreach ($row as $v) {
                    $collector[] = $context + [self::DEFAULT_SCALAR_KEY => $v];
                }
                return;
            }

            // Lista de objetos/arrays -> cada elemento vira linha (recursivo)
            foreach ($row as $item) {
                $this->expandRow($item, $collector, $context);
            }
            return;
        }

        // Não é lista: objeto associativo.
        // Separar campos escalares de campos lista/objeto complexos.
        $scalars = [];
        $complexLists = [];
        foreach ($row as $k => $v) {
            if (is_array($v) || is_object($v)) {
                $vArr = (array)$v;
                if ($this->isList($vArr) && !$this->allScalars($vArr)) {
                    // lista de objetos -> expandir verticalmente preservando contexto scalar.
                    $complexLists[$k] = $vArr;
                } elseif ($this->isList($vArr) && $this->allScalars($vArr)) {
                    // lista de escalares: transformar cada item em linha com a chave como prefixo.
                    foreach ($vArr as $sv) {
                        $collector[] = $context + [$k => $sv];
                    }
                } else {
                    // objeto aninhado => flatten inline
                    $scalars = $scalars + $this->flatten($v, $k);
                }
            } else {
                $scalars[$k] = $v;
            }
        }

        // Se houver listas de objetos, cada objeto gera uma linha combinando contexto + scalars + flatten(item).
        if (!empty($complexLists)) {
            foreach ($complexLists as $listKey => $items) {
                foreach ($items as $obj) {
                    $line = $context + $scalars + $this->flatten($obj, $listKey);
                    $collector[] = $line;
                }
            }
            return;
        }

        // Caso base: objeto simples sem listas de objetos.
        $collector[] = $context + $scalars;
    }

    private function flatten($item, $prefix = '') {
        $result = [];
        if (is_array($item) || is_object($item)) {
            // Objetos são convertidos para array e percorridos recursivamente.
            $item = (array)$item;
            foreach ($item as $k => $v) {
                $key = $prefix === '' ? (string)$k : $prefix . '.' . $k;
                if (is_array($v)) {
                    $result += $this->flatten($v, $key);
                } else {
                    $result[$key] = $v;
                }
            }
        } else {
            $result[$prefix === '' ? self::DEFAULT_SCALAR_KEY : $prefix] = $item;
        }
        return $result;
    }

    /**
     * Retorna true quando o array possui chaves sequenciais (0..n-1).
     */
    private function isList(array $array) {
        if ($array === []) {
            return true;
        }

        return array_keys($array) === range(0, count($array) - 1);
    }

    /**
     * Verifica se todos os elementos são escalares (string/numero/bool/null).
     */
    private function allScalars(array $list) {
        foreach ($list as $item) {
            if (is_array($item) || is_object($item)) {
                return false;
            }
        }

        return true;
    }
}
