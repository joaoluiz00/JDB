<?php

require_once '../Model/BancoDeDados.php';
require_once '../Model/Admin.php';

class ControllerAdmin
{
    private $db;

    public function __construct()
    {
        $this->db = new BancoDeDados('localhost', 'root', '', 'banco');
    }

    public function registerAdmin($nome, $email, $senha)
{
    // Removida a criptografia da senha
    $sql = "INSERT INTO admin (nome, email, senha, coin) VALUES (?, ?, ?, 0)";
    $conn = $this->db->connect();
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $nome, $email, $senha);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        return true;
    } else {
        echo "Erro: " . $stmt->error;
        $stmt->close();
        $conn->close();
        return false;
    }
}
}