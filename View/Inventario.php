<?php
require_once '../Controller/ControllerCartas.php';
session_start();
 
if (!isset($_SESSION['id'])) {
    header("Location: ../View/");
    exit();
}
 
$idUsuario = $_SESSION['id'];
$controller = new ControllerCartas();
 
// Inicializa a variável
$cartas = null;
 
try {
    $cartas = $controller->getCartasUsuario($idUsuario);
} catch (Exception $e) {
    $error = $e->getMessage();
}
 
// Forçar retorno vazio se houver erro
if (!$cartas instanceof mysqli_result) {
    $cartas = new mysqli_result(new mysqli_stmt(), 0);
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
<button class="theme-toggle" onclick="toggleTheme()">🌓</button>
    <audio id="bgMusic" loop hidden>
        <source src="../Assets/music/musicafundo1.mp3" type="audio/mpeg">
    </audio>
 
    <div class="inventory-container">
        <h1 class="inventory-title">📜 Inventário de Cartas</h1>
       
        <?php if ($cartas->num_rows > 0): ?>
            <div class="inventory-grid">
                <?php while ($carta = $cartas->fetch_assoc()): ?>
                    <div class="inventory-card">
                        <div class="card-header">
                            <img src="<?php echo $carta['path']; ?>" alt="<?php echo $carta['nome']; ?>" class="card-image">
                        </div>
                        <div class="card-body">
                            <h2><?php echo $carta['nome']; ?></h2>
                            <div class="card-stats">
                                <div class="stat-item">
                                    <span class="stat-icon">❤️</span>
                                    <span><?php echo $carta['vida']; ?></span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-icon">⚔️</span>
                                    <span><?php echo $carta['ataque1']; ?> (<?php echo $carta['ataque1_dano']; ?>)</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-icon">🗡️</span>
                                    <span><?php echo $carta['ataque2']; ?> (<?php echo $carta['ataque2_dano']; ?>)</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-icon">🎯</span>
                                    <span><?php echo $carta['esquiva_critico']; ?>%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-inventory">
                <p>🎴 Você ainda não possui cartas no inventário</p>
            </div>
        <?php endif; ?>
       
        <div class="inventory-actions">
            <a href="Perfil.php" class="btn-primary"> Voltar ao Perfil</a>
        </div>
     
    </div>
 
 
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../Assets/script.js"></script>
</body>
</html>