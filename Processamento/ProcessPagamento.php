<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../View/index.php");
    die();
}

require_once '../Service/NotificationService.php';
require_once '../Controller/ControllerCartas.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipoItem = $_POST['tipo_item'] ?? 'carta';
    $idItem = $_POST['id_item'] ?? ($_POST['id_carta'] ?? null);
    $precoDinheiro = $_POST['preco_dinheiro'];
    $cep = $_POST['cep'];
    $rua = $_POST['rua'];
    $bairro = $_POST['bairro'];
    $cidade = $_POST['cidade'];
    $estado = $_POST['estado'];
    $pagamento = $_POST['pagamento'];

    // Aqui você pode integrar com um gateway de pagamento ou salvar os dados no banco
    // Exemplo: Salvar o pedido no banco de dados
    // Você pode usar $tipoItem e $idItem para saber o que foi comprado

    // NOTIFICAÇÃO: Compra realizada
    $notificationService = NotificationService::getInstance();
    
    // Busca nome do item
    $nomeItem = 'Item';
    if ($tipoItem === 'carta' && $idItem) {
        require_once '../Model/BancoDeDados.php';
        $conn = BancoDeDados::getInstance('localhost', 'root', '', 'banco')->getConnection();
        $sqlCarta = "SELECT nome FROM cartas WHERE id = ?";
        $stmtCarta = $conn->prepare($sqlCarta);
        $stmtCarta->bind_param("i", $idItem);
        $stmtCarta->execute();
        $stmtCarta->bind_result($nomeItem);
        $stmtCarta->fetch();
        $stmtCarta->close();
        
        if (!$nomeItem) {
            $nomeItem = 'Carta';
        }
    }
    
    $notificationService->notificarCompra(
        $_SESSION['id'],
        $_SESSION['nome'] ?? 'Usuário',
        $_SESSION['email'] ?? '',
        $tipoItem,
        $idItem,
        $nomeItem,
        $precoDinheiro
    );

    $_SESSION['success'] = "Compra com dinheiro real realizada com sucesso!";
    header("Location: ../View/Loja.php?success=1");
    die();
}