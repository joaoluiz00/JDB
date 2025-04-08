<?php
require_once '../Controller/ControllerCartas.php';

$controller = new ControllerCartas();
$cartas = $controller->getCartas();



// Verifica mensagens da sessão
$showSuccess = isset($_SESSION['success']);
$showError = isset($_SESSION['error']);

// Limpa as mensagens após exibir
if ($showSuccess) unset($_SESSION['success']);
if ($showError) unset($_SESSION['error']);



?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loja de Cartas</title>
    <link rel="stylesheet" href="../Assets/style.css"> 
</head>
<body>
    <div class="store-container">
        <div class="store-header">
            <h1 class="store-title">🏪 Loja de Cartas</h1>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert success">✅ Compra realizada com sucesso!</div>
            <?php elseif (isset($_GET['error'])): ?>
                <div class="alert error">❌ Erro: <?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>
        </div>

        <div class="cards-grid">
            <?php while ($carta = $cartas->fetch_assoc()): ?>
                <div class="card-item">
                    <div class="card-image-container">
                        <img src="<?php echo $carta['path']; ?>" alt="<?php echo $carta['nome']; ?>" class="card-image">
                    </div>
                    <div class="card-details">
                        <h2><?php echo $carta['nome']; ?></h2>
                        <p class="price">💰 <?php echo $carta['preco']; ?> moedas</p>
                        <form method="POST" action="../Processamento/ProcessCartas.php">
                            <input type="hidden" name="id_carta" value="<?php echo $carta['id']; ?>">
                            <input type="hidden" name="preco" value="<?php echo $carta['preco']; ?>">
                            <button type="submit" name="comprar" class="btn-buy">Comprar</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    <script src="../Assets/script.js"></script> 
</body>
</html>