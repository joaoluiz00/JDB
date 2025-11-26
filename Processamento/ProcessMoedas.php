<?php
session_start();

if (!isset($_SESSION['id'])) {
    header('Location: ../View/index.php');
    die();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../View/LojaMoedas.php');
    die();
}

$pagamento = $_POST['pagamento'] ?? '';

if ($pagamento === 'cartao') {
    $_POST['tipo_compra'] = 'moedas';
    require __DIR__ . '/ProcessCartao.php';
    die();
}

if ($pagamento === 'pix') {
    $_SESSION['error'] = 'Use o botão "Gerar PIX" para criar o QR Code com o Mercado Pago.';
    header('Location: ../View/ConfirmarPagamentoMoedas.php');
    die();
}

$_SESSION['error'] = 'Forma de pagamento inválida.';
header('Location: ../View/ConfirmarPagamentoMoedas.php');
die();