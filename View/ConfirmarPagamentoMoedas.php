<?php
// Inicia a sessão
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    die();
}

// Verifica se os parâmetros necessários foram enviados
if (!isset($_GET['id_pacote']) || !isset($_GET['valor_dinheiro'])) {
    header("Location: LojaMoedas.php?error=Parâmetros inválidos");
    die();
}

require_once '../Controller/ControllerUsuario.php';
require_once '../Service/MercadoPagoService.php';

// Obtém os dados do usuário
$userController = new ControllerUsuario();
$userId = $_SESSION['id'];
$user = $userController->readUser($userId);
try {
    $mercadoPago = MercadoPagoService::getInstance();
    $publicKey = $mercadoPago->getPublicKey();
} catch (Exception $e) {
    $_SESSION['error'] = 'Configure suas credenciais do Mercado Pago antes de comprar moedas. ' . $e->getMessage();
    header('Location: LojaMoedas.php');
    die();
}
$documento = preg_replace('/\D/', '', $_GET['documento'] ?? '');

// Obtém os dados do pacote
$idPacote = intval($_GET['id_pacote']);
$valorDinheiro = floatval($_GET['valor_dinheiro']);

// Obtém o nome do pacote e quantidade de moedas
require_once '../Model/BancoDeDados.php';
$banco = BancoDeDados::getInstance();
$query = "SELECT nome_pacote, quantidade_moedas FROM pacotes_moedas WHERE id_pacote = ?";
$stmt = $banco->getConnection()->prepare($query);
$stmt->bind_param("i", $idPacote);
$stmt->execute();
$stmt->bind_result($nomePacote, $quantidadeMoedas);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Pagamento</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../Assets/loja.css">
    <script src="https://sdk.mercadopago.com/js/v2"></script>
    <style>
        .payment-container {
            max-width: 800px;
            margin: 50px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .payment-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .payment-details {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
            background-color: #f9f9f9;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .payment-options {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .payment-option {
            flex: 1;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .payment-option.selected {
            background-color: #e9f5ff;
            border-color: #007bff;
        }
        
        .card-fields {
            display: none;
        }
        
        .card-fields.active {
            display: block;
        }

        .mp-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .mp-row .form-group {
            flex: 1;
        }

        .pix-section {
            text-align: center;
            padding: 20px;
        }

        .pix-section button {
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <!-- Navegação fixa -->
    <nav class="navbar">
        <div class="nav-left">
            <a href="LojaMoedas.php" class="btn btn-primary">Voltar para Loja de Moedas</a>
        </div>
        <div class="nav-right">
            <p class="user-coins">Suas moedas: <?php echo $user->getCoin(); ?></p>
        </div>
    </nav>

    <div class="payment-container">
        <div class="payment-header">
            <h1>Confirmar Pagamento</h1>
        </div>
        
        <div class="payment-details">
            <h3>Resumo da compra</h3>
            <p><strong>Pacote:</strong> <?php echo htmlspecialchars($nomePacote); ?></p>
            <p><strong>Quantidade de moedas:</strong> <?php echo $quantidadeMoedas; ?></p>
            <p><strong>Valor:</strong> R$ <?php echo number_format($valorDinheiro, 2, ',', '.'); ?></p>
        </div>
        
        <form action="../Processamento/ProcessCartao.php" method="POST" id="formCartaoMoedas">
            <input type="hidden" name="tipo_compra" value="moedas">
            <input type="hidden" name="id_pacote" value="<?php echo $idPacote; ?>">
            <input type="hidden" name="quantidade_moedas" value="<?php echo $quantidadeMoedas; ?>">
            <input type="hidden" name="valor_dinheiro" value="<?php echo $valorDinheiro; ?>">
            <input type="hidden" name="token" id="token">
            <input type="hidden" name="payment_method_id" id="paymentMethodId">
            <input type="hidden" name="issuer_id" id="issuerId">
            <input type="hidden" name="installments" id="installmentsHidden">
            <input type="hidden" name="cardholderName" id="cardholderNameHidden">
            <input type="hidden" name="cardholderEmail" id="cardholderEmailHidden">
            <input type="hidden" name="identificationType" id="identificationTypeHidden">
            <input type="hidden" name="identificationNumber" id="identificationNumberHidden">
            
            <h3>Selecione a forma de pagamento</h3>
            <div class="payment-options">
                <div class="payment-option selected" id="card-option">
                    <p>Cartão de Crédito</p>
                </div>
                <div class="payment-option" id="pix-option">
                    <p>PIX</p>
                </div>
            </div>

            <div class="form-group">
                <label for="form-checkout__cardholderEmail">E-mail</label>
                <input type="email" class="form-control" id="form-checkout__cardholderEmail" value="<?php echo htmlspecialchars($user->getEmail()); ?>" required>
            </div>

            <div class="form-group">
                <label for="form-checkout__cardholderName">Nome do Titular</label>
                <input type="text" class="form-control" id="form-checkout__cardholderName" placeholder="Nome completo" required>
            </div>

            <div class="form-group">
                <label for="form-checkout__cardNumber">Número do Cartão</label>
                <input type="text" class="form-control" id="form-checkout__cardNumber" placeholder="0000 0000 0000 0000" required>
            </div>

            <div class="mp-row">
                <div class="form-group">
                    <label for="form-checkout__cardExpirationMonth">Mês</label>
                    <input type="text" class="form-control" id="form-checkout__cardExpirationMonth" placeholder="MM" required>
                </div>
                <div class="form-group">
                    <label for="form-checkout__cardExpirationYear">Ano</label>
                    <input type="text" class="form-control" id="form-checkout__cardExpirationYear" placeholder="AA" required>
                </div>
                <div class="form-group">
                    <label for="form-checkout__securityCode">CVV</label>
                    <input type="text" class="form-control" id="form-checkout__securityCode" placeholder="123" required>
                </div>
            </div>

            <div class="mp-row">
                <div class="form-group">
                    <label for="form-checkout__identificationType">Documento</label>
                    <select class="form-control" id="form-checkout__identificationType" required></select>
                </div>
                <div class="form-group">
                    <label for="form-checkout__identificationNumber">CPF</label>
                    <input type="text" class="form-control" id="form-checkout__identificationNumber" placeholder="00000000000" value="<?php echo htmlspecialchars($documento); ?>" required>
                </div>
            </div>

            <div class="mp-row">
                <div class="form-group">
                    <label for="form-checkout__issuer">Bandeira</label>
                    <select class="form-control" id="form-checkout__issuer" required></select>
                </div>
                <div class="form-group">
                    <label for="form-checkout__installments">Parcelas</label>
                    <select class="form-control" id="form-checkout__installments" required></select>
                </div>
            </div>

            <div class="card-fields active" id="card-fields">
                <button type="submit" class="btn btn-primary btn-lg btn-block">
                    💳 Pagar com Cartão (Mercado Pago)
                </button>
            </div>

            <div id="pix-fields" class="card-fields">
                <div class="pix-section">
                    <p>Prefere pagar com PIX? Gere um QR Code aprovado pelo Mercado Pago.</p>
                    <button type="button" class="btn btn-success" id="gerarPixMoedas">Gerar PIX agora</button>
                </div>
            </div>
        </form>
    </div>

    <form id="formPixMoedas" action="ConfirmarPagamentoPix.php" method="POST" style="display:none;">
        <input type="hidden" name="tipo_compra" value="moedas">
        <input type="hidden" name="documento" value="<?php echo htmlspecialchars($documento); ?>">
        <input type="hidden" name="id_pacote" value="<?php echo $idPacote; ?>">
        <input type="hidden" name="quantidade_moedas" value="<?php echo $quantidadeMoedas; ?>">
        <input type="hidden" name="preco_dinheiro" value="<?php echo $valorDinheiro; ?>">
    </form>

    <script>
        const cardOption = document.getElementById('card-option');
        const pixOption = document.getElementById('pix-option');
        const cardFields = document.getElementById('card-fields');
        const pixFields = document.getElementById('pix-fields');
        const btnPix = document.getElementById('gerarPixMoedas');
        const pixDocInput = document.querySelector('#formPixMoedas input[name="documento"]');
        const cpfInput = document.getElementById('form-checkout__identificationNumber');

        function togglePayment(option) {
            const isCard = option === 'card';
            cardOption.classList.toggle('selected', isCard);
            pixOption.classList.toggle('selected', !isCard);
            cardFields.classList.toggle('active', isCard);
            pixFields.classList.toggle('active', !isCard);
        }

        cardOption.addEventListener('click', () => togglePayment('card'));
        pixOption.addEventListener('click', () => togglePayment('pix'));
        togglePayment('card');

        btnPix.addEventListener('click', () => {
            if (cpfInput && pixDocInput) {
                pixDocInput.value = cpfInput.value.replace(/\D/g, '');
            }
            document.getElementById('formPixMoedas').submit();
        });

        const mp = new MercadoPago('<?php echo $publicKey; ?>', { locale: 'pt-BR' });
        const cardForm = mp.cardForm({
            amount: '<?php echo number_format($valorDinheiro, 2, '.', ''); ?>',
            iframe: true,
            form: {
                id: 'formCartaoMoedas',
                cardholderName: { id: 'form-checkout__cardholderName', placeholder: 'Nome completo' },
                cardholderEmail: { id: 'form-checkout__cardholderEmail', placeholder: 'email@exemplo.com' },
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
                    document.getElementById('formCartaoMoedas').submit();
                },
                onError: error => {
                    alert('Erro ao processar o cartão: ' + (error?.message || 'tente novamente.'));
                }
            }
        });
    </script>
</body>
</html>