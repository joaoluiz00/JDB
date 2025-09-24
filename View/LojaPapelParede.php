<?php
require_once __DIR__ . '/../Controller/ControllerPapelParede.php';
require_once __DIR__ . '/../Controller/ControllerUsuario.php';
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    die();
}

$idUsuario = $_SESSION['id'];
$controller = new ControllerPapelParede();
$controllerUsuario = new ControllerUsuario();
$papeis = $controller->listarTodos();
$papeisUsuario = $controller->getPapeisUsuario($idUsuario);
$user = $controllerUsuario->readUser($idUsuario);

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
    <title>Loja de Papéis de Parede</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../Assets/style.css">
    <link rel="stylesheet" href="../Assets/loja.css">
</head>
<body>
    <div id="imageViewer" class="image-viewer">
        <span class="close">&times;</span>
        <img id="viewerImage" class="viewer-content">
    </div>

    <nav class="navbar">
        <div class="nav-left">
            <a href="Home.php" class="btn btn-primary">Voltar para Home</a>
            <a href="LojaMoedas.php" class="btn btn-primary">Comprar Moedas</a>
            <a href="LojaIcone.php" class="btn btn-primary">Comprar Ícones</a>
            <a href="LojaPacote.php" class="btn btn-primary">Comprar Pacotes</a>
            <a href="LojaPapelParede.php" class="btn btn-primary">Papéis de Parede</a>
            <a href="Carrinho.php" class="btn btn-warning">🛒 Carrinho</a>
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
            <h1 class="store-title">Loja de Papéis de Parede</h1>
        </div>

        <?php if ($showSuccess): ?>
            <div class="alert success">✅ Compra realizada com sucesso!</div>
        <?php elseif ($showError): ?>
            <div class="alert error">❌ Erro: <?php echo htmlspecialchars($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="cards-grid">
            <?php foreach ($papeis as $papel): ?>
                <?php $jaPossui = in_array($papel->getId(), $papeisUsuario); ?>
                <div class="card-item <?php if($jaPossui) echo 'item-possui'; ?>">
                    <div class="card-image-container">
                        <img src="<?= $papel->getPath() ?>" alt="<?= $papel->getNome() ?>" class="card-image" onclick="openImage('<?= $papel->getPath() ?>')">
                    </div>
                    <div class="card-details">
                        <h2><?= $papel->getNome() ?></h2>
                        <p class="price"><?= $papel->getPreco() ?> moedas</p>
                        <p class="price">R$ <?= number_format($papel->getPrecoDinheiro(),2,',','.') ?></p>

                        <?php if (!$jaPossui): ?>
                            <form method="post" action="../Processamento/ProcessPapelParede.php" onsubmit="return confirm('Tem certeza que deseja comprar este papel de parede com moedas do jogo?');">
                                <input type="hidden" name="id_papel" value="<?= $papel->getId() ?>">
                                <input type="hidden" name="preco" value="<?= $papel->getPreco() ?>">
                                <button type="submit" name="comprar_moedas" class="btn btn-primary">Comprar com Moedas</button>
                            </form>

                            <form action="../Processamento/ProcessCarrinho.php" method="POST">
                                <input type="hidden" name="action" value="adicionar">
                                <input type="hidden" name="tipo_item" value="papel_fundo">
                                <input type="hidden" name="id_item" value="<?= $papel->getId() ?>">
                                <input type="hidden" name="preco_unitario" value="<?= $papel->getPrecoDinheiro() ?>">
                                <input type="hidden" name="preco_moedas" value="<?= $papel->getPreco() ?>">
                                <button type="submit" class="btn btn-success">🛒 Adicionar ao Carrinho</button>
                            </form>

                            <form method="get" action="ConfirmarEndereco.php">
                                <input type="hidden" name="id_papel" value="<?= $papel->getId() ?>">
                                <input type="hidden" name="preco_dinheiro" value="<?= $papel->getPrecoDinheiro() ?>">
                                <button type="submit" class="btn btn-primary">Comprar com Dinheiro</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="../Assets/script.js"></script>
</body>
</html>