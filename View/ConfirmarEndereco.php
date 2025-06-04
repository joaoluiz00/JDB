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
    $totalCarrinho = $carrinhoController->calcularTotal($userId);
    
    // Aplicar desconto se houver cupom na sessão
    $desconto = isset($_SESSION['desconto']) ? $_SESSION['desconto'] : 0;
    $totalFinal = $totalCarrinho - $desconto;
    
    if ($itensCarrinho->num_rows == 0) {
        $_SESSION['error'] = "Carrinho vazio!";
        header("Location: Carrinho.php");
        die();
    }
} else {
    // Compra de item individual
    $idCarta = $_GET['id_carta'] ?? null;
    $precoDinheiro = $_GET['preco_dinheiro'] ?? null;
    
    if (!$idCarta || !$precoDinheiro) {
        $_SESSION['error'] = "Dados de compra inválidos!";
        header("Location: Loja.php");
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
    <script>
        function buscarCEP() {
            const cep = document.getElementById('cep').value.replace(/\D/g, '');
            if (cep.length === 8) {
                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.erro) {
                            document.getElementById('rua').value = data.logradouro;
                            document.getElementById('bairro').value = data.bairro;
                            document.getElementById('cidade').value = data.localidade;
                            document.getElementById('estado').value = data.uf;
                        } else {
                            alert('CEP não encontrado.');
                        }
                    })
                    .catch(error => {
                        alert('Erro ao buscar CEP.');
                    });
            }
        }

        function formatarCEP(input) {
            let valor = input.value.replace(/\D/g, '');
            valor = valor.replace(/^(\d{5})(\d)/, '$1-$2');
            input.value = valor;
        }
    </script>
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

    <div class="store-container">
        <div class="store-header">
            <h1 class="store-title">Confirmar Endereço de Entrega</h1>
        </div>

        <?php if ($isCarrinho): ?>
            <div class="resumo-pedido">
                <h3>Resumo do Pedido</h3>
                <?php 
                $itensCarrinho->data_seek(0); // Reset pointer
                while ($item = $itensCarrinho->fetch_assoc()): 
                ?>
                    <div class="item-resumo">
                        <span><?php echo $item['nome']; ?> (<?php echo $item['quantidade']; ?>x)</span>
                        <span>R$ <?php echo number_format($item['quantidade'] * $item['preco_unitario'], 2, ',', '.'); ?></span>
                    </div>
                <?php endwhile; ?>
                
                <?php if ($desconto > 0): ?>
                    <div class="item-resumo desconto">
                        <span>Desconto aplicado:</span>
                        <span>-R$ <?php echo number_format($desconto, 2, ',', '.'); ?></span>
                    </div>
                <?php endif; ?>
                
                <div class="total-resumo">
                    <strong>Total: R$ <?php echo number_format($totalFinal, 2, ',', '.'); ?></strong>
                </div>
            </div>
        <?php else: ?>
            <div class="resumo-pedido">
                <h3>Total da Compra</h3>
                <div class="total-resumo">
                    <strong>R$ <?php echo number_format($totalFinal, 2, ',', '.'); ?></strong>
                </div>
            </div>
        <?php endif; ?>

        <form action="../Processamento/ProcessPagamento.php" method="POST" class="endereco-form">
            <?php if ($isCarrinho): ?>
                <input type="hidden" name="tipo_compra" value="carrinho">
            <?php else: ?>
                <input type="hidden" name="tipo_compra" value="individual">
                <input type="hidden" name="id_carta" value="<?php echo $idCarta; ?>">
                <input type="hidden" name="preco_dinheiro" value="<?php echo $precoDinheiro; ?>">
            <?php endif; ?>

            <div class="form-section">
                <h3>Endereço de Entrega</h3>
                
                <div class="form-group">
                    <label for="cep">CEP:</label>
                    <input type="text" id="cep" name="cep" maxlength="9" required onblur="buscarCEP()" onkeyup="formatarCEP(this)" placeholder="00000-000">
                </div>
                
                <div class="form-row">
                    <div class="form-group col-md-8">
                        <label for="rua">Rua:</label>
                        <input type="text" id="rua" name="rua" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="numero">Número:</label>
                        <input type="text" id="numero" name="numero" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="complemento">Complemento:</label>
                    <input type="text" id="complemento" name="complemento" placeholder="Apartamento, bloco, etc.">
                </div>
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="bairro">Bairro:</label>
                        <input type="text" id="bairro" name="bairro" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="cidade">Cidade:</label>
                        <input type="text" id="cidade" name="cidade" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="estado">Estado:</label>
                    <select id="estado" name="estado" required>
                        <option value="">Selecione o estado</option>
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

            <div class="form-section">
                <h3>Forma de Pagamento</h3>
                <div class="form-group">
                    <select id="pagamento" name="pagamento" required class="form-control">
                        <option value="">Selecione a forma de pagamento</option>
                        <option value="pix">PIX - Aprovação Instantânea</option>
                        <option value="cartao">Cartão de Crédito - Parcelamento disponível</option>
                    </select>
                </div>
            </div>

            <div class="checkout-buttons">
                <button type="submit" class="btn btn-primary btn-lg">Confirmar Compra</button>
                <a href="<?php echo $isCarrinho ? 'Carrinho.php' : 'Loja.php'; ?>" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('pagamento').addEventListener('change', function() {
            if (this.value === 'cartao') {
                <?php if ($isCarrinho): ?>
                    window.location.href = 'AdicionarCartão.php?carrinho=1';
                <?php else: ?>
                    const idCarta = "<?php echo $idCarta ?? ''; ?>";
                    const precoDinheiro = "<?php echo $precoDinheiro ?? ''; ?>";
                    window.location.href = `AdicionarCartão.php?id_carta=${idCarta}&preco_dinheiro=${precoDinheiro}`;
                <?php endif; ?>
            }
        });

        // Impede o envio do formulário se o método de pagamento for "Cartão de Crédito"
        document.querySelector('form').addEventListener('submit', function(event) {
            const pagamento = document.getElementById('pagamento').value;
            if (pagamento === 'cartao') {
                event.preventDefault();
            }
        });

        document.getElementById('pagamento').addEventListener('change', function() {
            if (this.value === 'cartao') {
                <?php if ($isCarrinho): ?>
                    window.location.href = 'AdicionarCartão.php?carrinho=1';
                <?php else: ?>
                    const idCarta = "<?php echo $idCarta ?? ''; ?>";
                    const precoDinheiro = "<?php echo $precoDinheiro ?? ''; ?>";
                    window.location.href = `AdicionarCartão.php?id_carta=${idCarta}&preco_dinheiro=${precoDinheiro}`;
                <?php endif; ?>
            }
        });

        document.querySelector('form').addEventListener('submit', function(event) {
            const pagamento = document.getElementById('pagamento').value;
            
            if (pagamento === 'cartao') {
                event.preventDefault();
                return;
            }
            
            if (pagamento === 'pix') {
                event.preventDefault();
                // Redirecionar para página PIX com os dados necessários
                <?php if ($isCarrinho): ?>
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'ConfirmarPagamentoPix.php';
                    
                    const tipoInput = document.createElement('input');
                    tipoInput.type = 'hidden';
                    tipoInput.name = 'tipo_compra';
                    tipoInput.value = 'carrinho';
                    form.appendChild(tipoInput);
                    
                    const totalInput = document.createElement('input');
                    totalInput.type = 'hidden';
                    totalInput.name = 'total';
                    totalInput.value = '<?php echo $totalFinal; ?>';
                    form.appendChild(totalInput);
                    
                    // Adicionar dados do endereço
                    const enderecoDados = {
                        cep: document.getElementById('cep').value,
                        rua: document.getElementById('rua').value,
                        numero: document.getElementById('numero').value,
                        complemento: document.getElementById('complemento').value,
                        bairro: document.getElementById('bairro').value,
                        cidade: document.getElementById('cidade').value,
                        estado: document.getElementById('estado').value
                    };
                    
                    Object.keys(enderecoDados).forEach(key => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = key;
                        input.value = enderecoDados[key];
                        form.appendChild(input);
                    });
                    
                    document.body.appendChild(form);
                    form.submit();
                <?php else: ?>
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'ConfirmarPagamentoPix.php';
                    
                    const tipoInput = document.createElement('input');
                    tipoInput.type = 'hidden';
                    tipoInput.name = 'tipo_compra';
                    tipoInput.value = 'individual';
                    form.appendChild(tipoInput);
                    
                    const idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'id_item';
                    idInput.value = '<?php echo $idCarta; ?>';
                    form.appendChild(idInput);
                    
                    const precoInput = document.createElement('input');
                    precoInput.type = 'hidden';
                    precoInput.name = 'preco_dinheiro';
                    precoInput.value = '<?php echo $precoDinheiro; ?>';
                    form.appendChild(precoInput);
                    
                    const tipoItemInput = document.createElement('input');
                    tipoItemInput.type = 'hidden';
                    tipoItemInput.name = 'tipo_item';
                    tipoItemInput.value = 'carta';
                    form.appendChild(tipoItemInput);
                    
                    // Adicionar dados do endereço
                    const enderecoDados = {
                        cep: document.getElementById('cep').value,
                        rua: document.getElementById('rua').value,
                        numero: document.getElementById('numero').value,
                        complemento: document.getElementById('complemento').value,
                        bairro: document.getElementById('bairro').value,
                        cidade: document.getElementById('cidade').value,
                        estado: document.getElementById('estado').value
                    };
                    
                    Object.keys(enderecoDados).forEach(key => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = key;
                        input.value = enderecoDados[key];
                        form.appendChild(input);
                    });
                    
                    document.body.appendChild(form);
                    form.submit();
                <?php endif; ?>
            }
        });
    </script>
</body>
</html>