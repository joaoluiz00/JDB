<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../View/index.php");
    die();
}

require_once '../Model/BancoDeDados.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idUsuario = $_SESSION['id'];
    $idPacote = $_POST['id_pacote'];
    $valorDinheiro = $_POST['valor_dinheiro'];
    $pagamento = $_POST['pagamento'];

    $db = new BancoDeDados('localhost', 'root', '', 'banco');

    if ($pagamento === 'cartao') {
        $numero = $_POST['numero'];
        $portador = $_POST['portador'];
        $validade = $_POST['validade'];
        $cvv = $_POST['cvv'];

        // Salvar os dados do cartão
        $db->salvarCartao($idUsuario, $numero, $portador, $validade, $cvv);
    }

    // Adicionar moedas ao usuário
    $pacote = $db->getPacotesMoedas()->fetch_assoc();
    $quantidadeMoedas = $pacote['quantidade_moedas'];

    $db->addCoins($idUsuario, $quantidadeMoedas);

    $_SESSION['success'] = "Compra de moedas realizada com sucesso!";
    header("Location: ../View/Loja.php?success=1");
    die();
}