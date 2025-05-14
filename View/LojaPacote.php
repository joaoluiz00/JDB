<?php
require_once '../Model/BancoDeDados.php';

$banco = new BancoDeDados('localhost', 'root', '', 'banco');
$pacotes = $banco->getItems(); // Obtém os pacotes da tabela 'pacote'

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loja de Pacotes</title>
    <link rel="stylesheet" href="../Assets/css/style.css">
</head>
<body>
    <h1>Loja de Pacotes</h1>
    <div class="loja-pacotes">
        <?php while ($pacote = $pacotes->fetch_assoc()): ?>
            <div class="pacote">
                <img src="<?= $pacote['path'] ?>" alt="<?= $pacote['nome'] ?>" class="pacote-img">
                <h2><?= $pacote['nome'] ?></h2>
                <p><?= $pacote['descricao'] ?></p>
                <p>Preço em Moedas: <?= $pacote['preco'] ?></p>
                <p>Preço em Dinheiro: R$ <?= number_format($pacote['preco_dinheiro'], 2, ',', '.') ?></p>
                <form action="../Processamento/ProcessPacotes.php" method="POST">
                    <input type="hidden" name="id_pacote" value="<?= $pacote['id'] ?>">
                    <input type="hidden" name="preco" value="<?= $pacote['preco'] ?>">
                    <button type="submit" name="comprar_moedas">Comprar com Moedas</button>
                </form>
                <form action="../Processamento/ProcessPacotes.php" method="POST">
                    <input type="hidden" name="id_pacote" value="<?= $pacote['id'] ?>">
                    <input type="hidden" name="preco_dinheiro" value="<?= $pacote['preco_dinheiro'] ?>">
                    <button type="submit" name="comprar_dinheiro">Comprar com Dinheiro</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>