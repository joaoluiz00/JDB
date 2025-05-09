<?php
require_once(__DIR__ . "/../Model/BancoDeDados.php");
require_once(__DIR__ . "/../Model/Usuario.php");

class ControllerUsuario
{
    private $database;

    public function __construct()
    {
        $this->database = new BancoDeDados("127.0.0.1", "root", "", "banco");
    }

    public function setProfileIcon($idUsuario, $idIcone) {
        $conn = $this->database->connect(); // Corrigido: Alterado de $this->db para $this->database
        $sql = "UPDATE usuario SET id_icone_perfil = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $idIcone, $idUsuario);
        $result = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $result;
    }

    public function createUser($name, $email, $password, $coins = 0)
    {
        $user = new Usuario(null, $name, $email, $password, $coins);
        $userId = $this->database->insertUser($user);
        session_start();
        $createdUser = $this->database->getUserByEmail($user->getEmail());
        $_SESSION['id'] = $createdUser['id'];
        $_SESSION['user'] = $createdUser['nome'];
    }

    public function readUser($id)
    {
        $row = $this->database->getUserById($id);
        $user = new Usuario($row['id'], $row['nome'], $row['email'], $row['senha'], $row['coin']);
        return $user;
    }

    public function updateUser($id, $name, $email, $password, $coins)
    {
        $user = new Usuario($id, $name, $email, $password, $coins);
        $this->database->updateUser($user, $id);
    }

    public function deleteUser($id)
    {
        $this->database->deleteUser($id);
    }

    public function loginUser($email, $password)
{
    $user = $this->database->getUserByEmail($email);
    if ($user && $user['senha'] == $password) {
        // Certifique-se de limpar qualquer sessão anterior
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
        
        // Inicie uma nova sessão
        session_start();
        
        // Configure as variáveis de sessão para o novo usuário
        $_SESSION['id'] = $user['id'];
        $_SESSION['user'] = $user['nome'];
        return true;
    }
    return false;
}

    public function gainMoney($amount, $id)
    {
        $result = $this->database->getUserById($id);
        $user = new Usuario($result['id'], $result['nome'], $result['email'], $result['senha'], $result['coin']);
        $user->setCoin($user->getCoin() + $amount);
        $this->database->updateUser($user, $id);
    }
}