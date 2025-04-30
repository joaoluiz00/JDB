<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    die();
}

$idPacote = $_GET['id_pacote'];
$valorDinheiro = $_GET['valor_dinheiro'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Pagamento</title>
    <link rel="stylesheet" href="../Assets/style.css">
</head>
<body>
    <h1>Confirmar Pagamento</h1>
    <form action="../Processamento/ProcessMoedas.php" method="POST">
        <input type="hidden" name="id_pacote" value="<?php echo $idPacote; ?>">
        <input type="hidden" name="valor_dinheiro" value="<?php echo $valorDinheiro; ?>">

        <div class="form-group">
            <label for="pagamento">Forma de Pagamento:</label>
            <select id="pagamento" name="pagamento" required>
                <option value="cartao">Cartão de Crédito</option>
                <option value="boleto">Boleto Bancário</option>
                <option value="pix">PIX</option>
            </select>
        </div>

        <div id="cartao-info" style="display: none;">
            <div class="form-group">
                <label for="numero">Número do Cartão:</label>
                <input type="text" id="numero" name="numero" maxlength="16">
            </div>
            <div class="form-group">
                <label for="portador">Nome do Portador:</label>
                <input type="text" id="portador" name="portador">
            </div>
            <div class="form-group">
                <label for="validade">Validade (MM/AA):</label>
                <input type="text" id="validade" name="validade" maxlength="5">
            </div>
            <div class="form-group">
                <label for="cvv">CVV:</label>
                <input type="text" id="cvv" name="cvv" maxlength="3">
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Confirmar Compra</button>
    </form>

    <script>
        document.getElementById('pagamento').addEventListener('change', function() {
            const cartaoInfo = document.getElementById('cartao-info');
            cartaoInfo.style.display = this.value === 'cartao' ? 'block' : 'none';
        });
    </script>
</body>
</html>