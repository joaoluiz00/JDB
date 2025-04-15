<?php

require_once '../Controller/ControllerAdmin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $controller = new ControllerAdmin();
    if ($controller->registerAdmin($nome, $email, $senha)) {
        header('Location: ../View/Index.php?success=admin_registered');
    } else {
        header('Location: ../View/CadastroAdmin.php?error=registration_failed');
    }
}