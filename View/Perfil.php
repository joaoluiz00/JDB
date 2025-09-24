<?php
require_once "../Controller/ControllerUsuario.php";
require_once "../Controller/ControllerIcone.php";
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    die();
}

$controllerUsuario = new ControllerUsuario();
$controllerIcone = new ControllerIcone();
$userId = $_SESSION['id'];
$user = $controllerUsuario->readUser($userId);

// Obtenha o ícone de perfil do usuário
$iconePerfil = $controllerIcone->getIconePerfil($userId);
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../Assets/style.css">
    <title>Perfil</title>
</head>
<body>
 <button class="theme-toggle" onclick="toggleTheme()">
        <img src="../Assets/img/modoescuro.PNG" alt="Alternar tema" class="theme-icon dark-icon">
        <img src="../Assets/img/modoclaro.PNG" alt="Alternar tema" class="theme-icon light-icon">
    </button>
    <div class="container mt-4">
        <h1>Perfil do Usuário</h1>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Nome: <?php echo htmlspecialchars($user->getNome()); ?></h5>
                <p class="card-text">Email: <?php echo htmlspecialchars($user->getEmail()); ?></p>
                <p class="card-text">Moedas: <?php echo htmlspecialchars($user->getCoin()); ?></p>
                <p class="card-text">
                    Foto de Perfil:
                    <?php if ($iconePerfil): ?>
                        <img src="<?php echo $iconePerfil['path']; ?>" alt="Foto de Perfil" class="profile-icon">
                    <?php else: ?>
                        Nenhuma foto de perfil definida.
                    <?php endif; ?>
                </p>
                <a href="Inventario.php" class="btn btn-primary">Ver Inventário</a>
                <a href="Home.php" class="btn btn-primary">Voltar</a>
                <form action="../Processamento/ProcessUsuario.php" method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit" class="btn btn-primary">Sair</button>
                </form>
            </div>
        </div>
    </div>
    <script src="../Assets/script.js"></script>
</body>
</html>