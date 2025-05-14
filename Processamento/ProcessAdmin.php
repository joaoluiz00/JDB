<?php

require_once '../Controller/ControllerAdmin.php';
require_once '../Model/BancoDeDados.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $controller = new ControllerAdmin();
    $db = BancoDeDados::getInstance('localhost', 'root', '', 'banco'); // Corrigido para usar getInstance()

    if ($_POST['action'] === 'register') {
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $senha = $_POST['senha'];

        if ($controller->registerAdmin($nome, $email, $senha)) {
            header('Location: ../View/Index.php?success=admin_registered');
        } else {
            header('Location: ../View/CadastroAdmin.php?error=registration_failed');
        }
    } elseif ($_POST['action'] === 'login') {
        $email = $_POST['email'];
        $senha = $_POST['senha'];

        if ($controller->loginAdmin($email, $senha)) {
            header('Location: ../View/HomeAdmin.php');
        } else {
            header('Location: ../View/LoginAdmin.php?error=invalid_credentials');
        }
    } elseif ($_POST['action'] === 'add_card') {
        $nome = $_POST['nome'];
        $imagem = $_POST['imagem'];
        $vida = $_POST['vida'];
        $ataque1 = $_POST['ataque1'];
        $ataque1_dano = $_POST['ataque1_dano'];
        $ataque2 = $_POST['ataque2'];
        $ataque2_dano = $_POST['ataque2_dano'];
        $esquiva_critico = $_POST['esquiva_critico'];
        $preco = $_POST['preco'];

        $conn = $db->getConnection(); // Corrigido para usar getConnection()
        $sql = "INSERT INTO cartas (nome, path, vida, ataque1, ataque1_dano, ataque2, ataque2_dano, esquiva_critico, preco) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssisssiii", $nome, $imagem, $vida, $ataque1, $ataque1_dano, $ataque2, $ataque2_dano, $esquiva_critico, $preco);

        if ($stmt->execute()) {
            header('Location: ../View/HomeAdmin.php?success=card_added');
        } else {
            header('Location: ../View/AdicionarCarta.php?error=add_failed');
        }

        $stmt->close();
    }
}