<!-- filepath: /C:/xampp/htdocs/JDB/View/Loja.php -->
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
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../Assets/loja.css">
</head>
<body>

        <div id="imageViewer" class="image-viewer">
            <span class="close">&times;</span>
            <img id="viewerImage" class="viewer-content">
        </div>
        
    <!-- Navegação fixa -->
    <nav class="navbar">
  
        <div class="nav-left">
            <a href="Home.php" class="btn btn-primary"> Voltar para Home</a>
            <a href="LojaMoedas.php" class="btn btn-primary"> Comprar Moedas</a>
            <a href="LojaIcone.php" class="btn btn-primary"> Comprar Icones</a>
            <a href="LojaPacote.php" class="btn btn-primary"> Comprar Pacotes</a>
            <a href="LojaPapelParede.php" class="btn btn-primary"> Papéis de Parede</a>
            <a href="Carrinho.php" class="btn btn-warning"> 🛒 Carrinho</a>
        </div>
        <div class="nav-right">
        <p class="user-coins">Suas moedas: <?php echo $user->getCoin(); ?></p>
            <button class="theme-toggle" onclick="toggleTheme()">🌓</button>
        </div>
    </nav>

    <!-- Música de fundo -->
    <audio id="bgMusic" loop hidden>
        <source src="../Assets/music/musicafundo1.mp3" type="audio/mpeg">
    </audio>

    <!-- Container principal da loja -->
    <div class="store-container">
        <div class="store-header">
            <h1 class="store-title"> Loja de Cartas</h1>
        </div>

        <!-- Mensagens de sucesso ou erro -->
        <?php if (isset($_GET['success']) || $showSuccess): ?>
            <div class="alert success">✅ Compra realizada com sucesso!</div>
        <?php elseif (isset($_GET['error']) || $showError): ?>
            <div class="alert error">❌ Erro: <?php echo isset($_GET['error']) ? htmlspecialchars($_GET['error']) : (isset($_SESSION['error']) ? htmlspecialchars($_SESSION['error']) : ''); ?></div>
        <?php endif; ?>
        
        

        <!-- Exibe as cartas disponíveis -->
        <div class="cards-grid">
            <?php while ($carta = $cartas->fetch_assoc()): ?>
                <div class="card-item">
                    <div class="card-image-container">
                        <img src="<?php echo $carta['path']; ?>" alt="<?php echo $carta['nome']; ?>" class="card-image">
                    </div>
                    <div class="card-details">
                        <h2><?php echo $carta['nome']; ?></h2>
                        <p>Vida: <?php echo $carta['vida']; ?></p>
                        <p>Ataque 1: <?php echo $carta['ataque1']; ?> (<?php echo $carta['ataque1_dano']; ?> dano)</p>
                        <p>Ataque 2: <?php echo $carta['ataque2']; ?> (<?php echo $carta['ataque2_dano']; ?> dano)</p>
                        <p>Esquiva: <?php echo $carta['esquiva']; ?></p>
                        <p>Crítico: <?php echo $carta['critico']; ?></p>
                        <p class="price"> <?php echo $carta['preco']; ?> moedas</p>
                        <p class="price"> R$ <?php echo number_format($carta['preco_dinheiro'], 2, ',', '.'); ?></p>
                        
                        <!-- Botão para comprar com moedas -->
                        <form action="../Processamento/ProcessCartas.php" method="POST" onsubmit="return confirm('Tem certeza que deseja comprar esta carta com moedas do jogo?');">
                            <input type="hidden" name="id_carta" value="<?php echo $carta['id']; ?>">
                            <input type="hidden" name="preco" value="<?php echo $carta['preco']; ?>">
                            <input type="hidden" name="action" value="comprar_moedas">
                            <button type="submit" class="btn btn-primary">Comprar com Moedas</button>
                        </form>

                        <!-- Botão para adicionar ao carrinho -->
                        <form action="../Processamento/ProcessCarrinho.php" method="POST">
                            <input type="hidden" name="tipo_item" value="carta">
                            <input type="hidden" name="id_item" value="<?php echo $carta['id']; ?>">
                            <input type="hidden" name="preco_unitario" value="<?php echo $carta['preco_dinheiro']; ?>">
                            <input type="hidden" name="preco_moedas" value="<?php echo $carta['preco']; ?>">
                            <input type="hidden" name="action" value="adicionar">
                            <button type="submit" class="btn btn-success">🛒 Adicionar ao Carrinho</button>
                        </form>

                        <!-- Botão para comprar com dinheiro -->
                        <form action="../View/ConfirmarEndereco.php" method="GET">
                            <input type="hidden" name="id_carta" value="<?php echo $carta['id']; ?>">
                            <input type="hidden" name="preco_dinheiro" value="<?php echo $carta['preco_dinheiro']; ?>">
                            <button type="submit" class="btn btn-primary">Comprar com Dinheiro</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    <script src="../Assets/script.js"></script>
</body>
</html>