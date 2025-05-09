<?php
require_once '../Controller/ControllerCartas.php';
require_once '../Controller/ControllerIcone.php';
require_once '../Controller/ControllerUsuario.php';
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../View/");
    exit();
}

$idUsuario = $_SESSION['id'];
$controllerCartas = new ControllerCartas();
$controllerIcone = new ControllerIcone();
$controllerUsuario = new ControllerUsuario();

// Inicializa as variáveis
$cartas = null;
$icones = null;

try {
    $cartas = $controllerCartas->getCartasUsuario($idUsuario);
    $icones = $controllerIcone->getIconesUsuario($idUsuario);
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Forçar retorno vazio se houver erro
if (!$cartas instanceof mysqli_result) {
    $cartas = new mysqli_result(new mysqli_stmt(), 0);
}
if (!$icones instanceof mysqli_result) {
    $icones = new mysqli_result(new mysqli_stmt(), 0);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Inventário</title>
    <link rel="stylesheet" href="../Assets/style.css">
</head>
<body>
    <div class="container mt-4">
        <h1>Inventário</h1>

        <!-- Exibe as cartas -->
        <h2>Cartas</h2>
        <div class="cards-grid">
            <?php while ($carta = $cartas->fetch_assoc()): ?>
                <div class="card-item">
                    <h3><?php echo $carta['nome']; ?></h3>
                    <p>Vida: <?php echo $carta['vida']; ?></p>
                    <p>Ataque 1: <?php echo $carta['ataque1']; ?> (<?php echo $carta['ataque1_dano']; ?> dano)</p>
                    <p>Ataque 2: <?php echo $carta['ataque2']; ?> (<?php echo $carta['ataque2_dano']; ?> dano)</p>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Exibe os ícones -->
        <h2>Ícones</h2>
        <div class="icons-grid">
            <?php while ($icone = $icones->fetch_assoc()): ?>
                <div class="icon-item">
                    <img src="<?php echo $icone['path']; ?>" alt="<?php echo $icone['nome']; ?>" class="icon-image">
                    <h3><?php echo $icone['nome']; ?></h3>
                    <form action="../Processamento/ProcessUsuario.php" method="POST">
                        <input type="hidden" name="action" value="set_profile_icon">
                        <input type="hidden" name="id_icone" value="<?php echo $icone['id']; ?>">
                        <button type="submit" class="btn btn-primary">Definir como Foto de Perfil</button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>