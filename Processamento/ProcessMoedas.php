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

    $db = BancoDeDados::getInstance('localhost', 'root', '', 'banco');

    if ($pagamento === 'cartao') {
        $numero = $_POST['numero'];
        $portador = $_POST['portador'];
        $validade = $_POST['validade'];
        $cvv = $_POST['cvv'];

        // Salvar os dados do cartão
        $db->salvarCartao($idUsuario, $numero, $portador, $validade, $cvv);
    }

    // Buscar o pacote específico
    $conn = $db->getConnection();
    $query = "SELECT quantidade_moedas FROM pacotes_moedas WHERE id_pacote = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $idPacote);
    $stmt->execute();
    $stmt->bind_result($quantidadeMoedas);
    $stmt->fetch();
    $stmt->close();

    // Adicionar moedas ao usuário
    $db->addCoins($idUsuario, $quantidadeMoedas);

    $_SESSION['success'] = "Compra de moedas realizada com sucesso!";
    header("Location: ../View/Loja.php?success=1");
    die();
}