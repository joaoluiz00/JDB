<?php
// Primeiro, inicie a sessão antes de qualquer acesso a $_SESSION
session_start();

// Verifique se o usuário está logado
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    die();
}

// Depois de verificar o login, carregue os controladores necessários
require_once '../Controller/ControllerCartas.php';
require_once '../Controller/ControllerUsuario.php';

// Obtenha as informações do usuário usando o ControllerUsuario
$userController = new ControllerUsuario();
$userId = $_SESSION['id'];
$user = $userController->readUser($userId);

// Obtenha as cartas usando o ControllerCartas
$cartasController = new ControllerCartas();
$cartas = $cartasController->getCartas();

// Verifica mensagens da sessão
$showSuccess = isset($_SESSION['success']);
$showError = isset($_SESSION['error']);

// Limpa as mensagens após exibir
if ($showSuccess) unset($_SESSION['success']);
if ($showError) unset($_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loja de Cartas</title>
    <link rel="stylesheet" href="../Assets/style.css">
</head>
<body>
    <button class="theme-toggle" onclick="toggleTheme()">🌓</button>
    <audio id="bgMusic" loop hidden>
        <source src="../Assets/music/musicafundo1.mp3" type="audio/mpeg">
    </audio>
    
    <div class="store-container">
        <div class="store-header">
            <h1 class="store-title">🏪 Loja de Cartas</h1>
            
            <!-- Exibe suas moedas atuais -->
            <div class="user-coins">
                <p>Suas moedas: 💰 <?php echo $user->getCoin(); ?></p>
            </div>
            
            <!-- Botão para voltar à página principal -->
            <div class="navigation">
                 <a href="Home.php" class="btn btn-primary">Voltar para Home</a>
            </div>
            
            <?php if (isset($_GET['success']) || $showSuccess): ?>
                <div class="alert success">✅ Compra realizada com sucesso!</div>
            <?php elseif (isset($_GET['error']) || $showError): ?>
                <div class="alert error">❌ Erro: <?php echo isset($_GET['error']) ? htmlspecialchars($_GET['error']) : (isset($_SESSION['error']) ? htmlspecialchars($_SESSION['error']) : ''); ?></div>
            <?php endif; ?>
        </div>

        <div class="cards-grid">
            <?php while ($carta = $cartas->fetch_assoc()): ?>
                <div class="card-item">
                    <div class="card-image-container">
                        <img src="<?php echo $carta['path']; ?>" alt="<?php echo $carta['nome']; ?>" class="card-image">
                    </div>
                    <div class="card-details">
                        <h2><?php echo $carta['nome']; ?></h2>
                        <p class="price">💰 <?php echo $carta['preco']; ?> moedas</p>
                        <form method="POST" action="../Processamento/ProcessCartas.php">
                            <input type="hidden" name="action" value="comprar">
                            <input type="hidden" name="id_carta" value="<?php echo $carta['id']; ?>">
                            <input type="hidden" name="preco" value="<?php echo $carta['preco']; ?>">
                            <button type="submit" name="comprar" class="btn-buy">Comprar</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    <script src="../Assets/script.js"></script>
</body>
</html>