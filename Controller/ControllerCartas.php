<?php
require_once '../Model/BancoDeDados.php';

class ControllerCartas {
    private $db;

    public function __construct() {
        $this->db = new BancoDeDados('localhost', 'root', '', 'banco');
    }

    public function getCartas() {
        return $this->db->getCartas();
    }

    public function comprarCarta($idUsuario, $idCarta, $preco) {
        return $this->db->comprarCarta($idUsuario, $idCarta, $preco);
    }

    public function getCartasUsuario($idUsuario) {
        return $this->db->getUserItems($idUsuario);
 
 
        $query = "SELECT * FROM inventario WHERE id_usuario = ?";
        $stmt = $conn->prepare($query);
       
        if (!$stmt) {
            throw new Exception("Erro na preparação da query: " . $conn->error);
        }
       
        $stmt->bind_param("i", $idUsuario);
       
        if (!$stmt->execute()) {
            throw new Exception("Erro na execução da query: " . $stmt->error);
        }
       
        $result = $stmt->get_result();
       
        if (!$result) {
            throw new Exception("Erro ao obter resultados: " . $conn->error);
        }
       
        return $result;
    }
    
}