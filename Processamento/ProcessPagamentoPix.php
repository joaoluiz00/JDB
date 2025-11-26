
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

require_once __DIR__ . '/../Controller/ControllerCarrinho.php';
require_once __DIR__ . '/../Model/BancoDeDados.php';
require_once __DIR__ . '/../Service/MercadoPagoService.php';
require_once __DIR__ . '/../Service/NotificationService.php';
require_once __DIR__ . '/helpers/pedido_helper.php';

$userId = $_SESSION['id'];
$paymentId = preg_replace('/\D/', '', $_POST['payment_id'] ?? '');

if (empty($paymentId)) {
    $_SESSION['error'] = 'Pagamento inválido: código PIX não encontrado.';
    header('Location: ../View/Carrinho.php');
    die();
}

$tipoCompra = $_POST['tipo_compra'] ?? 'individual';
$totalInformado = floatval($_POST['total'] ?? 0);
$endereco = isset($_POST['endereco']) && is_array($_POST['endereco']) ? $_POST['endereco'] : [];
$idItem = intval($_POST['id_item'] ?? 0);
$tipoItem = $_POST['tipo_item'] ?? 'carta';
$precoDinheiro = floatval($_POST['preco_dinheiro'] ?? 0);
$idPacoteMoedas = intval($_POST['id_pacote'] ?? 0);
$quantidadeMoedas = intval($_POST['quantidade_moedas'] ?? 0);

try {
    $mercadoPago = MercadoPagoService::getInstance();
    $pagamento = $mercadoPago->getPayment($paymentId);
} catch (Exception $e) {
    $_SESSION['error'] = 'Erro ao validar pagamento PIX: ' . $e->getMessage();
    header('Location: ../View/Carrinho.php');
    die();
}

$statusPagamento = $pagamento['status'] ?? 'pending';
if ($statusPagamento !== 'approved') {
    $_SESSION['error'] = 'Pagamento PIX ainda não foi aprovado. Status atual: ' . $statusPagamento;
    header('Location: ../View/Carrinho.php');
    die();
}

$totalPago = floatval($pagamento['transaction_amount'] ?? $totalInformado);
if ($totalPago <= 0) {
    $_SESSION['error'] = 'Valor do pagamento PIX inválido.';
    header('Location: ../View/Carrinho.php');
    die();
}

$metadata = $pagamento['metadata'] ?? [];
$tipoCompra = $metadata['tipo_compra'] ?? $tipoCompra;
$externalReference = $pagamento['external_reference'] ?? ($metadata['external_reference'] ?? null);

$conn = BancoDeDados::getInstance('localhost', 'root', '', 'banco')->getConnection();
$carrinhoController = new ControllerCarrinho();
$transactionStarted = false;

try {
    $conn->begin_transaction();
    $transactionStarted = true;

    $sqlPedido = "INSERT INTO pedidos (id_usuario, total, metodo_pagamento, status, hash_transacao, data_pedido, payment_id, payment_status, payment_detail, external_reference) VALUES (?, ?, 'pix', 'enviado', ?, NOW(), ?, ?, ?, ?)";
    $stmtPedido = $conn->prepare($sqlPedido);
    $hash = $metadata['hash'] ?? md5($userId . time() . $totalPago);
    $statusDetail = $pagamento['status_detail'] ?? '';
    $stmtPedido->bind_param('idsssss', $userId, $totalPago, $hash, $paymentId, $statusPagamento, $statusDetail, $externalReference);
    $stmtPedido->execute();
    $idPedido = $conn->insert_id;
    $stmtPedido->close();

    if (!empty($endereco)) {
        $sqlEnd = "INSERT INTO enderecos_entrega (id_pedido, cep, rua, numero, complemento, bairro, cidade, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtEnd = $conn->prepare($sqlEnd);
        $cep = $endereco['cep'] ?? '';
        $rua = $endereco['rua'] ?? '';
        $numero = $endereco['numero'] ?? '';
        $complemento = $endereco['complemento'] ?? null;
        $bairro = $endereco['bairro'] ?? '';
        $cidade = $endereco['cidade'] ?? '';
        $estado = $endereco['estado'] ?? '';
        $stmtEnd->bind_param('isssssss', $idPedido, $cep, $rua, $numero, $complemento, $bairro, $cidade, $estado);
        $stmtEnd->execute();
        $stmtEnd->close();
    }

    if ($tipoCompra === 'carrinho') {
        $itens = $carrinhoController->getItensCarrinho($userId);
        while ($item = $itens->fetch_assoc()) {
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

        adicionarItemPedido($conn, $idPedido, 'moeda', $idPacoteMoedas, 1, $totalPago);
        BancoDeDados::getInstance()->addCoins($userId, $quantidadeMoedas);

        $notificationService = NotificationService::getInstance();
        $notificationService->notificarCompraMoedas(
            $userId,
            $_SESSION['nome'] ?? 'Usuário',
            $_SESSION['email'] ?? '',
            $quantidadeMoedas,
            $totalPago
        );
    } else {
        adicionarItemPedido($conn, $idPedido, $tipoItem, $idItem, 1, $precoDinheiro ?: $totalPago);
        concederItemAoUsuario($conn, $userId, $tipoItem, $idItem, 1);
    }

    $conn->commit();

    if ($tipoCompra === 'moedas') {
        $_SESSION['success'] = 'Pagamento PIX aprovado! As moedas já estão na sua conta.';
        header('Location: ../View/LojaMoedas.php');
    } else {
        $_SESSION['success'] = 'Pagamento PIX aprovado e itens adicionados!';
        header('Location: ../View/HistoricoCompras.php');
    }
} catch (Exception $e) {
    if ($transactionStarted) {
        $conn->rollback();
    }
    $_SESSION['error'] = 'Erro ao processar pagamento PIX: ' . $e->getMessage();
    header('Location: ../View/Carrinho.php');
    die();
}

die();
?>
