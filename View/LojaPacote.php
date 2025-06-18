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

// Obtenha os pacotes disponíveis
$banco = BancoDeDados::getInstance('localhost', 'root', '', 'banco');
$pacotes = $banco->getItems(); // Obtém os pacotes da tabela 'pacote'

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
    <title>Loja de Pacotes</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../Assets/loja.css">
</head>
<body>
    <!-- Navegação fixa -->
    <nav class="navbar">
        <div class="nav-left">
            <a href="Home.php" class="btn btn-primary"> Voltar para Home</a>
            <a href="Loja.php" class="btn btn-primary"> Comprar Cartas</a>
            <a href="LojaMoedas.php" class="btn btn-primary"> Comprar Moedas</a>
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
            <h1 class="store-title">Loja de Pacotes</h1>
            <p>Compre pacotes e ganhe 3 cartas aleatórias!</p>
        </div>

        <!-- Mensagens de sucesso ou erro -->
        <?php if (isset($_GET['success']) || $showSuccess): ?>
            <div class="alert success">✅ Compra realizada com sucesso!</div>
        <?php elseif (isset($_GET['error']) || $showError): ?>
            <div class="alert error">❌ Erro: <?php echo isset($_GET['error']) ? htmlspecialchars($_GET['error']) : (isset($_SESSION['error']) ? htmlspecialchars($_SESSION['error']) : ''); ?></div>
        <?php endif; ?>

        <!-- Exibe os pacotes disponíveis -->
        <div class="cards-grid">
            <?php while ($pacote = $pacotes->fetch_assoc()): ?>
                <div class="card-item">
                    <div class="card-image-container">
                        <img src="<?php echo $pacote['path']; ?>" alt="<?php echo $pacote['nome']; ?>" class="card-image">
                        <?php if ($pacote['cor'] != 'todos'): ?>
            <div class="color-indicator" style="background-color: <?php echo $pacote['cor']; ?>"></div>
        <?php endif; ?>
                    </div>
                    <div class="card-details">
                        <h2><?php echo $pacote['nome']; ?></h2>
                        <p><?php echo $pacote['descricao']; ?></p>
                        <p class="price"><?php echo $pacote['preco']; ?> moedas</p>
                        <p class="price">R$ <?php echo number_format($pacote['preco_dinheiro'], 2, ',', '.'); ?></p>
                        
                        <!-- Botão para comprar com moedas -->
                        <form action="../Processamento/ProcessPacotes.php" method="POST" onsubmit="return confirm('Tem certeza que deseja comprar este pacote com moedas do jogo?');">
                            <input type="hidden" name="id_pacote" value="<?php echo $pacote['id']; ?>">
                            <input type="hidden" name="preco" value="<?php echo $pacote['preco']; ?>">
                            <input type="hidden" name="cor" value="<?php echo $pacote['cor']; ?>">
                            <input type="hidden" name="action" value="comprar_moedas">
                            <button type="submit" class="btn btn-primary">Comprar com Moedas</button>
                        </form>

                        <!-- Botão para comprar com dinheiro -->
                        <form action="../View/ConfirmarPagamentoPacote.php" method="GET">
                            <input type="hidden" name="id_pacote" value="<?php echo $pacote['id']; ?>">
                            <input type="hidden" name="preco_dinheiro" value="<?php echo $pacote['preco_dinheiro']; ?>">
                            <input type="hidden" name="cor" value="<?php echo $pacote['cor']; ?>">
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