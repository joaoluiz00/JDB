<?php
require_once __DIR__ . '/../Controller/ControllerPapelParede.php';

$controller = new ControllerPapelParede();

session_start();
$idUsuario = $_SESSION['id'] ?? null;

if (isset($_POST['comprar_moedas'])) {
    $idPapel = $_POST['id_papel'];
    $preco = $_POST['preco'];
    if ($controller->comprarComMoedas($idUsuario, $idPapel, $preco)) {
        header('Location: ../View/Inventario.php?msg=papel_comprado');
    } else {
        header('Location: ../View/LojaPapelParede.php?erro=saldo');
    }
    exit;
}

if (isset($_POST['comprar_dinheiro'])) {
    $idPapel = $_POST['id_papel'];
    header('Location: ../View/ConfirmarPagamentoPapelParede.php?id=' . $idPapel);
    exit;
}

if (isset($_POST['equipar'])) {
    $idPapel = $_POST['id_papel'];
    $controller->equipar($idUsuario, $idPapel);
    header('Location: ../View/Inventario.php?msg=papel_equipado');
    exit;
}
