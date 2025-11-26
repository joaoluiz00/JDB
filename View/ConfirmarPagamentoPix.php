<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    die();
}

require_once '../Controller/ControllerCarrinho.php';
require_once '../Controller/ControllerUsuario.php';
require_once '../Model/BancoDeDados.php';
require_once '../Service/MercadoPagoService.php';

$userController = new ControllerUsuario();
$carrinhoController = new ControllerCarrinho();
$banco = BancoDeDados::getInstance('localhost', 'root', '', 'banco');
$conn = $banco->getConnection();

$userId = $_SESSION['id'];
$user = $userController->readUser($userId);

$tipoCompra = $_POST['tipo_compra'] ?? 'individual';
$documento = preg_replace('/\D/', '', $_POST['documento'] ?? '');
if (empty($documento)) {
    $_SESSION['error'] = 'Informe o CPF para continuar com o pagamento PIX.';
    header('Location: ConfirmarEndereco.php');
    die();
}

$endereco = isset($_POST['endereco']) && is_array($_POST['endereco']) ? $_POST['endereco'] : [];
if (empty($endereco)) {
    $camposEndereco = ['cep', 'rua', 'numero', 'complemento', 'bairro', 'cidade', 'estado'];
    foreach ($camposEndereco as $campo) {
        if (isset($_POST[$campo])) {
            $endereco[$campo] = $_POST[$campo];
        }
    }
}
$descricaoCompra = '';
$resumoItens = [];
$total = 0.0;
$tipoItem = $_POST['tipo_item'] ?? null;
$idItem = intval($_POST['id_item'] ?? 0);
$precoDinheiro = floatval($_POST['preco_dinheiro'] ?? 0);
$idPacoteMoedas = intval($_POST['id_pacote'] ?? 0);
$quantidadeMoedas = intval($_POST['quantidade_moedas'] ?? 0);
$redirectErro = 'Loja.php';

switch ($tipoCompra) {
    case 'carrinho':
        $redirectErro = 'Carrinho.php';
        $itensCarrinho = $carrinhoController->getItensCarrinho($userId);
        if ($itensCarrinho->num_rows === 0) {
            $_SESSION['error'] = 'Seu carrinho está vazio.';
            header('Location: Carrinho.php');
            die();
        }
        $totalCarrinho = $carrinhoController->calcularTotal($userId);
        $desconto = isset($_SESSION['desconto']) ? floatval($_SESSION['desconto']) : 0;
        $total = max($totalCarrinho - $desconto, 0);
        while ($item = $itensCarrinho->fetch_assoc()) {
            if ($item['tipo_pagamento'] !== 'dinheiro') {
                continue;
            }
            $resumoItens[] = [
                'nome' => $item['nome'],
                'quantidade' => $item['quantidade'],
                'valor' => $item['preco_unitario'] * $item['quantidade']
            ];
        }
        $descricaoCompra = 'Compra do carrinho (' . count($resumoItens) . ' itens)';
        break;
    case 'moedas':
        $redirectErro = 'ConfirmarPagamentoMoedas.php';
        $idPacoteMoedas = $idPacoteMoedas ?: intval($_POST['id_item'] ?? 0);
        $stmt = $conn->prepare('SELECT nome_pacote, quantidade_moedas, valor_dinheiro FROM pacotes_moedas WHERE id_pacote = ?');
        $stmt->bind_param('i', $idPacoteMoedas);
        $stmt->execute();
        $result = $stmt->get_result();
        $pacote = $result->fetch_assoc();
        $stmt->close();
        if (!$pacote) {
            $_SESSION['error'] = 'Pacote de moedas inválido.';
            header('Location: ConfirmarPagamentoMoedas.php');
            die();
        }
        $descricaoCompra = 'Pacote de moedas: ' . $pacote['nome_pacote'];
        $quantidadeMoedas = intval($pacote['quantidade_moedas']);
        $total = floatval($pacote['valor_dinheiro']);
        $resumoItens[] = [
            'nome' => $pacote['nome_pacote'],
            'quantidade' => 1,
            'valor' => $total,
            'moedas' => $quantidadeMoedas
        ];
        break;
    default:
        $redirectErro = 'Loja.php';
        $dadosItem = buscarDadosItem($conn, $tipoItem, $idItem);
        if (!$dadosItem) {
            $_SESSION['error'] = 'Item inválido para pagamento.';
            header('Location: ' . $redirectErro);
            die();
        }
        $descricaoCompra = $dadosItem['nome'] . ' (' . ucfirst($tipoItem) . ')';
        $total = floatval($dadosItem['preco']);
        $precoDinheiro = $total;
        $resumoItens[] = [
            'nome' => $dadosItem['nome'],
            'quantidade' => 1,
            'valor' => $total
        ];
        break;
}

if ($total <= 0) {
    $_SESSION['error'] = 'Valor do pagamento inválido.';
    header('Location: ' . $redirectErro);
    die();
}

try {
    $mercadoPago = MercadoPagoService::getInstance();
    $externalReference = 'PIX-' . strtoupper(uniqid());
    $metadata = [
        'tipo_compra' => $tipoCompra,
        'user_id' => $userId,
        'tipo_item' => $tipoItem,
        'id_item' => $idItem,
        'id_pacote_moedas' => $idPacoteMoedas,
        'quantidade_moedas' => $quantidadeMoedas,
        'external_reference' => $externalReference,
    ];

    $zipCode = preg_replace('/\D/', '', $endereco['cep'] ?? '');
    $stateCode = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $endereco['estado'] ?? ''), 0, 2));
    $addressPayload = null;
    if (!empty($stateCode)) {
        $addressPayload = array_filter([
            'zip_code' => $zipCode,
            'street_name' => $endereco['rua'] ?? '',
            'street_number' => $endereco['numero'] ?? '',
            'neighborhood' => $endereco['bairro'] ?? '',
            'city' => $endereco['cidade'] ?? '',
            'federal_unit' => $stateCode,
        ], static fn($value) => $value !== null && $value !== '');
    }

    $pixPayment = $mercadoPago->createPixPayment(
        $total,
        $descricaoCompra,
        [
            'email' => $user->getEmail(),
            'first_name' => $user->getNome(),
            'last_name' => '',
            'identification' => [
                'type' => 'CPF',
                'number' => $documento,
            ],
            'address' => $addressPayload,
        ],
        $metadata
    );
} catch (Exception $e) {
    $_SESSION['error'] = 'Erro ao gerar pagamento PIX: ' . $e->getMessage();
    header('Location: ' . $redirectErro);
    die();
}

$paymentId = (string)($pixPayment['id'] ?? '');
if (empty($paymentId)) {
    $_SESSION['error'] = 'Não foi possível identificar o pagamento PIX.';
    header('Location: ' . $redirectErro);
    die();
}

$transactionData = $pixPayment['point_of_interaction']['transaction_data'] ?? [];
$qrCodeBase64 = $transactionData['qr_code_base64'] ?? '';
$qrCodeString = $transactionData['qr_code'] ?? '';
$ticketUrl = $transactionData['ticket_url'] ?? '';
$hashTransacao = $metadata['external_reference'];
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
            max-width: 900px;
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
        .pix-code {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
            font-family: monospace;
            word-break: break-all;
            margin: 15px 0;
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
        .status-check button {
            margin-top: 10px;
        }
        .qr-image {
            max-width: 260px;
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 10px;
            background: #fff;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-left">
            <a href="<?php echo $tipoCompra === 'carrinho' ? 'Carrinho.php' : ($tipoCompra === 'moedas' ? 'LojaMoedas.php' : 'Loja.php'); ?>" class="btn btn-secondary">Voltar</a>
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
            <p><strong>ID da Transação:</strong> <?php echo htmlspecialchars($hashTransacao); ?></p>
            <ul>
                <?php foreach ($resumoItens as $item): ?>
                    <li><?php echo htmlspecialchars($item['nome']); ?> - <?php echo $item['quantidade']; ?>x (R$ <?php echo number_format($item['valor'], 2, ',', '.'); ?>)
                        <?php if (!empty($item['moedas'])): ?>
                            - <?php echo $item['moedas']; ?> moedas
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php if (!empty($endereco)): ?>
                <div class="endereco-info">
                    <h5>Endereço de Entrega:</h5>
                    <p><?php echo htmlspecialchars(($endereco['rua'] ?? '') . ', ' . ($endereco['numero'] ?? '')); ?>
                       <?php echo !empty($endereco['complemento']) ? ' - ' . htmlspecialchars($endereco['complemento']) : ''; ?></p>
                    <p><?php echo htmlspecialchars(($endereco['bairro'] ?? '') . ' - ' . ($endereco['cidade'] ?? '') . '/' . ($endereco['estado'] ?? '')); ?></p>
                    <p>CEP: <?php echo htmlspecialchars($endereco['cep'] ?? ''); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <div class="qr-code-section">
            <h3>Escaneie o QR Code</h3>
            <?php if ($qrCodeBase64): ?>
                <img src="data:image/png;base64,<?php echo $qrCodeBase64; ?>" alt="QR Code PIX" class="qr-image">
            <?php else: ?>
                <div class="pix-code"><?php echo htmlspecialchars($qrCodeString); ?></div>
            <?php endif; ?>

            <p>Ou copie o código PIX abaixo:</p>
            <div class="pix-code" id="pixCode"><?php echo htmlspecialchars($qrCodeString); ?></div>
            <button class="btn btn-info" id="btnCopiar">📋 Copiar Código PIX</button>
            <?php if ($ticketUrl): ?>
                <div style="margin-top: 10px;">
                    <a href="<?php echo $ticketUrl; ?>" target="_blank">Abrir QR Code em nova aba</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="status-check">
            <div id="status-text">Aguardando confirmação do pagamento...</div>
            <button class="btn btn-outline-primary" id="btnVerificar">Já paguei, verificar agora</button>
        </div>

        <form action="../Processamento/ProcessPagamentoPix.php" method="POST" id="formPagamento">
            <input type="hidden" name="payment_id" value="<?php echo $paymentId; ?>">
            <input type="hidden" name="tipo_compra" value="<?php echo $tipoCompra; ?>">
            <input type="hidden" name="total" value="<?php echo $total; ?>">
            <input type="hidden" name="hash_transacao" value="<?php echo htmlspecialchars($hashTransacao); ?>">
            <?php foreach ($endereco as $key => $value): ?>
                <input type="hidden" name="endereco[<?php echo $key; ?>]" value="<?php echo htmlspecialchars($value); ?>">
            <?php endforeach; ?>
            <?php if ($tipoCompra === 'individual'): ?>
                <input type="hidden" name="id_item" value="<?php echo $idItem; ?>">
                <input type="hidden" name="tipo_item" value="<?php echo htmlspecialchars($tipoItem); ?>">
                <input type="hidden" name="preco_dinheiro" value="<?php echo $precoDinheiro; ?>">
            <?php endif; ?>
            <?php if ($tipoCompra === 'moedas'): ?>
                <input type="hidden" name="id_pacote" value="<?php echo $idPacoteMoedas; ?>">
                <input type="hidden" name="quantidade_moedas" value="<?php echo $quantidadeMoedas; ?>">
            <?php endif; ?>
        </form>
    </div>

    <script>
        const paymentId = '<?php echo $paymentId; ?>';
        const statusText = document.getElementById('status-text');
        const btnVerificar = document.getElementById('btnVerificar');
        const formPagamento = document.getElementById('formPagamento');
        let tentativas = 0;

        document.getElementById('btnCopiar').addEventListener('click', function() {
            const codigo = document.getElementById('pixCode').textContent;
            navigator.clipboard.writeText(codigo).then(function() {
                alert('Código PIX copiado para a área de transferência!');
            });
        });

        async function verificarPagamento(manual = false) {
            try {
                const resposta = await fetch(`../Processamento/ProcessMercadoPagoStatus.php?payment_id=${paymentId}`);
                if (!resposta.ok) {
                    throw new Error('Falha ao consultar status');
                }
                const dados = await resposta.json();
                if (dados.status === 'approved') {
                    statusText.innerHTML = '<strong style="color:green;">Pagamento aprovado! Finalizando pedido...</strong>';
                    formPagamento.submit();
                    return;
                }
                statusText.textContent = `Status atual: ${dados.status}`;
            } catch (error) {
                statusText.textContent = 'Não foi possível verificar o pagamento automaticamente.';
            }

            if (!manual && tentativas < 60) {
                tentativas++;
                setTimeout(verificarPagamento, 5000);
            }
        }

        btnVerificar.addEventListener('click', function() {
            verificarPagamento(true);
        });

        setTimeout(verificarPagamento, 10000);
    </script>
</body>
</html>

<?php
function buscarDadosItem(mysqli $conn, ?string $tipoItem, int $idItem): ?array
{
    if ($idItem <= 0 || !$tipoItem) {
        return null;
    }

    switch ($tipoItem) {
        case 'carta':
            $sql = 'SELECT nome, preco_dinheiro as preco FROM cartas WHERE id = ?';
            break;
        case 'icone':
            $sql = 'SELECT nome, preco_dinheiro as preco FROM img_perfil WHERE id = ?';
            break;
        case 'pacote':
            $sql = 'SELECT nome, preco_dinheiro as preco FROM pacote WHERE id = ?';
            break;
        case 'papel_fundo':
            $sql = 'SELECT nome, preco_dinheiro as preco FROM papel_fundo WHERE id = ?';
            break;
        default:
            return null;
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $idItem);
    $stmt->execute();
    $resultado = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $resultado ?: null;
}
?>
