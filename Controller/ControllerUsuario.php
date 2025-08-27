<?php
require_once(__DIR__ . "/../Model/BancoDeDados.php");
require_once(__DIR__ . "/../Model/Usuario.php");

class ControllerUsuario
{
    private $database;

    public function __construct()
    {
        // Obtém a instância Singleton do BancoDeDados
        $this->database = BancoDeDados::getInstance("127.0.0.1", "root", "", "banco");
    }

    public function loginUser($email, $password)
{
    // Busca o usuário pelo e-mail
    $user = $this->database->getUserByEmail($email);
    $senhaCorreta = false;
    if ($user) {
        // Verifica se a senha está correta (criptografada ou não)
        $senhaCorreta = password_verify($password, $user['senha']) || $password === $user['senha'];
    }

    // Verifica se o usuário existe e se a senha está correta
    if ($user && $senhaCorreta) {
        // Verifica se a sessão já está ativa
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['id'] = $user['id'];
        $_SESSION['nome'] = $user['nome'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['coin'] = $user['coin'];
        return true;
    }

    return false;
}

    public function setProfileIcon($idUsuario, $idIcone)
    {
        $conn = $this->database->getConnection(); // Usa getConnection() para obter a conexão
        $sql = "UPDATE usuario SET id_icone_perfil = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $idIcone, $idUsuario);
        $result = $stmt->execute();
        $stmt->close();
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
    if ($row) {
        // Retorna um objeto da classe Usuario
        return new Usuario($row['id'], $row['nome'], $row['email'], $row['senha'], $row['coin']);
    }
    return null; // Retorna null se o usuário não for encontrado
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

    public function getUserItems($userId)
    {
        $result = $this->database->getUserItems($userId);
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        return $items;
    }

    public function buyItem($userId, $pacoteId, $preco)
    {
        return $this->database->buyItem($userId, $pacoteId, $preco);
    }

    public function addCoins($userId, $amount)
    {
        return $this->database->addCoins($userId, $amount);
    }

    
}