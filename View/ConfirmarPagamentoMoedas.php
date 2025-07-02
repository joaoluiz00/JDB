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

// Obtém os dados do usuário
$userController = new ControllerUsuario();
$userId = $_SESSION['id'];
$user = $userController->readUser($userId);

// Obtém os dados do pacote
$idPacote = $_GET['id_pacote'];
$valorDinheiro = $_GET['valor_dinheiro'];

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
        
        <form action="../Processamento/ProcessMoedas.php" method="POST">
            <input type="hidden" name="id_pacote" value="<?php echo $idPacote; ?>">
            <input type="hidden" name="valor_dinheiro" value="<?php echo $valorDinheiro; ?>">
            
            <h3>Selecione a forma de pagamento</h3>
            <div class="payment-options">
                <div class="payment-option" id="card-option">
                    <p>Cartão de Crédito</p>
                </div>
                <div class="payment-option" id="pix-option">
                    <p>PIX</p>
                </div>
            </div>
            
            <input type="hidden" name="pagamento" id="pagamento-input" value="cartao">
            
            <div id="card-fields" class="card-fields active">
                <div class="form-group">
                    <label for="numero">Número do Cartão</label>
                    <input type="text" class="form-control" id="numero" name="numero" placeholder="0000 0000 0000 0000" required>
                </div>
                <div class="form-group">
                    <label for="portador">Nome do Titular</label>
                    <input type="text" class="form-control" id="portador" name="portador" placeholder="Nome como impresso no cartão" required>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="validade">Data de Validade</label>
                        <input type="text" class="form-control" id="validade" name="validade" placeholder="MM/AA" required>
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="cvv">Código de Segurança</label>
                        <input type="text" class="form-control" id="cvv" name="cvv" placeholder="CVV" required>
                    </div>
                </div>
            </div>
            
            <div id="pix-fields" class="card-fields">
                <div class="text-center">
                    <img src="../Assets/img/qrcode-placeholder.png" alt="QR Code PIX" style="width: 200px; margin: 20px auto;">
                    <p>Escaneie o QR Code acima com o aplicativo do seu banco para pagar</p>
                    <p>Código PIX: <strong>12345678901234567890</strong></p>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary btn-lg btn-block">Finalizar Pagamento</button>
        </form>
    </div>

    <script>
        // Script para alternar entre as opções de pagamento
        document.getElementById('card-option').addEventListener('click', function() {
            selectPaymentOption('cartao');
        });
        
        document.getElementById('pix-option').addEventListener('click', function() {
            selectPaymentOption('pix');
        });
        
        function selectPaymentOption(option) {
            document.getElementById('pagamento-input').value = option;
            
            if (option === 'cartao') {
                document.getElementById('card-option').classList.add('selected');
                document.getElementById('pix-option').classList.remove('selected');
                document.getElementById('card-fields').classList.add('active');
                document.getElementById('pix-fields').classList.remove('active');
            } else {
                document.getElementById('pix-option').classList.add('selected');
                document.getElementById('card-option').classList.remove('selected');
                document.getElementById('pix-fields').classList.add('active');
                document.getElementById('card-fields').classList.remove('active');
            }
        }
        
        // Inicializar com cartão selecionado
        selectPaymentOption('cartao');
    </script>
</body>
</html>