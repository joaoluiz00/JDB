<?php
require_once '../Controller/ControllerCartas.php';
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../View/"); // Redireciona para a página inicial se o usuário não estiver logado
    exit();
}

$idUsuario = $_SESSION['id']; // Obtém o ID do usuário logado
$controller = new ControllerCartas();
$cartas = $controller->getCartasUsuario($idUsuario); // Busca as cartas do usuário
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventário</title>
    <link rel="stylesheet" href="../Assets/style.css"> <!-- Link para o arquivo CSS -->
</head>
<body>
    <h1>Inventário de Cartas</h1>
    <div class="grid">
        <?php if ($cartas->num_rows > 0): ?>
            <?php while ($carta = $cartas->fetch_assoc()): ?>
                <div class="card">
                    <img src="<?php echo $carta['path']; ?>" alt="<?php echo $carta['nome']; ?>">
                    <h2><?php echo $carta['nome']; ?></h2>
                    <p>Vida: <?php echo $carta['vida']; ?></p>
                    <p>Ataque 1: <?php echo $carta['ataque1']; ?> (<?php echo $carta['ataque1_dano']; ?> dano)</p>
                    <p>Ataque 2: <?php echo $carta['ataque2']; ?> (<?php echo $carta['ataque2_dano']; ?> dano)</p>
                    <p>Esquiva Crítica: <?php echo $carta['esquiva_critico']; ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Você ainda não possui cartas no inventário.</p>
        <?php endif; ?>
    </div>
    <a href="Perfil.php" class="btn-primary">Voltar ao Perfil</a>
</body>
</html>