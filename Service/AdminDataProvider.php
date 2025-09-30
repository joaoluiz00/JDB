<?php
require_once __DIR__ . '/../Model/BancoDeDados.php';

class AdminDataProvider {
    private $db;
    public function __construct() {
        $this->db = BancoDeDados::getInstance('localhost', 'root', '', 'banco');
    }

    /**
     * Coleta dados-chave do sistema para exportação.
     * Você pode ajustar conforme a necessidade do trabalho.
     * @return array
     */
    public function getAllData() {
        $conn = $this->db->getConnection();

        // Helper interno para reduzir repetição.
        $fetchAll = function(string $sql) use ($conn) {
            $rows = [];
            if ($result = $conn->query($sql)) {
                while ($row = $result->fetch_assoc()) { $rows[] = $row; }
            }
            return $rows;
        };

        // 1. Tabelas principais de entidades
        $usuarios         = $fetchAll("SELECT id, nome, email, coin, id_icone_perfil, id_papel_fundo FROM usuario");
        $admin            = $fetchAll("SELECT id, nome, email, coin FROM admin");
        $cartas           = $fetchAll("SELECT id, nome, path, vida, ataque1, ataque1_dano, ataque2, ataque2_dano, esquiva, critico, preco, preco_dinheiro, cor FROM cartas");
        $pacotes          = $fetchAll("SELECT id, nome, descricao, path, preco, preco_dinheiro, cor FROM pacote");
        $pacotes_moedas   = $fetchAll("SELECT id_pacote, path, nome_pacote, quantidade_moedas, valor_dinheiro FROM pacotes_moedas");
        $icones           = $fetchAll("SELECT id, nome, path, preco, preco_dinheiro FROM img_perfil");
        $papel_fundo      = $fetchAll("SELECT id, nome, path, preco, preco_dinheiro FROM papel_fundo");

        // 2. Relacionamentos / tabelas de junção
        $cartas_usuario       = $fetchAll("SELECT id, id_usuario, id_carta, equipada, preco_dinheiro FROM cartas_usuario");
        $icones_usuario       = $fetchAll("SELECT id, id_usuario, id_icone FROM icones_usuario");
        $papel_fundo_usuario  = $fetchAll("SELECT id, id_usuario, id_papel FROM papel_fundo_usuario");
        $pacote_cartas        = $fetchAll("SELECT id, id_pacote, id_carta FROM pacote_cartas");

        // 3. Comércio / transações
        $historico_transacoes = $fetchAll("SELECT id, id_usuario, tipo_transacao, id_item, valor, metodo_pagamento, data_transacao FROM historico_transacoes");
        $carrinho             = $fetchAll("SELECT id, id_usuario, tipo_item, id_item, quantidade, preco_unitario, tipo_pagamento, preco_moedas, data_adicao FROM carrinho");
        $pedidos              = $fetchAll("SELECT id, id_usuario, total, status, metodo_pagamento, data_pedido, hash_transacao FROM pedidos");
        $pedido_itens         = $fetchAll("SELECT id, id_pedido, tipo_item, id_item, quantidade, preco_unitario FROM pedido_itens");

        // 4. Cupons
        $cupons         = $fetchAll("SELECT id, codigo, descricao, tipo_desconto, valor_desconto, valor_minimo, data_inicio, data_fim, ativo, uso_maximo, uso_atual FROM cupons");
        $cupons_usuario = $fetchAll("SELECT id, id_usuario, id_cupom, data_uso FROM cupons_usuario");

        // IMPORTANTE: Se o volume de dados crescer muito, considere paginação ou export streaming.

        return [
            // Entidades
            'usuarios' => $usuarios,
            'admin' => $admin,
            'cartas' => $cartas,
            'pacotes' => $pacotes,
            'pacotes_moedas' => $pacotes_moedas,
            'icones' => $icones,
            'papel_fundo' => $papel_fundo,
            // Relacionamentos
            'cartas_usuario' => $cartas_usuario,
            'icones_usuario' => $icones_usuario,
            'papel_fundo_usuario' => $papel_fundo_usuario,
            'pacote_cartas' => $pacote_cartas,
            // Transações / fluxo
            'historico_transacoes' => $historico_transacoes,
            'carrinho' => $carrinho,
            'pedidos' => $pedidos,
            'pedido_itens' => $pedido_itens,
            // Cupons
            'cupons' => $cupons,
            'cupons_usuario' => $cupons_usuario,
        ];
    }

    /**
     * Retorna os dados de uma única "tabela lógica" para exportação.
     * @param string $table Nome lógico: usuarios, admin, cartas, pacotes, pacotes_moedas, icones, papel_fundo,
     *                      cartas_usuario, icones_usuario, papel_fundo_usuario, pacote_cartas,
     *                      historico_transacoes, carrinho, pedidos, pedido_itens, cupons, cupons_usuario
     * @return array|null Um array associativo com uma única chave => lista de linhas; ou null se inválido.
     */
    public function getDataFor(string $table) {
        $all = $this->getAllData();
        if (!array_key_exists($table, $all)) {
            return null;
        }
        // Retornamos apenas a seção solicitada, mantendo a estrutura { tableName: rows }
        return [$table => $all[$table]];
    }
}
