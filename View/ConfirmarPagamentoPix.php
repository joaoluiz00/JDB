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
$tipoCompra = $_POST['tipo_compra'] ?? 'individual';
$isCarrinho = ($tipoCompra === 'carrinho');

// Dados do endereço
$endereco = [
    'cep' => $_POST['cep'] ?? '',
    'rua' => $_POST['rua'] ?? '',
    'numero' => $_POST['numero'] ?? '',
    'complemento' => $_POST['complemento'] ?? '',
    'bairro' => $_POST['bairro'] ?? '',
    'cidade' => $_POST['cidade'] ?? '',
    'estado' => $_POST['estado'] ?? ''
];

if ($isCarrinho) {
    $total = $_POST['total'] ?? 0;
    $itensCarrinho = $carrinhoController->getItensCarrinho($userId);
    
    if ($itensCarrinho->num_rows == 0) {
        $_SESSION['error'] = "Carrinho vazio!";
        header("Location: Carrinho.php");
        die();
    }
    
    $descricaoCompra = "Compra do carrinho (" . $itensCarrinho->num_rows . " itens)";
} else {
    $idItem = $_POST['id_item'] ?? null;
    $precoDinheiro = $_POST['preco_dinheiro'] ?? null;
    $tipoItem = $_POST['tipo_item'] ?? 'carta';
    
    if (!$idItem || !$precoDinheiro) {
        $_SESSION['error'] = "Dados de compra inválidos!";
        header("Location: Loja.php");
        die();
    }
    
    $total = $precoDinheiro;
    
    // Buscar nome do item baseado no tipo
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
    
    if (isset($sql)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idItem);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $nomeItem = $row['nome'];
        }
        $stmt->close();
    }
    
    $descricaoCompra = $nomeItem . " (" . ucfirst($tipoItem) . ")";
}

// Gerar código PIX simulado
$codigoPix = "00020126360014BR.GOV.BCB.PIX0114" . $userId . time() . "5204000053039865802BR5925Loja JDB Card Game6009SAO PAULO62070503***6304";
$hashTransacao = md5($userId . time() . $total);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento PIX</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../Assets/loja.css">
    <style>
        .payment-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .qr-code-section {
            text-align: center;
            background: var(--card-bg);
            padding: 30px;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .qr-placeholder {
            width: 200px;
            height: 200px;
            background: #f0f0f0;
            border: 2px dashed #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 14px;
            color: #666;
        }
        
        .pix-code {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
            font-family: monospace;
            word-break: break-all;
            margin: 15px 0;
        }
        
        .copy-button {
            margin-top: 10px;
        }
        
        .payment-info {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .endereco-info {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        
        .status-check {
            text-align: center;
            margin: 30px 0;
        }
        
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
        <div class="payment-header">
            <h1 class="text-center">Pagamento via PIX</h1>
        </div>
        
        <div class="payment-info">
            <h3>Resumo da Compra</h3>
            <p><strong>Descrição:</strong> <?php echo htmlspecialchars($descricaoCompra); ?></p>
            <p><strong>Valor Total:</strong> R$ <?php echo number_format($total, 2, ',', '.'); ?></p>
            <p><strong>ID da Transação:</strong> <?php echo $hashTransacao; ?></p>
            
            <div class="endereco-info">
                <h5>Endereço de Entrega:</h5>
                <p><?php echo $endereco['rua'] . ', ' . $endereco['numero']; ?>
                   <?php echo !empty($endereco['complemento']) ? ' - ' . $endereco['complemento'] : ''; ?></p>
                <p><?php echo $endereco['bairro'] . ' - ' . $endereco['cidade'] . '/' . $endereco['estado']; ?></p>
                <p>CEP: <?php echo $endereco['cep']; ?></p>
            </div>
        </div>

        <div class="qr-code-section">
            <h3>Escaneie o QR Code</h3>
            <div class="qr-placeholder">
                QR Code PIX<br>
                (Simulação)
            </div>
            
            <p>Ou copie o código PIX abaixo:</p>
            <div class="pix-code" id="pixCode"><?php echo $codigoPix; ?></div>
            <button class="btn btn-info copy-button" onclick="copiarCodigo()">📋 Copiar Código PIX</button>
        </div>

        <div class="status-check">
            <div class="loading"></div>
            <p>Aguardando pagamento...</p>
            <small>O status será atualizado automaticamente após a confirmação do pagamento</small>
        </div>

        <form action="../Processamento/ProcessPagamentoPix.php" method="POST" id="formPagamento" style="display: none;">
            <input type="hidden" name="tipo_compra" value="<?php echo $tipoCompra; ?>">
            <input type="hidden" name="hash_transacao" value="<?php echo $hashTransacao; ?>">
            <input type="hidden" name="total" value="<?php echo $total; ?>">
            
            <!-- Dados do endereço -->
            <?php foreach ($endereco as $key => $value): ?>
                <input type="hidden" name="endereco[<?php echo $key; ?>]" value="<?php echo htmlspecialchars($value); ?>">
            <?php endforeach; ?>
            
            <?php if ($isCarrinho): ?>
                <!-- Para carrinho, não precisamos de dados específicos do item -->
            <?php else: ?>
                <input type="hidden" name="id_item" value="<?php echo $idItem; ?>">
                <input type="hidden" name="tipo_item" value="<?php echo $tipoItem; ?>">
            <?php endif; ?>
        </form>

        <div class="text-center" style="margin-top: 30px;">
            <button class="btn btn-success btn-lg" onclick="simularPagamento()">
                💰 Simular Pagamento Aprovado (Para Teste)
            </button>
        </div>
    </div>

    <script>
        function copiarCodigo() {
            const codigo = document.getElementById('pixCode').textContent;
            navigator.clipboard.writeText(codigo).then(function() {
                alert('Código PIX copiado para a área de transferência!');
            });
        }

        function simularPagamento() {
            if (confirm('Simular que o pagamento foi aprovado?')) {
                document.getElementById('formPagamento').submit();
            }
        }

        // Simulação de verificação de pagamento (em produção seria uma consulta real)
        let tentativas = 0;
        const maxTentativas = 60; // 5 minutos (5 segundos * 60)
        
        function verificarPagamento() {
            tentativas++;
            
            // Simula uma chance aleatória de "pagamento aprovado" após algumas tentativas
            if (tentativas > 10 && Math.random() > 0.95) {
                document.querySelector('.status-check').innerHTML = `
                    <div style="color: green;">
                        <h4>✅ Pagamento Aprovado!</h4>
                        <p>Redirecionando...</p>
                    </div>
                `;
                
                setTimeout(() => {
                    document.getElementById('formPagamento').submit();
                }, 2000);
                return;
            }
            
            if (tentativas < maxTentativas) {
                setTimeout(verificarPagamento, 5000); // Verifica a cada 5 segundos
            } else {
                document.querySelector('.status-check').innerHTML = `
                    <div style="color: orange;">
                        <h4>⏰ Tempo limite excedido</h4>
                        <p>Use o botão de simular pagamento ou tente novamente mais tarde.</p>
                    </div>
                `;
            }
        }

        // Inicia a verificação após 10 segundos
        setTimeout(verificarPagamento, 10000);
    </script>
</body>
</html>