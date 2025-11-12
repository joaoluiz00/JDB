<?php
session_start();

// Simula usuário logado para teste
if (!isset($_SESSION['id'])) {
    $_SESSION['id'] = 1;
    $_SESSION['nome'] = 'Teste';
    $_SESSION['email'] = 'teste@teste.com';
}

require_once __DIR__ . '/../Service/NotificationService.php';

// Cria uma notificação de teste
$service = NotificationService::getInstance();
$service->initialize();

// Dispara notificação de teste
$service->notificarBoasVindas($_SESSION['id'], $_SESSION['nome']);
$service->notificarCompra(
    $_SESSION['id'],
    $_SESSION['nome'],
    $_SESSION['email'],
    'carta',
    1,
    'Dragão de Teste',
    50.00
);

header('Location: Loja.php?teste=ok');
