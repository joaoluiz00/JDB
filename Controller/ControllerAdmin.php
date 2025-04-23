<?php
require_once '../Model/BancoDeDados.php';
class ControllerAdmin
{
    private $db;

    public function __construct()
    {
        $this->db = new BancoDeDados('localhost', 'root', '', 'banco');
    }

    public function registerAdmin($nome, $email, $senha)
    {
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

    public function loginAdmin($email, $senha)
    {
        $sql = "SELECT * FROM admin WHERE email = ? AND senha = ?";
        $conn = $this->db->connect();
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $email, $senha);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $stmt->close();
            $conn->close();
            return true;
        } else {
            $stmt->close();
            $conn->close();
            return false;
        }
    }
}