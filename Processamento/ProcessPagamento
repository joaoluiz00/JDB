<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../View/index.php");
    die();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idCarta = $_POST['id_carta'];
    $precoDinheiro = $_POST['preco_dinheiro'];
    $cep = $_POST['cep'];
    $rua = $_POST['rua'];
    $bairro = $_POST['bairro'];
    $cidade = $_POST['cidade'];
    $estado = $_POST['estado'];
    $pagamento = $_POST['pagamento'];

    // Aqui você pode integrar com um gateway de pagamento ou salvar os dados no banco
    // Exemplo: Salvar o pedido no banco de dados

    $_SESSION['success'] = "Compra com dinheiro real realizada com sucesso!";
    header("Location: ../View/Loja.php?success=1");
    die();
}