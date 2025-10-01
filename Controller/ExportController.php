<?php
// Controlador de exportação (JSON/CSV) para dados administrativos.
session_start();
require_once __DIR__ . '/../Service/AdminDataProvider.php';
require_once __DIR__ . '/../Model/Export/JsonExporter.php';
require_once __DIR__ . '/../Model/Export/CsvFromJsonAdapter.php';

class ExportController {
    /**
     * Realiza o download da tabela informada em JSON (padrão) ou CSV.
     * Parâmetros de query:
     * - table: nome lógico (ex.: usuarios, cartas, pedidos...)
     * - format: json|csv (json é o default)
     */
    public function download($format = 'json') {
        // Autorização: apenas admin autenticado
        if (!isset($_SESSION['admin_id'])) {
            http_response_code(403);
            header('Content-Type: text/plain; charset=utf-8');
            echo 'Acesso negado. Somente administradores podem exportar.';
            exit;
        }

        $provider = new AdminDataProvider();
        // Tabela obrigatória via ?table=usuarios|cartas|...
        $table = isset($_GET['table']) ? strtolower(trim($_GET['table'])) : null;
        if (!$table) {
            http_response_code(400);
            header('Content-Type: text/plain; charset=utf-8');
            echo 'Informe a tabela a exportar via parâmetro ?table=...';
            exit;
        }

        // Busca os dados a partir do provedor
        $data = $provider->getDataFor($table);
        if ($data === null) {
            http_response_code(400);
            header('Content-Type: text/plain; charset=utf-8');
            echo 'Tabela inválida para exportação.';
            exit;
        }

        // Seleciona o exportador (Adapter para CSV; JSON direto)
        $exporter = ($format === 'csv')
            ? new CsvFromJsonAdapter(new JsonExporter())
            : new JsonExporter();

        // Gera conteúdo e configura nome do arquivo
        $payload = $exporter->export($data);
        $suffix = $table ? ($table . '_') : '';
        $filename = 'export_' . $suffix . date('Ymd_His') . '.' . $exporter->extension();

        // Headers de resposta e envio do arquivo
        header('Content-Type: ' . $exporter->contentType());
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        echo $payload;
        exit;
    }
}

// Roteamento simples ao acessar diretamente via HTTP
if (php_sapi_name() !== 'cli') {
    $format = isset($_GET['format']) ? strtolower($_GET['format']) : 'json';
    (new ExportController())->download($format);
}
