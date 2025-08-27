<?php
require_once __DIR__ . '/../Model/BancoDeDados.php';

class ControllerVendas {
    private $db;

    public function __construct() {
        $this->db = BancoDeDados::getInstance('localhost', 'root', '', 'banco');
    }

    public function getPedidosByUsuario($idUsuario) {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM pedidos WHERE id_usuario = ? ORDER BY data_pedido DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $idUsuario);
        $stmt->execute();
        $res = $stmt->get_result();
        $pedidos = [];
        while ($row = $res->fetch_assoc()) { $pedidos[] = $row; }
        $stmt->close();
        return $pedidos;
    }

    public function getItensPedido($idPedido) {
        $conn = $this->db->getConnection();
        $sql = "SELECT id, tipo_item, id_item, quantidade, preco_unitario FROM pedido_itens WHERE id_pedido = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $idPedido);
        $stmt->execute();
        $res = $stmt->get_result();
        $itens = [];
        while ($row = $res->fetch_assoc()) { $itens[] = $row; }
        $stmt->close();
        return $itens;
    }

    public function getHistoricoMoedasByUsuario($idUsuario) {
        $conn = $this->db->getConnection();
        $sql = "SELECT id, tipo_transacao, id_item, valor, metodo_pagamento, data_transacao FROM historico_transacoes WHERE id_usuario = ? AND metodo_pagamento = 'moedas' ORDER BY data_transacao DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $idUsuario);
        $stmt->execute();
        $res = $stmt->get_result();
        $historico = [];
        while ($row = $res->fetch_assoc()) { $historico[] = $row; }
        $stmt->close();
        return $historico;
    }

    public function getAllPedidos() {
        $conn = $this->db->getConnection();
        $sql = "SELECT p.*, u.nome as nome_usuario, u.email FROM pedidos p JOIN usuario u ON p.id_usuario = u.id ORDER BY p.data_pedido DESC";
        $res = $conn->query($sql);
        $pedidos = [];
        while ($row = $res->fetch_assoc()) { $pedidos[] = $row; }
        return $pedidos;
    }

    public function getAllHistoricoMoedas() {
        $conn = $this->db->getConnection();
        $sql = "SELECT h.*, u.nome as nome_usuario, u.email FROM historico_transacoes h JOIN usuario u ON h.id_usuario = u.id WHERE h.metodo_pagamento = 'moedas' ORDER BY h.data_transacao DESC";
        $res = $conn->query($sql);
        $historico = [];
        while ($row = $res->fetch_assoc()) { $historico[] = $row; }
        return $historico;
    }

    public function resolveItemNome($tipo, $idItem) {
        $conn = $this->db->getConnection();
        switch ($tipo) {
            case 'carta':
                $sql = 'SELECT nome FROM cartas WHERE id = ?'; break;
            case 'icone':
                $sql = 'SELECT nome FROM img_perfil WHERE id = ?'; break;
            case 'pacote':
                $sql = 'SELECT nome FROM pacote WHERE id = ?'; break;
            default:
                return 'Item #' . $idItem;
        }
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $idItem);
        $stmt->execute();
        $res = $stmt->get_result();
        $nome = 'Item #' . $idItem;
        if ($row = $res->fetch_assoc()) { $nome = $row['nome']; }
        $stmt->close();
        return $nome;
    }
}
