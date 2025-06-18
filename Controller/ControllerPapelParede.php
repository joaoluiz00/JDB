<?php
require_once __DIR__ . '/../Model/BancoDeDados.php';
require_once __DIR__ . '/../Model/PapelParede.php';

class ControllerPapelParede {
    private $db;
    public function __construct() {
        $this->db = BancoDeDados::getInstance();
    }

    public function listarTodos() {
        $conn = $this->db->getConnection();
        $result = $conn->query('SELECT * FROM papel_fundo');
        $papeis = [];
        while ($row = $result->fetch_assoc()) {
            $papeis[] = PapelParede::factory($row);
        }
        return $papeis;
    }

    public function getById($id) {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare('SELECT * FROM papel_fundo WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ? PapelParede::factory($row) : null;
    }

    // Compra com moedas (corrigido)
    public function comprarComMoedas($idUsuario, $idPapel, $preco) {
        $conn = $this->db->getConnection();
        $conn->begin_transaction();
        // Verifica saldo
        $stmtSaldo = $conn->prepare('SELECT coin FROM usuario WHERE id = ? FOR UPDATE');
        $stmtSaldo->bind_param('i', $idUsuario);
        $stmtSaldo->execute();
        $result = $stmtSaldo->get_result();
        $row = $result->fetch_assoc();
        $stmtSaldo->close();
        if (!$row || $row['coin'] < $preco) {
            $conn->rollback();
            return false;
        }
        // Desconta moedas
        $stmtUpdate = $conn->prepare('UPDATE usuario SET coin = coin - ? WHERE id = ?');
        $stmtUpdate->bind_param('ii', $preco, $idUsuario);
        $stmtUpdate->execute();
        $stmtUpdate->close();
        // Adiciona papel ao inventário
        $stmtInsert = $conn->prepare('INSERT INTO papel_fundo_usuario (id_usuario, id_papel) VALUES (?, ?)');
        $stmtInsert->bind_param('ii', $idUsuario, $idPapel);
        $stmtInsert->execute();
        $stmtInsert->close();
        $conn->commit();
        return true;
    }

    public function comprarComDinheiro($idPapel) {
        header('Location: ../View/ConfirmarPagamentoPapelParede.php?id=' . $idPapel);
        exit;
    }

    public function equipar($idUsuario, $idPapel) {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare('UPDATE usuario SET id_papel_fundo = ? WHERE id = ?');
        $stmt->bind_param('ii', $idPapel, $idUsuario);
        $stmt->execute();
        $stmt->close();
    }

    public function getPapeisUsuario($idUsuario) {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare('SELECT id_papel FROM papel_fundo_usuario WHERE id_usuario = ?');
        $stmt->bind_param('i', $idUsuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $ids = [];
        while ($row = $result->fetch_assoc()) {
            $ids[] = $row['id_papel'];
        }
        $stmt->close();
        return $ids;
    }
}
