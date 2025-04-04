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
    }
}