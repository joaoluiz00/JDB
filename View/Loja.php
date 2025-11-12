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
require_once '../Controller/ControllerAvaliacao.php';

// Obtenha as informações do usuário usando o ControllerUsuario
$userController = new ControllerUsuario();
$controllerAvaliacao = new ControllerAvaliacao();
$userId = $_SESSION['id'];
$user = $userController->readUser($userId);

// Obtenha as cartas usando o ControllerCartas
$cartasController = new ControllerCartas();
$cartas = $cartasController->getCartas();

// Verifica mensagens da sessão
$successMsg = isset($_SESSION['success']) ? $_SESSION['success'] : null;
$errorMsg = isset($_SESSION['error']) ? $_SESSION['error'] : null;
$showSuccess = !empty($successMsg);
$showError = !empty($errorMsg);

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
    <link rel="stylesheet" href="../Assets/style.css">
    <link rel="stylesheet" href="../Assets/loja.css">
    <link rel="stylesheet" href="../Assets/gameboy-card.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../Assets/notificacoes.css">
    <!-- jQuery DEVE ser carregado ANTES do widget -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <style>
        .rating-widget {
            margin-top: 10px;
            padding: 8px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
            text-align: center;
        }
        .rating-stars {
            color: #ffc107;
            font-size: 1rem;
            margin-bottom: 5px;
        }
        .rating-info {
            font-size: 0.85rem;
            color: #666;
        }
        .btn-ver-avaliacoes {
            display: inline-block;
            margin-top: 5px;
            padding: 5px 10px;
            font-size: 0.8rem;
            background: #007bff;
            color: white;
            border-radius: 3px;
            text-decoration: none;
            transition: background 0.2s;
        }
        .btn-ver-avaliacoes:hover {
            background: #0056b3;
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>        <div id="imageViewer" class="image-viewer">
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
            <?php include __DIR__ . '/components/NotificacoesWidget.php'; ?>
            <button class="theme-toggle" onclick="toggleTheme()">
                <img src="../Assets/img/modoescuro.PNG" alt="Alternar tema" class="theme-icon dark-icon">
                <img src="../Assets/img/modoclaro.PNG" alt="Alternar tema" class="theme-icon light-icon">
            </button>
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
        <?php if ($showSuccess): ?>
            <div class="alert success">✅ <?php echo htmlspecialchars($successMsg); ?></div>
        <?php elseif ($showError): ?>
            <div class="alert error">❌ Erro: <?php echo htmlspecialchars($errorMsg); ?></div>
        <?php elseif (isset($_GET['success'])): ?>
            <div class="alert success">✅ Compra realizada com sucesso!</div>
        <?php elseif (isset($_GET['error'])): ?>
            <div class="alert error">❌ Erro: <?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>
        
        

        <!-- Exibe as cartas disponíveis -->
        <div class="cards-grid">
            <?php while ($carta = $cartas->fetch_assoc()): 
                $mediaInfo = $controllerAvaliacao->getMediaAvaliacoes('carta', $carta['id']);
            ?>
                <div class="gameboy-card">
                    <div class="gameboy-screen">
                        <img src="<?php echo $carta['path']; ?>" 
                            alt="<?php echo $carta['nome']; ?>" 
                            class="card-image" 
                            onclick="openImage('<?php echo $carta['path']; ?>')">
                    </div>
                    <div class="gameboy-details">
                        <h2><?php echo $carta['nome']; ?></h2>
                        <p>Vida: <?php echo $carta['vida']; ?></p>
                        <p>Ataque 1: <?php echo $carta['ataque1']; ?> (<?php echo $carta['ataque1_dano']; ?> dano)</p>
                        <p>Ataque 2: <?php echo $carta['ataque2']; ?> (<?php echo $carta['ataque2_dano']; ?> dano)</p>
                        <p>Esquiva: <?php echo $carta['esquiva']; ?></p>
                        <p>Crítico: <?php echo $carta['critico']; ?></p>
                        <p class="gameboy-price"> <?php echo $carta['preco']; ?> moedas</p>
                        <p class="gameboy-price"> R$ <?php echo number_format($carta['preco_dinheiro'], 2, ',', '.'); ?></p>
                        
                        <!-- Widget de Avaliações -->
                        <?php if ($mediaInfo['total'] > 0): ?>
                            <div class="rating-widget">
                                <div class="rating-stars">
                                    <?php 
                                    $media = $mediaInfo['media'];
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo ($i <= round($media)) ? '★' : '☆';
                                    }
                                    ?>
                                </div>
                                <div class="rating-info">
                                    <?php echo $mediaInfo['media']; ?> / 5.0 (<?php echo $mediaInfo['total']; ?> avaliações)
                                </div>
                                <a href="VisualizarAvaliacoes.php?tipo=carta&id=<?php echo $carta['id']; ?>" 
                                   class="btn-ver-avaliacoes">
                                    <i class="fas fa-comments"></i> Ver Avaliações
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="rating-widget">
                                <div class="rating-info">Sem avaliações ainda</div>
                                <a href="VisualizarAvaliacoes.php?tipo=carta&id=<?php echo $carta['id']; ?>" 
                                   class="btn-ver-avaliacoes">
                                    <i class="fas fa-star"></i> Seja o primeiro!
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="gameboy-buttons">
                        <form action="../Processamento/ProcessCartas.php" method="POST" onsubmit="return confirm('Tem certeza que deseja comprar esta carta com moedas do jogo?');">
                            <input type="hidden" name="id_carta" value="<?php echo $carta['id']; ?>">
                            <input type="hidden" name="preco" value="<?php echo $carta['preco']; ?>">
                            <input type="hidden" name="action" value="comprar_moedas">
                            <button type="submit" class="gameboy-btn buy" title="Comprar com Moedas">M</button>
                        </form>
                        <form action="../Processamento/ProcessCarrinho.php" method="POST">
                            <input type="hidden" name="tipo_item" value="carta">
                            <input type="hidden" name="id_item" value="<?php echo $carta['id']; ?>">
                            <input type="hidden" name="preco_unitario" value="<?php echo $carta['preco_dinheiro']; ?>">
                            <input type="hidden" name="preco_moedas" value="<?php echo $carta['preco']; ?>">
                            <input type="hidden" name="action" value="adicionar">
                            <button type="submit" class="gameboy-btn" title="Adicionar ao Carrinho">🛒</button>
                        </form>
                        <form action="../View/ConfirmarEndereco.php" method="GET">
                            <input type="hidden" name="id_carta" value="<?php echo $carta['id']; ?>">
                            <input type="hidden" name="preco_dinheiro" value="<?php echo $carta['preco_dinheiro']; ?>">
                            <button type="submit" class="gameboy-btn buy" title="Comprar com Dinheiro">R$</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    <script src="../Assets/script.js"></script>
    <script src="../Assets/js/notificacoes.js"></script>
</body>
</html>