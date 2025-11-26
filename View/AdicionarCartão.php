<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    die();
}

require_once '../Controller/ControllerCarrinho.php';
require_once '../Controller/ControllerUsuario.php';
require_once '../Service/MercadoPagoService.php';

$userController = new ControllerUsuario();
$carrinhoController = new ControllerCarrinho();
$userId = $_SESSION['id'];
$user = $userController->readUser($userId);
$documento = preg_replace('/\D/', '', $_GET['documento'] ?? '');

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
    $idCarta = $_GET['id_carta'] ?? $_GET['id_icone'] ?? $_GET['id_pacote'] ?? $_GET['id_papel'] ?? null;
    $precoDinheiro = $_GET['preco_dinheiro'] ?? null;
    if (isset($_GET['id_carta'])) {
        $tipoItem = 'carta';
    } elseif (isset($_GET['id_icone'])) {
        $tipoItem = 'icone';
    } elseif (isset($_GET['id_pacote'])) {
        $tipoItem = 'pacote';
    } elseif (isset($_GET['id_papel'])) {
        $tipoItem = 'papel_fundo';
    } else {
        $tipoItem = 'carta';
    }

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
        case 'papel_fundo':
            $sql = "SELECT nome FROM papel_fundo WHERE id = ?";
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

try {
    $mercadoPago = MercadoPagoService::getInstance();
    $publicKey = $mercadoPago->getPublicKey();
} catch (Exception $e) {
    $_SESSION['error'] = 'Configure as credenciais do Mercado Pago antes de continuar. ' . $e->getMessage();
    header('Location: ' . ($isCarrinho ? 'Carrinho.php' : 'Loja.php'));
    die();
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
    <script src="https://sdk.mercadopago.com/js/v2"></script>
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

        .mp-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .mp-row .form-group {
            flex: 1;
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

        <form action="../Processamento/ProcessCartao.php" method="POST" class="card-form" id="paymentForm">
            <input type="hidden" name="tipo_compra" value="<?php echo $isCarrinho ? 'carrinho' : 'individual'; ?>">
            <?php if ($isCarrinho): ?>
                <input type="hidden" name="total" value="<?php echo $valorCompra; ?>">
            <?php else: ?>
                <input type="hidden" name="id_item" value="<?php echo $idCarta; ?>">
                <input type="hidden" name="tipo_item" value="<?php echo $tipoItem; ?>">
                <input type="hidden" name="preco_dinheiro" value="<?php echo $precoDinheiro; ?>">
            <?php endif; ?>

            <input type="hidden" name="token" id="token">
            <input type="hidden" name="payment_method_id" id="paymentMethodId">
            <input type="hidden" name="issuer_id" id="issuerId">
            <input type="hidden" name="installments" id="installmentsHidden">
            <input type="hidden" name="cardholderName" id="cardholderNameHidden">
            <input type="hidden" name="cardholderEmail" id="cardholderEmailHidden">
            <input type="hidden" name="identificationType" id="identificationTypeHidden">
            <input type="hidden" name="identificationNumber" id="identificationNumberHidden">

            <div class="form-group">
                <label for="form-checkout__cardholderName">Nome do Titular</label>
                <input type="text" id="form-checkout__cardholderName" placeholder="NOME COMO NO CARTÃO" required>
            </div>

            <div class="form-group">
                <label for="form-checkout__cardholderEmail">E-mail do Titular</label>
                <input type="email" id="form-checkout__cardholderEmail" placeholder="email@exemplo.com" value="<?php echo htmlspecialchars($user->getEmail()); ?>" required>
            </div>

            <div class="form-group">
                <label for="form-checkout__cardNumber">Número do Cartão</label>
                <input type="text" id="form-checkout__cardNumber" placeholder="1234 5678 9012 3456" required>
            </div>

            <div class="mp-row">
                <div class="form-group">
                    <label for="form-checkout__cardExpirationMonth">Mês</label>
                    <input type="text" id="form-checkout__cardExpirationMonth" placeholder="MM" required>
                </div>
                <div class="form-group">
                    <label for="form-checkout__cardExpirationYear">Ano</label>
                    <input type="text" id="form-checkout__cardExpirationYear" placeholder="AA" required>
                </div>
                <div class="form-group">
                    <label for="form-checkout__securityCode">CVV</label>
                    <input type="text" id="form-checkout__securityCode" placeholder="123" required>
                </div>
            </div>

            <div class="mp-row">
                <div class="form-group">
                    <label for="form-checkout__identificationType">Documento</label>
                    <select id="form-checkout__identificationType" required></select>
                </div>
                <div class="form-group">
                    <label for="form-checkout__identificationNumber">CPF</label>
                    <input type="text" id="form-checkout__identificationNumber" placeholder="00000000000" value="<?php echo $documento; ?>" required>
                </div>
            </div>

            <div class="mp-row">
                <div class="form-group">
                    <label for="form-checkout__issuer">Bandeira</label>
                    <select id="form-checkout__issuer" required></select>
                </div>
                <div class="form-group">
                    <label for="form-checkout__installments">Parcelas</label>
                    <select id="form-checkout__installments" required></select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg btn-block" id="form-checkout__submit">
                💳 Pagar com Cartão
            </button>
        </form>
    </div>

    <script>
        const cardNumberInput = document.getElementById('form-checkout__cardNumber');
        const cardholderInput = document.getElementById('form-checkout__cardholderName');
        const expMonthInput = document.getElementById('form-checkout__cardExpirationMonth');
        const expYearInput = document.getElementById('form-checkout__cardExpirationYear');

        function atualizarNumeroCartao(value) {
            const digits = value.replace(/\s/g, '').replace(/[^0-9]/g, '');
            const formatted = digits.match(/.{1,4}/g)?.join(' ') || digits;
            const displayValue = formatted ? formatted.padEnd(19, '*').replace(/(.{4})/g, '$1 ').trim() : '**** **** **** ****';
            document.getElementById('cardDisplay').textContent = displayValue;
        }

        function atualizarNome(value) {
            document.getElementById('nameDisplay').textContent = value.trim().toUpperCase() || 'NOME DO PORTADOR';
        }

        function atualizarValidade() {
            const mes = expMonthInput.value.padStart(2, '0');
            const ano = expYearInput.value.padStart(2, '0');
            const texto = mes.trim() || ano.trim() ? `${mes}/${ano}` : 'MM/AA';
            document.getElementById('validadeDisplay').textContent = texto;
        }

        cardNumberInput.addEventListener('input', (e) => atualizarNumeroCartao(e.target.value));
        cardholderInput.addEventListener('input', (e) => atualizarNome(e.target.value));
        expMonthInput.addEventListener('input', atualizarValidade);
        expYearInput.addEventListener('input', atualizarValidade);

        const mp = new MercadoPago('<?php echo $publicKey; ?>', { locale: 'pt-BR' });
        const cardForm = mp.cardForm({
            amount: '<?php echo number_format($valorCompra, 2, '.', ''); ?>',
            iframe: true,
            form: {
                id: 'paymentForm',
                cardholderName: { id: 'form-checkout__cardholderName', placeholder: 'Nome como no cartão' },
                cardholderEmail: { id: 'form-checkout__cardholderEmail', placeholder: 'email@example.com' },
                cardNumber: { id: 'form-checkout__cardNumber', placeholder: 'Numero do cartão' },
                cardExpirationMonth: { id: 'form-checkout__cardExpirationMonth', placeholder: 'MM' },
                cardExpirationYear: { id: 'form-checkout__cardExpirationYear', placeholder: 'AA' },
                securityCode: { id: 'form-checkout__securityCode', placeholder: 'CVV' },
                installments: { id: 'form-checkout__installments' },
                identificationType: { id: 'form-checkout__identificationType' },
                identificationNumber: { id: 'form-checkout__identificationNumber', placeholder: 'CPF' },
                issuer: { id: 'form-checkout__issuer' }
            },
            callbacks: {
                onFormMounted: error => {
                    if (error) {
                        console.warn('Erro ao montar formulário:', error);
                    }
                },
                onSubmit: event => {
                    event.preventDefault();
                    const data = cardForm.getCardFormData();
                    document.getElementById('token').value = data.token;
                    document.getElementById('paymentMethodId').value = data.paymentMethodId;
                    document.getElementById('issuerId').value = data.issuerId;
                    document.getElementById('installmentsHidden').value = data.installments;
                    document.getElementById('cardholderNameHidden').value = data.cardholderName;
                    document.getElementById('cardholderEmailHidden').value = data.cardholderEmail;
                    document.getElementById('identificationTypeHidden').value = data.identificationType;
                    document.getElementById('identificationNumberHidden').value = data.identificationNumber;
                    document.getElementById('paymentForm').submit();
                },
                onError: error => {
                    alert('Erro ao processar o cartão: ' + (error?.message || 'tente novamente.'));
                }
            }
        });
    </script>
</body>
</html>