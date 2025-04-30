<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../View/index.php");
    die();
}

require_once '../Model/BancoDeDados.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idUsuario = $_SESSION['id'];
    $idCarta = $_POST['id_carta'];
    $precoDinheiro = $_POST['preco_dinheiro'];
    $numero = $_POST['numero'];
    $portador = $_POST['portador'];
    $validade = $_POST['validade'];
    $cvv = $_POST['cvv'];

    $db = new BancoDeDados('localhost', 'root', '', 'banco');

    // Salvar os dados do cartão na tabela `cartoes`
    $db->salvarCartao($idUsuario, $numero, $portador, $validade, $cvv);

    // Aqui você pode adicionar lógica para processar o pagamento com o cartão

    // Exemplo: Confirmar a compra e redirecionar
    $_SESSION['success'] = "Cartão salvo e compra realizada com sucesso!";
    header("Location: ../View/Loja.php?success=1");
    die();
}