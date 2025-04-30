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
    <title>Adicionar Cartão</title>
    <link rel="stylesheet" href="../Assets/style.css">
</head>
<body>
    <h1>Adicionar Cartão de Crédito</h1>
    <form action="../Processamento/ProcessCartao.php" method="POST">
        <input type="hidden" name="id_carta" value="<?php echo $idCarta; ?>">
        <input type="hidden" name="preco_dinheiro" value="<?php echo $precoDinheiro; ?>">

        <div class="form-group">
            <label for="numero">Número do Cartão:</label>
            <input type="text" id="numero" name="numero" maxlength="16" required>
        </div>
        <div class="form-group">
            <label for="portador">Nome do Portador:</label>
            <input type="text" id="portador" name="portador" required>
        </div>
        <div class="form-group">
            <label for="validade">Validade (MM/AA):</label>
            <input type="text" id="validade" name="validade" maxlength="5" required>
        </div>
        <div class="form-group">
            <label for="cvv">CVV:</label>
            <input type="text" id="cvv" name="cvv" maxlength="3" required>
        </div>

        <button type="submit" class="btn btn-primary">Salvar Cartão e Confirmar Compra</button>
    </form>
</body>
</html>