<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    die();
}

require_once '../Controller/ControllerCarrinho.php';
require_once '../Controller/ControllerUsuario.php';

$userController = new ControllerUsuario();
$carrinhoController = new ControllerCarrinho();
$userId = $_SESSION['id'];
$user = $userController->readUser($userId);

$itensCarrinho = $carrinhoController->getItensCarrinho($userId);
$totaisMistos = $carrinhoController->calcularTotalMisto($userId);

// Verificar se existem mensagens antes de tentar acessá-las
$showSuccess = isset($_SESSION['success']) ? $_SESSION['success'] : null;
$showError = isset($_SESSION['error']) ? $_SESSION['error'] : null;

$desconto = 0;
$cupomAplicado = false;

// Processar cupom se enviado (só funciona para compras em dinheiro)
if (isset($_POST['aplicar_cupom']) && !empty($_POST['codigo_cupom'])) {
    $codigoCupom = $_POST['codigo_cupom'];
    $cupom = $carrinhoController->validarCupom($codigoCupom);
    
    if ($cupom) {
        if ($totaisMistos['dinheiro'] >= $cupom['valor_minimo']) {
            if ($cupom['tipo_desconto'] == 'percentual') {
                $desconto = ($totaisMistos['dinheiro'] * $cupom['valor_desconto']) / 100;
            } else {
                $desconto = $cupom['valor_desconto'];
            }
            $cupomAplicado = true;
            $_SESSION['cupom_aplicado'] = $cupom;
            $_SESSION['desconto'] = $desconto;
            $_SESSION['success'] = "Cupom aplicado com sucesso!";
        } else {
            $_SESSION['error'] = "Valor mínimo para este cupom é R$ " . number_format($cupom['valor_minimo'], 2, ',', '.');
        }
    } else {
        $_SESSION['error'] = "Cupom inválido ou expirado.";
    }
    header("Location: Carrinho.php");
    die();
}

// Recuperar cupom da sessão se existir
if (isset($_SESSION['cupom_aplicado'])) {
    $cupomAplicado = true;
    $desconto = $_SESSION['desconto'] ?? 0;
}

$totalFinalDinheiro = $totaisMistos['dinheiro'] - $desconto;

// Processar remoção de cupom
if (isset($_GET['remover_cupom'])) {
    unset($_SESSION['cupom_aplicado']);
    unset($_SESSION['desconto']);
    $_SESSION['success'] = "Cupom removido com sucesso!";
    header("Location: Carrinho.php");
    die();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho de Compras</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../Assets/style.css">
    <link rel="stylesheet" href="../Assets/loja.css">
    <style>
        .tipo-pagamento-section {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        
        .preco-display {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 5px 0;
        }
        
        .preco-opcao {
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 0.9em;
        }
        
        .preco-ativo {
            background: #28a745;
            color: white;
        }
        
        .preco-inativo {
            background: #e9ecef;
            color: #6c757d;
        }
        
        .resumo-mixto {
            background: var(--card-bg);
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
        }
        
        .checkout-options {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-left">
            <a href="Home.php" class="btn btn-primary">Home</a>
            <a href="Loja.php" class="btn btn-primary">Continuar Comprando</a>
        </div>
        <div class="nav-right">
            <p class="user-coins">Suas moedas: <?php echo $user->getCoin(); ?></p>
            <button class="theme-toggle" onclick="toggleTheme()">
                <img src="../Assets/img/modoescuro.PNG" alt="Alternar tema" class="theme-icon dark-icon">
                <img src="../Assets/img/modoclaro.PNG" alt="Alternar tema" class="theme-icon light-icon">
            </button>
        </div>
    </nav>

    <div class="store-container">
        <div class="store-header">
            <h1 class="store-title">🛒 Carrinho de Compras</h1>
        </div>

        <?php if ($showSuccess): ?>
            <div class="alert success">✅ <?php echo $showSuccess; ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if ($showError): ?>
            <div class="alert error">❌ <?php echo $showError; ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if ($itensCarrinho->num_rows > 0): ?>
            <div class="carrinho-container">
                <?php while ($item = $itensCarrinho->fetch_assoc()): ?>
                    <div class="carrinho-item">
                        <img src="<?php echo $item['path']; ?>" alt="<?php echo $item['nome']; ?>" class="carrinho-image">
                        <div class="carrinho-details">
                            <h3><?php echo $item['nome']; ?></h3>
                            <p>Tipo: <?php echo ucfirst($item['tipo_item']); ?></p>
                            
                            <!-- Exibir preços e tipo de pagamento -->
                            <div class="tipo-pagamento-section">
                                <div class="preco-display">
                                    <span>💰 Moedas: <?php echo $item['preco_moedas'] * $item['quantidade']; ?></span>
                                    <span>💵 Dinheiro: R$ <?php echo number_format($item['preco_unitario'] * $item['quantidade'], 2, ',', '.'); ?></span>
                                </div>
                                
                                <form action="../Processamento/ProcessCarrinho.php" method="POST" class="tipo-pagamento-form">
                                    <input type="hidden" name="id_carrinho" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="action" value="alterar_pagamento">
                                    <label>Pagar com:</label>
                                    <select name="tipo_pagamento" onchange="this.form.submit()">
                                        <option value="dinheiro" <?php echo $item['tipo_pagamento'] === 'dinheiro' ? 'selected' : ''; ?>>💵 Dinheiro Real</option>
                                        <option value="moedas" <?php echo $item['tipo_pagamento'] === 'moedas' ? 'selected' : ''; ?>>💰 Moedas do Jogo</option>
                                    </select>
                                </form>
                                
                                <div class="preco-atual">
                                    <strong>
                                        <?php if ($item['tipo_pagamento'] === 'moedas'): ?>
                                            Total: <?php echo $item['preco_moedas'] * $item['quantidade']; ?> moedas
                                        <?php else: ?>
                                            Total: R$ <?php echo number_format($item['preco_unitario'] * $item['quantidade'], 2, ',', '.'); ?>
                                        <?php endif; ?>
                                    </strong>
                                </div>
                            </div>
                            
                            <form action="../Processamento/ProcessCarrinho.php" method="POST" class="quantidade-form">
                                <input type="hidden" name="id_carrinho" value="<?php echo $item['id']; ?>">
                                <input type="hidden" name="action" value="atualizar_quantidade">
                                <label>Quantidade:</label>
                                
                                <?php if ($item['tipo_item'] === 'icone'): ?>
                                    <!-- Para ícones, mostrar quantidade fixa -->
                                    <input type="number" name="quantidade" value="1" min="1" max="1" readonly style="background-color: #f8f9fa;">
                                    <small class="text-muted">Ícones não podem ter quantidade maior que 1</small>
                                <?php else: ?>
                                    <!-- Para outros itens, permitir alteração -->
                                    <input type="number" name="quantidade" value="<?php echo $item['quantidade']; ?>" min="1" max="10">
                                    <button type="submit" class="btn btn-sm btn-info">Atualizar</button>
                                <?php endif; ?>
                            </form>
                            
                            <form action="../Processamento/ProcessCarrinho.php" method="POST">
                                <input type="hidden" name="id_carrinho" value="<?php echo $item['id']; ?>">
                                <input type="hidden" name="action" value="remover">
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Remover este item?')">Remover</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>

                <!-- Cupom só para compras em dinheiro -->
                <?php if ($totaisMistos['dinheiro'] > 0): ?>
                    <div class="cupom-section">
                        <h3>Cupom de Desconto (Apenas para compras em dinheiro)</h3>
                        <?php if (!$cupomAplicado): ?>
                            <form method="POST">
                                <input type="text" name="codigo_cupom" placeholder="Digite o código do cupom" required>
                                <button type="submit" name="aplicar_cupom" class="btn btn-success">Aplicar Cupom</button>
                            </form>
                        <?php else: ?>
                            <p class="cupom-aplicado">✅ Cupom aplicado: <?php echo $_SESSION['cupom_aplicado']['codigo']; ?></p>
                            <p>Desconto: R$ <?php echo number_format($desconto, 2, ',', '.'); ?></p>
                            <a href="?remover_cupom=1" class="btn btn-warning btn-sm">Remover Cupom</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="resumo-mixto">
                    <h3>Resumo do Pedido</h3>
                    
                    <?php if ($totaisMistos['moedas'] > 0): ?>
                        <div class="total-moedas">
                            <h4>💰 Pagamento com Moedas do Jogo</h4>
                            <p><strong>Total: <?php echo $totaisMistos['moedas']; ?> moedas</strong></p>
                            <small>Suas moedas: <?php echo $user->getCoin(); ?> | 
                            <?php if ($user->getCoin() >= $totaisMistos['moedas']): ?>
                                <span style="color: green;">✅ Suficiente</span>
                            <?php else: ?>
                                <span style="color: red;">❌ Insuficiente (faltam <?php echo $totaisMistos['moedas'] - $user->getCoin(); ?> moedas)</span>
                            <?php endif; ?>
                            </small>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($totaisMistos['dinheiro'] > 0): ?>
                        <div class="total-dinheiro">
                            <h4>💵 Pagamento com Dinheiro Real</h4>
                            <p>Subtotal: R$ <?php echo number_format($totaisMistos['dinheiro'], 2, ',', '.'); ?></p>
                            <?php if ($desconto > 0): ?>
                                <p>Desconto: -R$ <?php echo number_format($desconto, 2, ',', '.'); ?></p>
                            <?php endif; ?>
                            <p><strong>Total: R$ <?php echo number_format($totalFinalDinheiro, 2, ',', '.'); ?></strong></p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="checkout-options">
                        <?php if ($totaisMistos['moedas'] > 0 && $user->getCoin() >= $totaisMistos['moedas']): ?>
                            <form action="../Processamento/ProcessComprasMoedas.php" method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="comprar_carrinho_moedas">
                                <button type="submit" class="btn btn-warning btn-lg" onclick="return confirm('Confirmar compra com moedas do jogo?')">
                                    💰 Comprar com Moedas (<?php echo $totaisMistos['moedas']; ?> moedas)
                                </button>
                            </form>
                        <?php endif; ?>
                        
                        <?php if ($totaisMistos['dinheiro'] > 0): ?>
                            <a href="ConfirmarEndereco.php?carrinho=1&tipo=dinheiro" class="btn btn-primary btn-lg">
                                💵 Finalizar Compra (R$ <?php echo number_format($totalFinalDinheiro, 2, ',', '.'); ?>)
                            </a>
                        <?php endif; ?>
                        
                        <form action="../Processamento/ProcessCarrinho.php" method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="limpar">
                            <button type="submit" class="btn btn-secondary" onclick="return confirm('Limpar todo o carrinho?')">Limpar Carrinho</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="carrinho-vazio">
                <h3>Seu carrinho está vazio</h3>
                <p>Adicione alguns itens para continuar</p>
                <a href="Loja.php" class="btn btn-primary">Ir às Compras</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="../Assets/script.js"></script>
</body>
</html>