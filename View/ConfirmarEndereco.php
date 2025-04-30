<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    die();
}

$idCarta = $_GET['id_carta'];
$precoDinheiro = $_GET['preco_dinheiro'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Endereço</title>
    <link rel="stylesheet" href="../Assets/style.css">
    <script>
        function buscarCEP() {
            const cep = document.getElementById('cep').value;
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
                    });
            } else {
                alert('CEP inválido.');
            }
        }
    </script>
</head>
<body>
    <h1>Confirmar Endereço</h1>
    <form action="../Processamento/ProcessPagamento.php" method="POST">
        <input type="hidden" name="id_carta" value="<?php echo $idCarta; ?>">
        <input type="hidden" name="preco_dinheiro" value="<?php echo $precoDinheiro; ?>">

        <div class="form-group">
            <label for="cep">CEP:</label>
            <input type="text" id="cep" name="cep" maxlength="8" required onblur="buscarCEP()">
        </div>
        <div class="form-group">
            <label for="rua">Rua:</label>
            <input type="text" id="rua" name="rua" required>
        </div>
        <div class="form-group">
            <label for="numero">Numero:</label>
            <input type="text" id="numero" name="numero" required>
        </div>
        <div class="form-group">
            <label for="bairro">Bairro:</label>
            <input type="text" id="bairro" name="bairro" required>
        </div>
        <div class="form-group">
            <label for="cidade">Cidade:</label>
            <input type="text" id="cidade" name="cidade" required>
        </div>
        <div class="form-group">
            <label for="estado">Estado:</label>
            <input type="text" id="estado" name="estado" required>
        </div>
        <div class="form-group">
            <label for="pagamento">Forma de Pagamento:</label>
            <select id="pagamento" name="pagamento" required>
                <option value="cartao">Cartão de Crédito</option>
                <option value="boleto">Boleto Bancário</option>
                <option value="pix">PIX</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Confirmar Compra</button>
    </form>
</body>
</html>