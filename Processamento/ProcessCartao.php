<?php
session_start();

if (!isset($_SESSION['id'])) {
    header('Location: ../View/index.php');
    die();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../View/Carrinho.php');
    die();
}

require_once '../Controller/ControllerCarrinho.php';
require_once '../Model/BancoDeDados.php';
require_once '../Service/MercadoPagoService.php';
require_once '../Service/NotificationService.php';
require_once __DIR__ . '/helpers/pedido_helper.php';

$userId = $_SESSION['id'];
$carrinhoController = new ControllerCarrinho();
$db = BancoDeDados::getInstance('localhost', 'root', '', 'banco');
$conn = $db->getConnection();

$tipoCompra = $_POST['tipo_compra'] ?? (($_POST['is_carrinho'] ?? '0') === '1' ? 'carrinho' : 'individual');
$token = $_POST['token'] ?? '';
$paymentMethodId = $_POST['payment_method_id'] ?? '';
$issuerId = $_POST['issuer_id'] ?? null;
$installments = intval($_POST['installments'] ?? 1);
$cardholderEmail = $_POST['cardholderEmail'] ?? ($_SESSION['email'] ?? '');
$cardholderName = trim($_POST['cardholderName'] ?? ($_SESSION['nome'] ?? ''));
$identificationType = $_POST['identificationType'] ?? 'CPF';
$identificationNumber = preg_replace('/\D/', '', $_POST['identificationNumber'] ?? ($_POST['documento'] ?? ''));

if (empty($token) || empty($paymentMethodId)) {
    $_SESSION['error'] = 'Pagamento inválido. Não recebemos os dados do cartão.';
    header('Location: ../View/Carrinho.php');
    die();
}

$descricaoCompra = 'Compra Loja JDB';
$amount = 0;
$idItem = intval($_POST['id_item'] ?? 0);
$tipoItem = $_POST['tipo_item'] ?? 'carta';
$precoDinheiro = floatval($_POST['preco_dinheiro'] ?? 0);
$idPacoteMoedas = intval($_POST['id_pacote'] ?? 0);
$quantidadeMoedas = intval($_POST['quantidade_moedas'] ?? 0);

switch ($tipoCompra) {
    case 'carrinho':
        $amount = floatval($_POST['total'] ?? $carrinhoController->calcularTotal($userId, 'dinheiro'));
        $descricaoCompra = 'Compra do carrinho (pagamento no cartão)';
        break;
    case 'moedas':
        $amount = floatval($_POST['valor_dinheiro'] ?? 0);
        $descricaoCompra = 'Compra de moedas pacote #' . $idPacoteMoedas;
        break;
    default:
        $amount = $precoDinheiro;
        $descricaoCompra = 'Compra de ' . ucfirst($tipoItem);
        break;
}

if ($amount <= 0) {
    $_SESSION['error'] = 'Valor da compra inválido.';
    header('Location: ../View/Carrinho.php');
    die();
}

$nomePartes = preg_split('/\s+/', $cardholderName, -1, PREG_SPLIT_NO_EMPTY);
$firstName = $nomePartes[0] ?? 'Cliente';
$lastName = $nomePartes[1] ?? (count($nomePartes) > 1 ? implode(' ', array_slice($nomePartes, 1)) : '');

try {
    $mercadoPago = MercadoPagoService::getInstance();
    $payment = $mercadoPago->createCardPayment([
        'amount' => $amount,
        'token' => $token,
        'installments' => $installments,
        'issuer_id' => $issuerId,
        'payment_method_id' => $paymentMethodId,
        'description' => $descricaoCompra,
        'payer' => [
            'email' => $cardholderEmail,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'identification' => [
                'type' => strtoupper($identificationType),
                'number' => $identificationNumber,
            ],
        ],
        'metadata' => [
            'tipo_compra' => $tipoCompra,
            'user_id' => $userId,
            'tipo_item' => $tipoItem,
            'id_item' => $idItem,
            'id_pacote_moedas' => $idPacoteMoedas,
        ],
    ]);
} catch (Exception $e) {
    $_SESSION['error'] = 'Erro ao comunicar com Mercado Pago: ' . $e->getMessage();
    header('Location: ../View/Carrinho.php');
    die();
}

$paymentStatus = $payment['status'] ?? 'pending';
if ($paymentStatus !== 'approved') {
    $detail = $payment['status_detail'] ?? 'não aprovado';
    $_SESSION['error'] = 'Pagamento não aprovado. Status: ' . $paymentStatus . ' (' . $detail . ').';
    header('Location: ../View/Carrinho.php');
    die();
}

$hash = md5($userId . time() . $amount);
$paymentId = (string)($payment['id'] ?? '');
$statusDetail = $payment['status_detail'] ?? '';
$externalReference = $payment['external_reference'] ?? null;
$transactionStarted = false;

try {
    $conn->begin_transaction();
    $transactionStarted = true;

    $sqlPedido = "INSERT INTO pedidos (id_usuario, total, metodo_pagamento, status, hash_transacao, data_pedido, payment_id, payment_status, payment_detail, external_reference) VALUES (?, ?, 'cartao', 'enviado', ?, NOW(), ?, ?, ?, ?)";
    $stmtPedido = $conn->prepare($sqlPedido);
    $stmtPedido->bind_param('idsssss', $userId, $amount, $hash, $paymentId, $paymentStatus, $statusDetail, $externalReference);
    $stmtPedido->execute();
    $idPedido = $conn->insert_id;
    $stmtPedido->close();

    if ($tipoCompra === 'carrinho') {
        $itensCarrinho = $carrinhoController->getItensCarrinho($userId);
        while ($item = $itensCarrinho->fetch_assoc()) {
            if ($item['tipo_pagamento'] !== 'dinheiro') {
                continue;
            }

            adicionarItemPedido($conn, $idPedido, $item['tipo_item'], $item['id_item'], $item['quantidade'], $item['preco_unitario']);
            concederItemAoUsuario($conn, $userId, $item['tipo_item'], $item['id_item'], $item['quantidade']);
        }
        $carrinhoController->limparCarrinho($userId);
    } elseif ($tipoCompra === 'moedas') {
        if ($idPacoteMoedas <= 0 || $quantidadeMoedas <= 0) {
            throw new RuntimeException('Pacote de moedas inválido.');
        }
        adicionarItemPedido($conn, $idPedido, 'moeda', $idPacoteMoedas, 1, $amount);
        $db->addCoins($userId, $quantidadeMoedas);

        $notificationService = NotificationService::getInstance();
        $notificationService->notificarCompraMoedas(
            $userId,
            $_SESSION['nome'] ?? 'Usuário',
            $_SESSION['email'] ?? '',
            $quantidadeMoedas,
            $amount
        );
    } else {
        adicionarItemPedido($conn, $idPedido, $tipoItem, $idItem, 1, $amount);
        concederItemAoUsuario($conn, $userId, $tipoItem, $idItem, 1);
    }

    $conn->commit();

    if ($tipoCompra === 'moedas') {
        $_SESSION['success'] = 'Compra de moedas aprovada! As moedas já estão disponíveis.';
        header('Location: ../View/LojaMoedas.php');
    } else {
        $_SESSION['success'] = 'Pagamento aprovado! Seus itens foram adicionados.';
        header('Location: ../View/HistoricoCompras.php');
    }
} catch (Exception $e) {
    if ($transactionStarted) {
        $conn->rollback();
    }
    $_SESSION['error'] = 'Erro ao finalizar pedido: ' . $e->getMessage();
    header('Location: ../View/Carrinho.php');
}

die();
?>