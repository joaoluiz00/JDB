<?php
session_start();
if (!isset($_SESSION['id'])) {
    header('Location: index.php');
    die();
}

require_once '../Controller/ControllerCarrinho.php';
require_once '../Controller/ControllerUsuario.php';

$userController = new ControllerUsuario();
$carrinhoController = new ControllerCarrinho();
$userId = $_SESSION['id'];
$user = $userController->readUser($userId);

$isCarrinho = isset($_GET['carrinho']) && $_GET['carrinho'] === '1';
$documento = preg_replace('/\D/', '', $_GET['documento'] ?? '');
$totalFinal = 0.0;
$tipoItem = null;
$idItem = null;
$precoDinheiro = null;

if ($isCarrinho) {
    $itensCarrinho = $carrinhoController->getItensCarrinho($userId);
    if ($itensCarrinho->num_rows === 0) {
        $_SESSION['error'] = 'Seu carrinho está vazio.';
        header('Location: Carrinho.php');
        die();
    }

    $totalCarrinho = $carrinhoController->calcularTotal($userId);
    $desconto = isset($_SESSION['desconto']) ? floatval($_SESSION['desconto']) : 0.0;
    $totalFinal = max($totalCarrinho - $desconto, 0);
} else {
    if (isset($_GET['id_carta'])) {
        $tipoItem = 'carta';
        $idItem = intval($_GET['id_carta']);
    } elseif (isset($_GET['id_icone'])) {
        $tipoItem = 'icone';
        $idItem = intval($_GET['id_icone']);
    } elseif (isset($_GET['id_pacote'])) {
        $tipoItem = 'pacote';
        $idItem = intval($_GET['id_pacote']);
    } elseif (isset($_GET['id_papel'])) {
        $tipoItem = 'papel_fundo';
        $idItem = intval($_GET['id_papel']);
    } else {
        $tipoItem = $_GET['tipo_item'] ?? 'carta';
        $idItem = intval($_GET['id_item'] ?? 0);
    }

    $precoDinheiro = isset($_GET['preco_dinheiro']) ? floatval($_GET['preco_dinheiro']) : null;
    if ($idItem <= 0 || $precoDinheiro === null) {
        $_SESSION['error'] = 'Dados de compra inválidos.';
        header('Location: Loja.php');
        die();
    }

    $totalFinal = $precoDinheiro;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Endereço</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../Assets/loja.css">
    <style>
        .checkout-container {
            max-width: 900px;
            margin: 30px auto;
            background: var(--card-bg, #fff);
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        .form-section { margin-bottom: 25px; }
        .form-section h3 { margin-bottom: 15px; }
        .checkout-buttons { display: flex; gap: 15px; }
        .checkout-buttons .btn { flex: 1; }
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

    <div class="checkout-container">
        <h1 class="text-center">Confirme seu endereço</h1>
        <p class="text-center">Informe seus dados para continuarmos com o pagamento.</p>

        <div class="alert alert-info">
            <strong>Resumo:</strong>
            <span><?php echo $isCarrinho ? 'Compra do carrinho' : 'Compra individual'; ?></span>
            <span class="float-right">Total: R$ <?php echo number_format($totalFinal, 2, ',', '.'); ?></span>
        </div>

        <form id="checkout-form" data-tipo-compra="<?php echo $isCarrinho ? 'carrinho' : 'individual'; ?>">
            <input type="hidden" id="total_final" value="<?php echo number_format($totalFinal, 2, '.', ''); ?>">
            <?php if (!$isCarrinho): ?>
                <input type="hidden" name="tipo_item" id="tipo_item" value="<?php echo htmlspecialchars($tipoItem); ?>">
                <input type="hidden" name="id_item" id="id_item" value="<?php echo $idItem; ?>">
                <input type="hidden" name="preco_dinheiro" id="preco_dinheiro" value="<?php echo number_format($precoDinheiro, 2, '.', ''); ?>">
            <?php endif; ?>

            <div class="form-section">
                <h3>Dados pessoais</h3>
                <div class="form-group">
                    <label for="cpf">CPF</label>
                    <input type="text" class="form-control" id="cpf" placeholder="000.000.000-00" value="<?php echo htmlspecialchars($documento); ?>" required>
                </div>
            </div>

            <div class="form-section">
                <h3>Endereço</h3>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="cep">CEP</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="cep" placeholder="00000-000" required>
                            <div class="input-group-append">
                                <button class="btn btn-outline-primary" type="button" id="buscarCep">Buscar CEP</button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-8">
                        <label for="rua">Rua</label>
                        <input type="text" class="form-control" id="rua" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="numero">Número</label>
                        <input type="text" class="form-control" id="numero" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="complemento">Complemento</label>
                        <input type="text" class="form-control" id="complemento">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="bairro">Bairro</label>
                        <input type="text" class="form-control" id="bairro" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="cidade">Cidade</label>
                        <input type="text" class="form-control" id="cidade" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="estado">Estado</label>
                        <select id="estado" class="form-control" required>
                            <option value="">Selecione</option>
                            <option value="AC">Acre</option>
                            <option value="AL">Alagoas</option>
                            <option value="AP">Amapá</option>
                            <option value="AM">Amazonas</option>
                            <option value="BA">Bahia</option>
                            <option value="CE">Ceará</option>
                            <option value="DF">Distrito Federal</option>
                            <option value="ES">Espírito Santo</option>
                            <option value="GO">Goiás</option>
                            <option value="MA">Maranhão</option>
                            <option value="MT">Mato Grosso</option>
                            <option value="MS">Mato Grosso do Sul</option>
                            <option value="MG">Minas Gerais</option>
                            <option value="PA">Pará</option>
                            <option value="PB">Paraíba</option>
                            <option value="PR">Paraná</option>
                            <option value="PE">Pernambuco</option>
                            <option value="PI">Piauí</option>
                            <option value="RJ">Rio de Janeiro</option>
                            <option value="RN">Rio Grande do Norte</option>
                            <option value="RS">Rio Grande do Sul</option>
                            <option value="RO">Rondônia</option>
                            <option value="RR">Roraima</option>
                            <option value="SC">Santa Catarina</option>
                            <option value="SP">São Paulo</option>
                            <option value="SE">Sergipe</option>
                            <option value="TO">Tocantins</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3>Forma de pagamento</h3>
                <select id="pagamento" class="form-control" required>
                    <option value="">Selecione</option>
                    <option value="pix">PIX - Aprovação instantânea</option>
                    <option value="cartao">Cartão de crédito</option>
                </select>
            </div>

            <div class="checkout-buttons">
                <button type="submit" class="btn btn-primary btn-lg">Continuar</button>
                <a href="<?php echo $isCarrinho ? 'Carrinho.php' : 'Loja.php'; ?>" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>

    <script>
        const cepInput = document.getElementById('cep');
        const btnBuscarCep = document.getElementById('buscarCep');
        const ruaInput = document.getElementById('rua');
        const bairroInput = document.getElementById('bairro');
        const cidadeInput = document.getElementById('cidade');
        const estadoSelect = document.getElementById('estado');

        async function consultarCep() {
            const cep = cepInput.value.replace(/\D/g, '');
            if (cep.length !== 8) {
                alert('Informe um CEP válido com 8 dígitos.');
                return;
            }
            try {
                const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                if (!response.ok) {
                    throw new Error('Erro ao consultar CEP.');
                }
                const data = await response.json();
                if (data.erro) {
                    alert('CEP não encontrado. Verifique e tente novamente.');
                    return;
                }
                ruaInput.value = data.logradouro || '';
                bairroInput.value = data.bairro || '';
                cidadeInput.value = data.localidade || '';
                estadoSelect.value = data.uf || '';
            } catch (error) {
                alert('Não foi possível consultar o CEP agora. Tente novamente em instantes.');
                console.error(error);
            }
        }

        btnBuscarCep.addEventListener('click', consultarCep);
        cepInput.addEventListener('blur', () => {
            if (cepInput.value.replace(/\D/g, '').length === 8) {
                consultarCep();
            }
        });

        const checkoutForm = document.getElementById('checkout-form');
        const pagamentoSelect = document.getElementById('pagamento');

        checkoutForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const pagamento = pagamentoSelect.value;
            if (!pagamento) {
                alert('Escolha uma forma de pagamento.');
                return;
            }
            if (!validarEndereco()) {
                alert('Preencha todos os campos de endereço.');
                return;
            }
            if (pagamento === 'pix') {
                enviarParaPix();
            } else {
                redirecionarParaCartao();
            }
        });

        function validarEndereco() {
            return ruaInput.value && bairroInput.value && cidadeInput.value && estadoSelect.value && cepInput.value && document.getElementById('numero').value;
        }

        function coletarEndereco() {
            return {
                cep: cepInput.value,
                rua: ruaInput.value,
                numero: document.getElementById('numero').value,
                complemento: document.getElementById('complemento').value,
                bairro: bairroInput.value,
                cidade: cidadeInput.value,
                estado: estadoSelect.value
            };
        }

        function enviarParaPix() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'ConfirmarPagamentoPix.php';
            const tipoCompra = checkoutForm.dataset.tipoCompra;
            adicionarInput(form, 'tipo_compra', tipoCompra);
            adicionarInput(form, 'total', document.getElementById('total_final').value);
            adicionarInput(form, 'documento', document.getElementById('cpf').value.replace(/\D/g, ''));

            if (tipoCompra === 'individual') {
                adicionarInput(form, 'tipo_item', document.getElementById('tipo_item').value);
                adicionarInput(form, 'id_item', document.getElementById('id_item').value);
                adicionarInput(form, 'preco_dinheiro', document.getElementById('preco_dinheiro').value);
            }

            const endereco = coletarEndereco();
            Object.keys(endereco).forEach(key => {
                adicionarInput(form, `endereco[${key}]`, endereco[key]);
            });

            document.body.appendChild(form);
            form.submit();
        }

        function redirecionarParaCartao() {
            const params = new URLSearchParams();
            const tipoCompra = checkoutForm.dataset.tipoCompra;
            const documento = document.getElementById('cpf').value.replace(/\D/g, '');

            if (tipoCompra === 'carrinho') {
                params.append('carrinho', '1');
            } else {
                const tipoItem = document.getElementById('tipo_item').value;
                const idItem = document.getElementById('id_item').value;
                const preco = document.getElementById('preco_dinheiro').value;
                params.append('preco_dinheiro', preco);
                if (tipoItem === 'carta') {
                    params.append('id_carta', idItem);
                } else if (tipoItem === 'icone') {
                    params.append('id_icone', idItem);
                } else if (tipoItem === 'pacote') {
                    params.append('id_pacote', idItem);
                } else if (tipoItem === 'papel_fundo') {
                    params.append('id_papel', idItem);
                } else {
                    params.append('tipo_item', tipoItem);
                    params.append('id_item', idItem);
                }
            }

            if (documento) {
                params.append('documento', documento);
            }

            window.location.href = 'AdicionarCartão.php?' + params.toString();
        }

        function adicionarInput(form, name, value) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            form.appendChild(input);
        }
    </script>
</body>
</html>
