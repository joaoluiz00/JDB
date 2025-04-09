<?php
require_once "../Controller/ControllerCartas.php";
require_once "../Controller/ControllerUsuario.php";

// Inicie a sessão antes de qualquer operação
session_start();

// Verifique se o usuário está logado
if (!isset($_SESSION['id'])) {
    header("Location: ../View/index.php");
    die();
}

// Verifique se a ação foi enviada
if (isset($_POST['comprar']) || (isset($_POST['action']) && $_POST['action'] === 'comprar')) {
    if (isset($_POST['id_carta']) && isset($_POST['preco'])) {
        $idCarta = $_POST['id_carta'];
        $preco = (int)$_POST['preco'];
        $idUsuario = $_SESSION['id'];
        
        // Obtenha as informações do usuário
        $userController = new ControllerUsuario();
        $user = $userController->readUser($idUsuario);
        
        // Verifique se o usuário tem moedas suficientes
        if ($user->getCoin() >= $preco) {
            // Subtraia o preço das moedas do usuário
            $novoSaldo = $user->getCoin() - $preco;
            $userController->updateUser($user->getId(), $user->getNome(), $user->getEmail(), $user->getSenha(), $novoSaldo);
            
            // Adicione a carta ao inventário do usuário sem subtrair novamente
            // Passamos preço como 0 para que não diminua novamente na função comprarCarta
            $cartasController = new ControllerCartas();
            $result = $cartasController->comprarCarta($idUsuario, $idCarta, 0);
            
            if ($result) {
                $_SESSION['success'] = "Compra realizada com sucesso!";
                header("Location: ../View/Loja.php?success=1");
            } else {
                $_SESSION['error'] = "Falha ao processar compra";
                header("Location: ../View/Loja.php?error=Falha+ao+processar+compra");
            }
            die();
        } else {
            $_SESSION['error'] = "Moedas insuficientes";
            header("Location: ../View/Loja.php?error=Moedas+insuficientes");
            die();
        }
    } else {
        $_SESSION['error'] = "Dados de compra inválidos";
        header("Location: ../View/Loja.php?error=Dados+de+compra+inválidos");
        die();
    }
} else {
    $_SESSION['error'] = "Ação inválida";
    header("Location: ../View/Loja.php?error=Ação+inválida");
    die();
}