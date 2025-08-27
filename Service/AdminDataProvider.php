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

        // Usuários
        $usuarios = [];
        if ($result = $conn->query("SELECT id, nome, email, coin FROM usuario")) {
            while ($row = $result->fetch_assoc()) { $usuarios[] = $row; }
        }

        // Cartas
        $cartas = [];
        if ($result = $conn->query("SELECT id, nome, vida, ataque1, ataque1_dano, ataque2, ataque2_dano, esquiva, critico, preco FROM cartas")) {
            while ($row = $result->fetch_assoc()) { $cartas[] = $row; }
        }

        // Pacotes de moedas
        $pacotes_moedas = [];
        if ($result = $conn->query("SELECT id_pacote, nome_pacote, quantidade_moedas, valor_dinheiro FROM pacotes_moedas")) {
            while ($row = $result->fetch_assoc()) { $pacotes_moedas[] = $row; }
        }

        // Ícones de perfil
        $icones = [];
        if ($result = $conn->query("SELECT id, nome, preco, preco_dinheiro FROM img_perfil")) {
            while ($row = $result->fetch_assoc()) { $icones[] = $row; }
        }

        return [
            'usuarios' => $usuarios,
            'cartas' => $cartas,
            'pacotes_moedas' => $pacotes_moedas,
            'icones' => $icones,
        ];
    }
}
