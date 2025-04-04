<?php
require_once '../Controller/ControllerCartas.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comprar'])) {
    $idCarta = $_POST['id_carta'];
    $preco = $_POST['preco'];
    $idUsuario = 1; // Substitua pelo ID do usuário logado (exemplo: $_SESSION['user_id'])

    $controller = new ControllerCartas();
    $resultado = $controller->comprarCarta($idUsuario, $idCarta, $preco);

    if ($resultado) {
        header("Location: ../View/Loja.php?success=1");
    } else {
        header("Location: ../View/Loja.php?error=1");
    }
    exit();
}