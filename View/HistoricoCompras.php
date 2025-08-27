<?php
session_start();
if (!isset($_SESSION['id'])) { header('Location: index.php'); die(); }

require_once __DIR__ . '/../Controller/ControllerVendas.php';

$controller = new ControllerVendas();
$userId = $_SESSION['id'];
$pedidos = $controller->getPedidosByUsuario($userId);
$historicoMoedas = $controller->getHistoricoMoedasByUsuario($userId);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Compras</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../Assets/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="Home.php">JDB</a>
        <div class="ml-auto">
            <a href="Home.php" class="btn btn-secondary">Voltar</a>
        </div>
    </nav>

    <div class="container mt-4">
        <h1>Histórico de Compras</h1>

        <h3 class="mt-4">Pagamentos com Dinheiro (Cartão/PIX)</h3>
        <?php if (empty($pedidos)): ?>
            <div class="alert alert-info">Nenhuma compra com dinheiro encontrada.</div>
        <?php else: ?>
            <div class="accordion" id="pedidosAccordion">
                <?php foreach ($pedidos as $idx => $pedido): ?>
                    <div class="card">
                        <div class="card-header" id="heading<?php echo $idx; ?>">
                            <h2 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse<?php echo $idx; ?>">
                                    Pedido #<?php echo $pedido['id']; ?> - R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?> - <?php echo strtoupper($pedido['metodo_pagamento']); ?> - <?php echo $pedido['status']; ?> - <?php echo $pedido['data_pedido']; ?>
                                </button>
                            </h2>
                        </div>
                        <div id="collapse<?php echo $idx; ?>" class="collapse" data-parent="#pedidosAccordion">
                            <div class="card-body">
                                <?php $itens = $controller->getItensPedido($pedido['id']); ?>
                                <?php if (empty($itens)): ?>
                                    <em>Sem itens.</em>
                                <?php else: ?>
                                    <ul>
                                        <?php foreach ($itens as $item): ?>
                                            <li>
                                                <?php echo ucfirst($item['tipo_item']); ?> - <?php echo $controller->resolveItemNome($item['tipo_item'], $item['id_item']); ?> | Qtde: <?php echo $item['quantidade']; ?> | Preço: R$ <?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <h3 class="mt-5">Compras com Moedas</h3>
        <?php if (empty($historicoMoedas)): ?>
            <div class="alert alert-info">Nenhuma compra com moedas encontrada.</div>
        <?php else: ?>
            <ul class="list-group">
                <?php foreach ($historicoMoedas as $h): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>
                            <?php echo ucfirst($h['tipo_transacao']); ?> - <?php echo $controller->resolveItemNome($h['tipo_transacao'], $h['id_item']); ?>
                        </span>
                        <span>
                            <?php echo intval($h['valor']); ?> moedas - <?php echo $h['data_transacao']; ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
