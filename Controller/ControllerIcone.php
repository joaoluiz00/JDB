<?php
require_once '../Model/BancoDeDados.php';

class ControllerIcone {
    private $db;

    public function __construct() {
        // Use o método getInstance() para obter a instância Singleton do BancoDeDados
        $this->db = BancoDeDados::getInstance('localhost', 'root', '', 'banco');
    }

    public function getIconesUsuario($idUsuario) {
        $conn = $this->db->getConnection();
        $sql = "SELECT i.id, i.nome, i.path FROM icones_usuario iu JOIN img_perfil i ON iu.id_icone = i.id WHERE iu.id_usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    
    public function getIconePerfil($idUsuario) {
        $conn = $this->db->getConnection();
        $sql = "SELECT i.id, i.nome, i.path FROM usuario u JOIN img_perfil i ON u.id_icone_perfil = i.id WHERE u.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $icone = $result->fetch_assoc();
        $stmt->close();
        return $icone;
    }

    public function comprarIcone($idUsuario, $idIcone) {
        $conn = $this->db->getConnection();

        // Insere o ícone comprado na tabela de ícones do usuário
        $sql = "INSERT INTO icones_usuario (id_usuario, id_icone) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $idUsuario, $idIcone);

        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }
}