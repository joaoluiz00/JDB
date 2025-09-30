<?php
require_once '../Model/BancoDeDados.php';

class ControllerAdmin
{
    private $db;

    public function __construct()
    {
        // Use o método getInstance() para obter a instância Singleton do BancoDeDados
        $this->db = BancoDeDados::getInstance('localhost', 'root', '', 'banco');
    }

    public function registerAdmin($nome, $email, $senha)
    {
        $conn = $this->db->getConnection();
        $sql = "INSERT INTO admin (nome, email, senha, coin) VALUES (?, ?, ?, 0)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $nome, $email, $senha);

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            echo "Erro: " . $stmt->error;
            $stmt->close();
            return false;
        }
    }

    public function loginAdmin($email, $senha)
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM admin WHERE email = ? AND senha = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $email, $senha);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            $stmt->close();
            // Não retornar senha por segurança
            unset($admin['senha']);
            return $admin; // retorna os dados do admin
        } else {
            $stmt->close();
            return false;
        }
    }
}