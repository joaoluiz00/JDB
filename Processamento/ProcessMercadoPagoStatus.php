<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuário não autenticado.']);
    die();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não suportado.']);
    die();
}

require_once __DIR__ . '/../Service/MercadoPagoService.php';

$paymentId = preg_replace('/\D/', '', $_GET['payment_id'] ?? '');
if (empty($paymentId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Parâmetro payment_id inválido.']);
    die();
}

try {
    $mercadoPago = MercadoPagoService::getInstance();
    $payment = $mercadoPago->getPayment($paymentId);

    echo json_encode([
        'status' => $payment['status'] ?? 'unknown',
        'status_detail' => $payment['status_detail'] ?? null,
        'transaction_amount' => $payment['transaction_amount'] ?? null,
        'payer' => $payment['payer'] ?? null,
        'point_of_interaction' => $payment['point_of_interaction']['transaction_data'] ?? null,
    ]);
} catch (Exception $e) {
    http_response_code(502);
    echo json_encode(['error' => $e->getMessage()]);
}
