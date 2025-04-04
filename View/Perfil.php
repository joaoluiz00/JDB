<?php
require_once "../Controller/ControllerUsuario.php";
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    die();
}

$controller = new ControllerUsuario();
$userId = $_SESSION['id'];
$user = $controller->readUser($userId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../Assets/style.css">
    <title>Perfil</title>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="Home.php">JDB</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="Perfil.php">Perfil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="Batalha.php">Batalha</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="Loja.php">Loja</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-4">
        <h1>Perfil do Usuário</h1>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Nome: <?php echo htmlspecialchars($user->getNome()); ?></h5>
                <p class="card-text">Email: <?php echo htmlspecialchars($user->getEmail()); ?></p>
                <p class="card-text">Moedas: <?php echo htmlspecialchars($user->getCoin()); ?></p>
                <a href="Inventario.php" class="btn-primary">Ver Inventário</a>
                <a href="Home.php" class="btn btn-primary">Voltar</a>
                <a href="Index.php" class="btn btn-primary">Sair</a>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../Assets/script.js"></script>
</body>
</html>