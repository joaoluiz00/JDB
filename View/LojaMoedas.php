<?php
// Primeiro, inicie a sessão antes de qualquer acesso a $_SESSION
session_start();

// Verifique se o usuário está logado
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    die();
}

// Carregue os controladores necessários
require_once '../Model/BancoDeDados.php';
require_once '../Controller/ControllerUsuario.php';

// Obtenha as informações do usuário
$userController = new ControllerUsuario();
$userId = $_SESSION['id'];
$user = $userController->readUser($userId);

// Obtenha os pacotes de moedas disponíveis
$banco = BancoDeDados::getInstance('localhost', 'root', '', 'banco');
$pacotes = $banco->getPacotesMoedas();

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
    <title>Loja de Moedas</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../Assets/loja.css">
    <style>
        .moedas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        
        .moeda-item {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 20px;
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .moeda-item:hover {
            transform: translateY(-5px);
        }
        
        .moeda-image {
            max-width: 100%;
            height: auto;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        
        .price {
            font-weight: bold;
            font-size: 1.2rem;
            color: #ff9900;
            margin: 10px 0;
        }

        .image-viewer {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .image-viewer .viewer-content {
            max-width: 90%;
            max-height: 90%;
        }

        .image-viewer .close {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 2rem;
            color: #fff;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div id="imageViewer" class="image-viewer">
        <span class="close" onclick="closeImage()">&times;</span>
        <img id="viewerImage" class="viewer-content">
    </div>

    <!-- Navegação fixa -->
    <nav class="navbar">
        <div class="nav-left">
            <a href="Home.php" class="btn btn-primary"> Voltar para Home</a>
            <a href="Loja.php" class="btn btn-primary"> Comprar Cartas</a>
            <a href="LojaPacote.php" class="btn btn-primary"> Comprar Pacotes</a>
            <a href="LojaIcone.php" class="btn btn-primary"> Comprar Icones</a>
            <a href="LojaPapelParede.php" class="btn btn-primary"> Papel de Parede</a>
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
            <h1 class="store-title">Loja de Moedas</h1>
        </div>

        <!-- Mensagens de sucesso ou erro -->
        <?php if (isset($_GET['success']) || $showSuccess): ?>
            <div class="alert success">✅ Compra realizada com sucesso!</div>
        <?php elseif (isset($_GET['error']) || $showError): ?>
            <div class="alert error">❌ Erro: <?php echo isset($_GET['error']) ? htmlspecialchars($_GET['error']) : (isset($_SESSION['error']) ? htmlspecialchars($_SESSION['error']) : ''); ?></div>
        <?php endif; ?>

        <!-- Exibe os pacotes de moedas disponíveis -->
<div class="moedas-grid">
    <?php while ($pacote = $pacotes->fetch_assoc()): ?>
        <div class="moeda-item">
            <div class="moeda-image-container">
                <img src="<?php echo $pacote['path']; ?>" 
                     alt="<?php echo $pacote['nome_pacote']; ?>" 
                     class="moeda-image"
                     onclick="openImage('<?php echo $pacote['path']; ?>')">
            </div>
            <div class="moeda-details">
                <h2><?php echo $pacote['nome_pacote']; ?></h2>
                <p>Quantidade: <?php echo $pacote['quantidade_moedas']; ?> moedas</p>
                <p class="price">R$ <?php echo number_format($pacote['valor_dinheiro'], 2, ',', '.'); ?></p>
                
                <!-- Botão para comprar com dinheiro -->
                <form action="../View/ConfirmarPagamentoMoedas.php" method="GET">
                    <input type="hidden" name="id_pacote" value="<?php echo $pacote['id_pacote']; ?>">
                    <input type="hidden" name="valor_dinheiro" value="<?php echo $pacote['valor_dinheiro']; ?>">
                    <button type="submit" class="btn btn-primary">Comprar Agora</button>
                </form>
            </div>
        </div>
    <?php endwhile; ?>
</div>
    </div>
    <script src="../Assets/script.js"></script>
    <script>
        function openImage(path) {
            const viewer = document.getElementById('imageViewer');
            const viewerImage = document.getElementById('viewerImage');
            viewerImage.src = path;
            viewer.style.display = 'flex';
        }

        function closeImage() {
            const viewer = document.getElementById('imageViewer');
            viewer.style.display = 'none';
        }
    </script>
</body>
</html>