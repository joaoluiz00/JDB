<?php
require_once __DIR__ . '/../Controller/ControllerPapelParede.php';
require_once __DIR__ . '/../Controller/ControllerUsuario.php';
session_start();
$idUsuario = $_SESSION['id'] ?? null;
$controller = new ControllerPapelParede();
$controllerUsuario = new ControllerUsuario();
$papeis = $controller->listarTodos();
$papeisUsuario = $idUsuario ? $controller->getPapeisUsuario($idUsuario) : [];
$user = $idUsuario ? $controllerUsuario->readUser($idUsuario) : null;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loja de Papéis de Parede</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../Assets/loja.css">
    <style>
        .item-possui {
            opacity: 0.6;
            position: relative;
        }
        .item-possui::after {
            content: "\2705 JÁ POSSUI";
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
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-left">
            <a href="Home.php" class="btn btn-primary">Voltar para Home</a>
            <a href="LojaMoedas.php" class="btn btn-primary">Comprar Moedas</a>
            <a href="LojaIcone.php" class="btn btn-primary">Comprar Ícones</a>
            <a href="LojaPacote.php" class="btn btn-primary">Pacotes</a>
            <a href="LojaPapelParede.php" class="btn btn-primary active">Papéis de Parede</a>
            <a href="Carrinho.php" class="btn btn-warning">🛒 Carrinho</a>
        </div>
        <div class="nav-right">
            <p class="user-coins">Suas moedas: <?php echo $user ? $user->getCoin() : 0; ?></p>
            <button class="theme-toggle" onclick="toggleTheme()">🌙</button>
        </div>
    </nav>
    <div class="container mt-4">
        <h1 class="mb-4">Loja de Papéis de Parede</h1>
        <div class="row">
            <?php foreach ($papeis as $papel): ?>
                <?php $jaPossui = in_array($papel->getId(), $papeisUsuario); ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 papel-item <?php if($jaPossui) echo 'item-possui'; ?>">
                        <img src="<?= $papel->getPath() ?>" class="card-img-top" alt="<?= $papel->getNome() ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= $papel->getNome() ?></h5>
                            <p class="card-text">Preço: <b><?= $papel->getPreco() ?></b> moedas</p>
                            <p class="card-text">Preço em dinheiro: <b>R$ <?= number_format($papel->getPrecoDinheiro(),2,',','.') ?></b></p>
                            <?php if ($jaPossui): ?>
                                <div class="alert alert-success text-center">Você já possui este papel de parede</div>
                            <?php else: ?>
                                <form method="post" action="../Processamento/ProcessPapelParede.php" class="mb-2">
                                    <input type="hidden" name="id_papel" value="<?= $papel->getId() ?>">
                                    <input type="hidden" name="preco" value="<?= $papel->getPreco() ?>">
                                    <button type="submit" name="comprar_moedas" class="btn btn-success btn-block mb-2">Comprar com moedas</button>
                                    <button type="submit" name="comprar_dinheiro" class="btn btn-info btn-block">Comprar com dinheiro</button>
                                </form>
                                <form method="post" action="../Processamento/ProcessCarrinho.php">
                                    <input type="hidden" name="action" value="adicionar">
                                    <input type="hidden" name="tipo_item" value="papel_fundo">
                                    <input type="hidden" name="id_item" value="<?= $papel->getId() ?>">
                                    <input type="hidden" name="preco_unitario" value="<?= $papel->getPrecoDinheiro() ?>">
                                    <input type="hidden" name="preco_moedas" value="<?= $papel->getPreco() ?>">
                                    <button type="submit" name="adicionar_carrinho" class="btn btn-warning btn-block">Adicionar ao Carrinho</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <a href="Inventario.php" class="btn btn-secondary mt-3">Ir para o Inventário</a>
    </div>
</body>
</html>
