<?php

class BancoDeDados
{
    private $host;
    private $user;
    private $password;
    private $database;

    public function __construct($host, $user, $password, $database)
    {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;
    }

    public function connect()
    {
        return mysqli_connect($this->host, $this->user, $this->password, $this->database);
    }

    public function insertUser(Usuario $usuario)
    {
        $conn = $this->connect();
        $sql = "INSERT INTO usuario (nome, email, senha, coin) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $nome = $usuario->getNome();
        $email = $usuario->getEmail();
        $senha = $usuario->getSenha();
        $coin = $usuario->getCoin();
        $stmt->bind_param("sssi", $nome, $email, $senha, $coin);

        if ($stmt->execute()) {
            $usuarioId = $stmt->insert_id;
            $stmt->close();
            $conn->close();
            return $usuarioId;
        } else {
            echo "Error: " . $stmt->error;
            $stmt->close();
            $conn->close();
            return false;
        }
    }

    public function getUsers($id)
    {
        $conn = $this->connect();
        $sql = "SELECT id, nome, senha, email, coin FROM usuario WHERE id = " . $id;
        $result = $conn->query($sql);
        $conn->close();
        return $result;
    }

    public function getUserById($id)
    {
        $conn = $this->connect();
        $sql = "SELECT id, nome, senha, email, coin FROM usuario WHERE id = " . $id;
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
        } else {
            $user = null;
        }
        $conn->close();
        return $user;
    }

    public function getUserByEmail($email)
    {
        $conn = $this->connect();
        $sql = "SELECT id, nome, senha, email, coin FROM usuario WHERE email = '" . $email . "'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
        } else {
            $user = null;
        }
        $conn->close();
        return $user;
    }

    public function updateUser(Usuario $usuario, $id)
    {
        $conn = $this->connect();
        $sql = "UPDATE usuario SET nome = ?, email = ?, senha = ?, coin = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $nome = $usuario->getNome();
        $email = $usuario->getEmail();
        $senha = $usuario->getSenha();
        $coin = $usuario->getCoin();
        $stmt->bind_param("sssii", $nome, $email, $senha, $coin, $id);

        if ($stmt->execute()) {
            // echo "Record updated successfully";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    }

    public function deleteUser($id)
    {
        $conn = $this->connect();
        $sql = "DELETE FROM usuario WHERE id = " . $id;
        if ($conn->query($sql) === TRUE) {
            // echo "Record deleted successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
        $conn->close();
    }
}