<?php
require_once '../Controller/ControllerCartas.php';

$controller = new ControllerCartas();
$cartas = $controller->getCartas();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loja de Cartas</title>
    <link rel="stylesheet" href="../Assets/style.css"> <!-- Link para o arquivo CSS -->
</head>
<body>
    <h1>Loja de Cartas</h1>
    <?php if (isset($_GET['success'])): ?>
        <p style="color: green;">Compra realizada com sucesso!</p>
    <?php elseif (isset($_GET['error'])): ?>
        <p style="color: red;">Erro ao realizar a compra. Moedas insuficientes.</p>
    <?php endif; ?>
    <div class="grid">
        <?php while ($carta = $cartas->fetch_assoc()): ?>
            <div class="card">
                <img src="<?php echo $carta['path']; ?>" alt="<?php echo $carta['nome']; ?>">
                <h2><?php echo $carta['nome']; ?></h2>
                <p>Preço: <?php echo $carta['preco']; ?> moedas</p>
                <form method="POST" action="../Processamento/ProcessCartas.php">
                    <input type="hidden" name="id_carta" value="<?php echo $carta['id']; ?>">
                    <input type="hidden" name="preco" value="<?php echo $carta['preco']; ?>">
                    <button type="submit" name="comprar" class="btn-primary">Comprar</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>
    <script src="../Assets/script.js"></script> <!-- Link para o arquivo JavaScript -->
</body>
</html>