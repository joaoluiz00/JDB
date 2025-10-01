<?php
// Inicie a sessão antes de qualquer acesso a $_SESSION
session_start();

// Verifique se o usuário está logado
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    die();
}

// Carregue os controladores necessários
require_once '../Controller/ControllerUsuario.php';
require_once '../Controller/ControllerIcone.php';
require_once '../Model/BancoDeDados.php';

$userController = new ControllerUsuario();
$iconeController = new ControllerIcone();
$userId = $_SESSION['id'];
$user = $userController->readUser($userId);

// Use o método getInstance() para obter a instância Singleton do BancoDeDados
$banco = BancoDeDados::getInstance('localhost', 'root', '', 'banco');
$icones = $banco->getIcons();

// Obter ícones que o usuário já possui
$iconesUsuario = $iconeController->getIconesUsuario($userId);
$iconesJaPossuidos = [];
while ($iconeUsuario = $iconesUsuario->fetch_assoc()) {
    $iconesJaPossuidos[] = $iconeUsuario['id'];
}

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
    <title>Loja de Ícones</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../Assets/style.css">
    <link rel="stylesheet" href="../Assets/loja.css">
    <link rel="stylesheet" href="../Assets/gameboy-card.css">
    <style>
        /* Estilos específicos para a loja de ícones, caso necessário,
           mas a intenção é reutilizar o máximo possível do loja.css */
        .item-possui {
            opacity: 0.6;
            position: relative;
            pointer-events: none; /* Desabilita cliques nos itens já possuídos */
        }
        .item-possui::after {
            content: "✅ JÁ POSSUI";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(40, 167, 69, 0.9);
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 12px;
            white-space: nowrap; /* Evita quebra de linha */
        }
        /* Ajustes para o card de ícone se ele for diferente do card de pacotes */
        .card-item.icon-item .card-image-container {
            height: 150px; /* Altura ajustada para ícones */
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-left">
            <a href="Home.php" class="btn btn-primary"> Voltar para Home</a>
            <a href="Loja.php" class="btn btn-primary"> Comprar Cartas</a>
            <a href="LojaPacote.php" class="btn btn-primary"> Comprar Pacotes</a>
            <a href="LojaMoedas.php" class="btn btn-primary"> Comprar Moedas</a>
            <a href="LojaPapelParede.php" class="btn btn-primary"> Papel de Parede</a>
            <a href="Carrinho.php" class="btn btn-warning"> 🛒 Carrinho</a>
        </div>
        <div class="nav-right">
            <p class="user-coins">Suas moedas: <?php echo $user->getCoin(); ?></p>
            <button class="theme-toggle" onclick="toggleTheme()">
                <img src="../Assets/img/modoescuro.PNG" alt="Alternar tema" class="theme-icon dark-icon">
                <img src="../Assets/img/modoclaro.PNG" alt="Alternar tema" class="theme-icon light-icon">
            </button>
        </div>
    </nav>

    <audio id="bgMusic" loop hidden>
        <source src="../Assets/music/musicafundo1.mp3" type="audio/mpeg">
    </audio>

    <div class="store-container">
        <div class="store-header">
            <h1 class="store-title">Loja de Ícones</h1>
            <p>Personalize seu perfil com ícones exclusivos!</p>
        </div>

        <?php if ($showSuccess): ?>
            <div class="alert success">✅ Compra realizada com sucesso!</div>
        <?php elseif ($showError): ?>
            <div class="alert error">❌ Erro: <?php echo htmlspecialchars($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="cards-grid">
            <?php if ($icones && $icones->num_rows > 0): ?>
                <?php while ($icone = $icones->fetch_assoc()): ?>
                    <?php $jaPossui = in_array($icone['id'], $iconesJaPossuidos); ?>
                    <div class="gameboy-card <?php echo $jaPossui ? 'item-possui' : ''; ?>">
                        <div class="gameboy-screen">
                            <img src="<?php echo $icone['path']; ?>" alt="<?php echo $icone['nome']; ?>" class="card-image" onclick="openImage('<?php echo $icone['path']; ?>')">
                        </div>
                        <div class="gameboy-details">
                            <h2><?php echo $icone['nome']; ?></h2>
                            <p class="gameboy-price">💰 <?php echo $icone['preco']; ?> moedas</p>
                            <p class="gameboy-price">💵 R$ <?php echo number_format($icone['preco_dinheiro'], 2, ',', '.'); ?></p>
                        </div>
                        <?php if (!$jaPossui): ?>
                        <div class="gameboy-buttons">
                            <form action="../Processamento/ProcessIcone.php" method="POST" onsubmit="return confirm('Tem certeza que deseja comprar este ícone com moedas do jogo?');">
                                <input type="hidden" name="id_icone" value="<?php echo $icone['id']; ?>">
                                <input type="hidden" name="preco" value="<?php echo $icone['preco']; ?>">
                                <input type="hidden" name="action" value="comprar_moedas">
                                <button type="submit" class="gameboy-btn buy">M</button>
                            </form>
                            <form action="../Processamento/ProcessCarrinho.php" method="POST">
                                <input type="hidden" name="tipo_item" value="icone">
                                <input type="hidden" name="id_item" value="<?php echo $icone['id']; ?>">
                                <input type="hidden" name="preco_unitario" value="<?php echo $icone['preco_dinheiro']; ?>">
                                <input type="hidden" name="preco_moedas" value="<?php echo $icone['preco']; ?>">
                                <input type="hidden" name="action" value="adicionar">
                                <button type="submit" class="gameboy-btn">🛒</button>
                            </form>
                            <form action="../View/ConfirmarEndereco.php" method="GET">
                                <input type="hidden" name="tipo_item" value="icone">
                                <input type="hidden" name="id_item" value="<?php echo $icone['id']; ?>">
                                <input type="hidden" name="preco_dinheiro" value="<?php echo $icone['preco_dinheiro']; ?>">
                                <button type="submit" class="gameboy-btn buy">R$</button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="no-items">Nenhum ícone disponível no momento.</p>
            <?php endif; ?>
        </div>
    </div>

    <div id="imageViewer" class="image-viewer">
        <span class="close">&times;</span>
        <img id="viewerImage" class="viewer-content">
    </div>

    <script src="../Assets/script.js"></script>
    <script>
        function openImage(imageSrc) {
            document.getElementById('imageViewer').style.display = 'block';
            document.getElementById('viewerImage').src = imageSrc;
        }

        document.querySelector('.close').addEventListener('click', function() {
            document.getElementById('imageViewer').style.display = 'none';
        });

        window.addEventListener('click', function(event) {
            if (event.target === document.getElementById('imageViewer')) {
                document.getElementById('imageViewer').style.display = 'none';
            }
        });
    </script>
</body>
</html>