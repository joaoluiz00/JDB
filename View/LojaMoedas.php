<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    die();
}

require_once '../Model/BancoDeDados.php';

// Use o método getInstance() para obter a instância Singleton do BancoDeDados
$db = BancoDeDados::getInstance('localhost', 'root', '', 'banco');
$pacotesMoedas = $db->getPacotesMoedas();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loja de Moedas</title>
    <link rel="stylesheet" href="../Assets/style.css">
</head>
<body>
    <h1>Loja de Moedas</h1>
    <div class="coins-grid">
        <?php if ($pacotesMoedas->num_rows > 0): ?>
            <?php while ($pacote = $pacotesMoedas->fetch_assoc()): ?>
                <div class="coin-package">
                    <h2><?php echo $pacote['nome_pacote']; ?></h2>
                    <p>Quantidade de Moedas: <?php echo $pacote['quantidade_moedas']; ?></p>
                    <p>Preço: R$ <?php echo number_format($pacote['valor_dinheiro'], 2, ',', '.'); ?></p>
                    <form action="ConfirmarPagamentoMoedas.php" method="GET">
                        <input type="hidden" name="id_pacote" value="<?php echo $pacote['id_pacote']; ?>">
                        <input type="hidden" name="valor_dinheiro" value="<?php echo $pacote['valor_dinheiro']; ?>">
                        <button type="submit" class="btn btn-primary">Comprar</button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Nenhum pacote de moedas disponível no momento.</p>
        <?php endif; ?>
    </div>
</body>
</html>