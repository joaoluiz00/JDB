<?php
require_once "../Controller/ControllerCartas.php";
require_once "../Controller/ControllerUsuario.php";
require_once "../Model/BancoDeDados.php";

// Inicie a sessão antes de qualquer operação
session_start();

// Verifique se o usuário está logado
if (!isset($_SESSION['id'])) {
    header("Location: ../View/index.php");
    die();
}

if (isset($_POST['action']) && $_POST['action'] === 'comprar_moedas') {
    if (isset($_POST['id_carta']) && isset($_POST['preco'])) {
        $idCarta = $_POST['id_carta'];
        $preco = (int)$_POST['preco'];
        $idUsuario = $_SESSION['id'];

        $userController = new ControllerUsuario();
        $user = $userController->readUser($idUsuario);

        if ($user->getCoin() >= $preco) {
            $novoSaldo = $user->getCoin() - $preco;
            $userController->updateUser($user->getId(), $user->getNome(), $user->getEmail(), $user->getSenha(), $novoSaldo);

            $cartasController = new ControllerCartas();
            $result = $cartasController->comprarCarta($idUsuario, $idCarta, 0);

            if ($result) {
                // Registrar histórico da compra com moedas
                $conn = BancoDeDados::getInstance('localhost', 'root', '', 'banco')->getConnection();
                $sqlHist = "INSERT INTO historico_transacoes (id_usuario, tipo_transacao, id_item, valor, metodo_pagamento, data_transacao) VALUES (?, 'carta', ?, ?, 'moedas', NOW())";
                $stmtHist = $conn->prepare($sqlHist);
                $stmtHist->bind_param("iid", $idUsuario, $idCarta, $preco);
                $stmtHist->execute();
                $stmtHist->close();

                $_SESSION['success'] = "Compra realizada com sucesso!";
                header("Location: ../View/Loja.php?success=1");
            } else {
                $_SESSION['error'] = "Falha ao processar compra";
                header("Location: ../View/Loja.php?error=Falha+ao+processar+compra");
            }
        } else {
            $_SESSION['error'] = "Moedas insuficientes";
            header("Location: ../View/Loja.php?error=Moedas+insuficientes");
        }
    } else {
        $_SESSION['error'] = "Dados de compra inválidos";
        header("Location: ../View/Loja.php?error=Dados+de+compra+inválidos");
    }
    die();
}