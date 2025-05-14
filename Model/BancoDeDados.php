<?php

class BancoDeDados
{
    private $host;
    private $user;
    private $password;
    private $database;
    private static $instance = null;
    private $connection;

    private function __construct($host, $user, $password, $database)
    {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;
        $this->connection = mysqli_connect($this->host, $this->user, $this->password, $this->database);
    }

    public static function getInstance($host = 'localhost', $user = 'root', $password = '', $database = 'banco')
    {
        if (self::$instance === null) {
            self::$instance = new BancoDeDados($host, $user, $password, $database);
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function insertUser(Usuario $usuario)
    {
        $conn = $this->getConnection();
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
            return $usuarioId;
        } else {
            echo "Error: " . $stmt->error;
            $stmt->close();
            return false;
        }
    }

    public function getUsers($id)
    {
        $conn = $this->getConnection();
        $sql = "SELECT id, nome, senha, email, coin FROM usuario WHERE id = " . $id;
        $result = $conn->query($sql);
        return $result;
    }

    public function getUserById($id)
    {
        $conn = $this->getConnection();
        $sql = "SELECT id, nome, senha, email, coin FROM usuario WHERE id = " . $id;
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
        } else {
            $user = null;
        }
        return $user;
    }

        public function getUserByEmail($email)
        {
            $conn = $this->getConnection();
            $sql = "SELECT id, nome, senha, email, coin FROM usuario WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            var_dump($user); // Verifica se os dados do usuário estão sendo retornados
            $stmt->close();
            return $user;
        }

    public function updateUser(Usuario $usuario, $id)
    {
        $conn = $this->getConnection();
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
    }

    public function deleteUser($id)
    {
        $conn = $this->getConnection();
        $sql = "DELETE FROM usuario WHERE id = " . $id;
        if ($conn->query($sql) === TRUE) {
            // echo "Record deleted successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    public function getItems()
    {
        $conn = $this->getConnection();
        $sql = "SELECT id, nome, descricao, path, preco FROM pacote";
        $result = $conn->query($sql);
        return $result;
    }

    // ... (restante dos métodos permanece o mesmo, utilizando $this->getConnection() para obter a conexão)


            public function getUserItems($userId) {
                $conn = $this->getConnection();
                $sql = "SELECT c.id, c.nome, c.path, c.vida, c.ataque1, c.ataque1_dano, c.ataque2, c.ataque2_dano, c.esquiva, c.critico, c.preco
                        FROM cartas c
                        JOIN cartas_usuario cu ON cu.id_carta = c.id
                        WHERE cu.id_usuario = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                $stmt->close();
                return $result; // Certifique-se de que a conexão não foi fechada
            }

        public function buyItem($userId, $pacoteId, $preco) {
            $conn = $this->getConnection();
            $sql = "UPDATE usuario SET coin = coin - " . $preco . " WHERE id = " . $userId . " AND coin >= " . $preco;
            if ($conn->query($sql) === TRUE) {
                $sql = "INSERT INTO pacote_cartas (id_pacote, id_carta) 
                        SELECT " . $pacoteId . ", c.id FROM cartas c WHERE c.id IN 
                        (SELECT id_carta FROM pacote_cartas WHERE id_pacote = " . $pacoteId . ")";
                if ($conn->query($sql) === TRUE) {
                    $conn->close();
                    return true;
                }
            }
            $conn->close();
            return false;
        }

        
        public function getCartas() {
            $conn = $this->getConnection();
            $sql = "SELECT id, nome, path, vida, ataque1, ataque1_dano, ataque2, ataque2_dano, esquiva, critico, preco, preco_dinheiro FROM cartas";
            $result = $conn->query($sql);
            return $result; // Não feche a conexão aqui
        }

        public function getPacoteCartas($pacoteId) {
            $conn = $this->getConnection();
            $sql = "SELECT c.id, c.nome, c.path, c.vida, c.ataque1, c.ataque1_dano, c.ataque2, c.ataque2_dano, c.esquiva, c.critico, c.preco
                    FROM cartas c
                    JOIN pacote_cartas pc ON pc.id_carta = c.id
                    WHERE pc.id_pacote = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $pacoteId);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            $conn->close();
            return $result;
        }

        public function addCoins($userId, $amount) {
            $conn = $this->getConnection();
            $sql = "UPDATE usuario SET coin = coin + " . $amount . " WHERE id = " . $userId;
            $result = $conn->query($sql);
            $conn->close();
            return $result;
        }

    public function equipItem($userId, $itemId) {
        $conn = $this->getConnection();
        $query = "UPDATE users_items ui
                  JOIN items i ON ui.items_id = i.id
                  SET ui.eqquiped = FALSE
                  WHERE ui.users_id = ? AND i.type = (SELECT type FROM items WHERE id = ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $userId, $itemId);
        $stmt->execute();
    
        $query = "UPDATE users_items SET eqquiped = TRUE WHERE users_id = ? AND items_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $userId, $itemId);
        return $stmt->execute();
    }

    public function comprarCarta($idUsuario, $idCarta, $preco) {
        $conn = $this->getConnection();
    
        // Verifica se o usuário tem moedas suficientes
        $sql = "SELECT coin FROM usuario WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $stmt->bind_result($coin);
        $stmt->fetch();
        $stmt->close();
    
        if ($coin >= $preco) {
            // Deduz o preço da carta
            $sql = "UPDATE usuario SET coin = coin - ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $preco, $idUsuario);
            $stmt->execute();
            $stmt->close();
    
            // Adiciona a carta à tabela cartas_usuario
            $sql = "INSERT INTO cartas_usuario (id_usuario, id_carta) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $idUsuario, $idCarta);
            $result = $stmt->execute();
            $stmt->close();
            $conn->close();
    
            return $result;
        } else {
            $conn->close();
            return false; // Moedas insuficientes
        }
    }

    public function getUsersList()
{
    $conn = $this->getConnection();
    $sql = "SELECT id, nome, email, coin FROM usuario";
    $result = $conn->query($sql);
    $usuarios = [];
    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }
    $conn->close();
    return $usuarios;
}

public function getPacotesMoedas() {
    $conn = $this->getConnection();
    $sql = "SELECT id_pacote, nome_pacote, quantidade_moedas, valor_dinheiro FROM pacotes_moedas";
    $result = $conn->query($sql);
    $conn->close();
    return $result;
}

public function salvarCartao($idUsuario, $numero, $portador, $validade, $cvv) {
    $conn = $this->getConnection();
    $sql = "INSERT INTO cartoes (id_usuario, numero, portador, validade, cvv) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $idUsuario, $numero, $portador, $validade, $cvv);
    $result = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $result;
}

public function getIcons() {
    $conn = $this->getConnection();
    $sql = "SELECT id, nome, path, preco, preco_dinheiro FROM img_perfil";
    $result = $conn->query($sql);
    return $result; // Não feche a conexão aqui
}


}