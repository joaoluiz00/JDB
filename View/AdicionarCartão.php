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

// Verificar se é compra do carrinho ou item individual
$isCarrinho = isset($_GET['carrinho']) && $_GET['carrinho'] == '1';

if ($isCarrinho) {
    // Compra do carrinho
    $itensCarrinho = $carrinhoController->getItensCarrinho($userId);
    $totalCarrinho = $carrinhoController->calcularTotal($userId, 'dinheiro');
    
    // Aplicar desconto se houver cupom na sessão
    $desconto = isset($_SESSION['desconto']) ? $_SESSION['desconto'] : 0;
    $totalFinal = $totalCarrinho - $desconto;
    
    if ($itensCarrinho->num_rows == 0) {
        $_SESSION['error'] = "Carrinho vazio!";
        header("Location: Carrinho.php");
        die();
    }
    
    $descricaoCompra = "Compra do carrinho (" . $itensCarrinho->num_rows . " itens)";
    $valorCompra = $totalFinal;
} else {
    // Compra individual
    $idCarta = $_GET['id_carta'] ?? $_GET['id_icone'] ?? $_GET['id_pacote'] ?? null;
    $precoDinheiro = $_GET['preco_dinheiro'] ?? null;
    $tipoItem = isset($_GET['id_carta']) ? 'carta' : (isset($_GET['id_icone']) ? 'icone' : 'pacote');
    
    if (!$idCarta || !$precoDinheiro) {
        $_SESSION['error'] = "Dados de compra inválidos!";
        header("Location: Loja.php");
        die();
    }
    
    // Buscar nome do item
    $conn = BancoDeDados::getInstance('localhost', 'root', '', 'banco')->getConnection();
    $nomeItem = "Item";
    
    switch ($tipoItem) {
        case 'carta':
            $sql = "SELECT nome FROM cartas WHERE id = ?";
            break;
        case 'icone':
            $sql = "SELECT nome FROM img_perfil WHERE id = ?";
            break;
        case 'pacote':
            $sql = "SELECT nome FROM pacote WHERE id = ?";
            break;
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idCarta);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $nomeItem = $row['nome'];
    }
    $stmt->close();
    
    $descricaoCompra = $nomeItem . " (" . ucfirst($tipoItem) . ")";
    $valorCompra = $precoDinheiro;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Cartão</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../Assets/loja.css">
    <style>
        .payment-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .card-form {
            background: var(--card-bg);
            padding: 30px;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .resumo-compra {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .card-visual {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
        }
        
        .card-number {
            font-size: 18px;
            letter-spacing: 2px;
            margin: 10px 0;
        }
        
        .card-info {
            display: flex;
            justify-content: space-between;
            align-items: end;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-left">
            <a href="<?php echo $isCarrinho ? 'Carrinho.php' : 'Loja.php'; ?>" class="btn btn-secondary">Voltar</a>
        </div>
        <div class="nav-right">
            <p class="user-coins">Suas moedas: <?php echo $user->getCoin(); ?></p>
        </div>
    </nav>

    <div class="payment-container">
        <h1 class="text-center">Adicionar Cartão de Crédito</h1>
        
        <div class="resumo-compra">
            <h3>Resumo da Compra</h3>
            <p><strong>Item:</strong> <?php echo htmlspecialchars($descricaoCompra); ?></p>
            <p><strong>Valor:</strong> R$ <?php echo number_format($valorCompra, 2, ',', '.'); ?></p>
        </div>

        <!-- Visualização do cartão -->
        <div class="card-visual">
            <div>BANCO VIRTUAL</div>
            <div class="card-number" id="cardDisplay">**** **** **** ****</div>
            <div class="card-info">
                <div>
                    <div style="font-size: 12px;">PORTADOR</div>
                    <div id="nameDisplay">NOME DO PORTADOR</div>
                </div>
                <div>
                    <div style="font-size: 12px;">VALIDADE</div>
                    <div id="validadeDisplay">MM/AA</div>
                </div>
            </div>
        </div>

        <form action="../Processamento/ProcessCartao.php" method="POST" class="card-form">
            <!-- Dados da compra -->
            <input type="hidden" name="is_carrinho" value="<?php echo $isCarrinho ? '1' : '0'; ?>">
            
            <?php if ($isCarrinho): ?>
                <input type="hidden" name="total" value="<?php echo $valorCompra; ?>">
            <?php else: ?>
                <input type="hidden" name="id_item" value="<?php echo $idCarta; ?>">
                <input type="hidden" name="tipo_item" value="<?php echo $tipoItem; ?>">
                <input type="hidden" name="preco_dinheiro" value="<?php echo $precoDinheiro; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="numero">Número do Cartão:</label>
                <input type="text" id="numero" name="numero" maxlength="19" placeholder="1234 5678 9012 3456" required>
            </div>
            
            <div class="form-group">
                <label for="portador">Nome do Portador:</label>
                <input type="text" id="portador" name="portador" placeholder="NOME COMO NO CARTÃO" style="text-transform: uppercase;" required>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="validade">Validade (MM/AA):</label>
                        <input type="text" id="validade" name="validade" maxlength="5" placeholder="12/25" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="cvv">CVV:</label>
                        <input type="text" id="cvv" name="cvv" maxlength="4" placeholder="123" required>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg btn-block">
                💳 Salvar Cartão e Finalizar Compra
            </button>
        </form>
    </div>

    <script>
        // Formatação e atualização visual do cartão
        document.getElementById('numero').addEventListener('input', function(e) {
            let valor = e.target.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = valor.match(/.{1,4}/g)?.join(' ') || valor;
            if (formattedValue.length <= 19) {
                e.target.value = formattedValue;
                
                // Atualizar visualização
                let displayValue = formattedValue.padEnd(19, '*').replace(/(.{4})/g, '$1 ').trim();
                if (formattedValue.length === 0) {
                    displayValue = '**** **** **** ****';
                }
                document.getElementById('cardDisplay').textContent = displayValue;
            }
        });

        document.getElementById('portador').addEventListener('input', function(e) {
            let valor = e.target.value.toUpperCase();
            document.getElementById('nameDisplay').textContent = valor || 'NOME DO PORTADOR';
        });

        document.getElementById('validade').addEventListener('input', function(e) {
            let valor = e.target.value.replace(/\D/g, '');
            if (valor.length >= 2) {
                valor = valor.substring(0,2) + '/' + valor.substring(2,4);
            }
            e.target.value = valor;
            document.getElementById('validadeDisplay').textContent = valor || 'MM/AA';
        });

        document.getElementById('cvv').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });
    </script>
</body>
</html>