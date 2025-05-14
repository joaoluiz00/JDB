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
require_once '../Model/BancoDeDados.php';

// Obtenha as informações do usuário
$userController = new ControllerUsuario();
$userId = $_SESSION['id'];
$user = $userController->readUser($userId);

// Obtenha os ícones do banco de dados
$banco = new BancoDeDados('localhost', 'root', '', 'banco');
$icones = $banco->getIcons();

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
    <link rel="stylesheet" href="../Assets/loja.css">
</head>
<body>
    <!-- Navegação fixa -->
    <nav class="navbar">
        <div class="nav-left">
            <a href="Home.php" class="btn btn-primary"> Voltar para Home</a>
            <a href="Loja.php" class="btn btn-primary"> Comprar Cartas</a>
            <a href="LojaMoedas.php" class="btn btn-primary"> Comprar Moedas</a>
        </div>
        <div class="nav-right">
            <p class="user-coins">Suas moedas:  <?php echo $user->getCoin(); ?></p>
            <button class="theme-toggle" onclick="toggleTheme()">🌓</button>
        </div>
    </nav>

    <!-- Container principal da loja -->
    <div class="store-container">
        <div class="store-header">
            <h1 class="store-title"> Loja de Ícones</h1>
        </div>

        <!-- Mensagens de sucesso ou erro -->
        <?php if ($showSuccess): ?>
            <div class="alert success">✅ Compra realizada com sucesso!</div>
        <?php elseif ($showError): ?>
            <div class="alert error">❌ Erro: <?php echo htmlspecialchars($_SESSION['error']); ?></div>
        <?php endif; ?>

        <!-- Exibe os ícones disponíveis -->
        <div class="icons-grid">
            <?php while ($icone = $icones->fetch_assoc()): ?>
                <div class="icon-item">
                    <div class="icon-image-container">
                        <img src="<?php echo $icone['path']; ?>" alt="<?php echo $icone['nome']; ?>" class="icon-image">
                    </div>
                    <div class="icon-details">
                        <h2><?php echo $icone['nome']; ?></h2>
                        <p class="price"> <?php echo $icone['preco']; ?> moedas</p>
                        <p class="price"> R$ <?php echo number_format($icone['preco_dinheiro'], 2, ',', '.'); ?></p>
                        
                        <!-- Botão para comprar com moedas -->
                        <form action="../Processamento/ProcessIcone.php" method="POST" onsubmit="return confirm('Tem certeza que deseja comprar este ícone com moedas do jogo?');">
                            <input type="hidden" name="id_icone" value="<?php echo $icone['id']; ?>">
                            <input type="hidden" name="preco" value="<?php echo $icone['preco']; ?>">
                            <input type="hidden" name="action" value="comprar_moedas">
                            <button type="submit" class="btn btn-primary">Comprar com Moedas</button>
                        </form>

                        <!-- Botão para comprar com dinheiro -->
                        <form action="../View/ConfirmarEndereco.php" method="GET">
                            <input type="hidden" name="id_icone" value="<?php echo $icone['id']; ?>">
                            <input type="hidden" name="preco_dinheiro" value="<?php echo $icone['preco_dinheiro']; ?>">
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