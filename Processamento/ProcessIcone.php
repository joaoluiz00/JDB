<?php
require_once "../Controller/ControllerIcone.php";
require_once "../Controller/ControllerUsuario.php";

// Inicie a sessão antes de qualquer operação
session_start();

// Verifique se o usuário está logado
if (!isset($_SESSION['id'])) {
    header("Location: ../View/index.php");
    die();
}



if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $idUsuario = $_SESSION['id'];

    $userController = new ControllerUsuario();
    $user = $userController->readUser($idUsuario);

    $iconeController = new ControllerIcone();

    if ($action === 'comprar_moedas' && isset($_POST['id_icone']) && isset($_POST['preco'])) {
        $idIcone = $_POST['id_icone'];
        $preco = (int)$_POST['preco'];

        if ($user->getCoin() >= $preco) {
            $novoSaldo = $user->getCoin() - $preco;
            $userController->updateUser($user->getId(), $user->getNome(), $user->getEmail(), $user->getSenha(), $novoSaldo);

            $result = $iconeController->comprarIcone($idUsuario, $idIcone);

            if ($result) {
                $_SESSION['success'] = "Ícone comprado com sucesso!";
                header("Location: ../View/LojaIcone.php?success=1");
            } else {
                $_SESSION['error'] = "Falha ao processar a compra do ícone.";
                header("Location: ../View/LojaIcone.php?error=Falha+ao+processar+compra");
            }
        } else {
            $_SESSION['error'] = "Moedas insuficientes.";
            header("Location: ../View/LojaIcone.php?error=Moedas+insuficientes");
        }
    } elseif ($action === 'comprar_dinheiro' && isset($_POST['id_icone']) && isset($_POST['preco_dinheiro'])) {
        $idIcone = $_POST['id_icone'];
        $precoDinheiro = (float)$_POST['preco_dinheiro'];

        // Aqui você pode implementar a lógica de pagamento real, como integração com um gateway de pagamento.
        // Por enquanto, vamos apenas simular a compra.

        $result = $iconeController->comprarIcone($idUsuario, $idIcone);

        if ($result) {
            $_SESSION['success'] = "Ícone comprado com sucesso!";
            header("Location: ../View/LojaIcone.php?success=1");
        } else {
            $_SESSION['error'] = "Falha ao processar a compra do ícone.";
            header("Location: ../View/LojaIcone.php?error=Falha+ao+processar+compra");
        }
    } else {
        $_SESSION['error'] = "Dados de compra inválidos.";
        header("Location: ../View/LojaIcone.php?error=Dados+de+compra+inválidos");
    }
    die();

    
}