<?php
session_start();
if (!isset($_SESSION['id'])) { header('Location: index.php'); die(); }

require_once __DIR__ . '/../Controller/ControllerVendas.php';
require_once __DIR__ . '/../Controller/ControllerAvaliacao.php';

$controller = new ControllerVendas();
$controllerAvaliacao = new ControllerAvaliacao();
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../Assets/style.css">
    <style>
        .item-with-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .item-info {
            flex-grow: 1;
        }
        .item-actions {
            display: flex;
            gap: 10px;
        }
        .btn-avaliar {
            font-size: 0.85rem;
            padding: 5px 12px;
        }
        .btn-ver-avaliacoes {
            font-size: 0.85rem;
            padding: 5px 12px;
        }
    </style>
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
                                    <div class="items-list">
                                        <?php foreach ($itens as $item): 
                                            $jaAvaliou = $controllerAvaliacao->usuarioJaAvaliou($userId, $item['tipo_item'], $item['id_item']);
                                            $nomeItem = $controller->resolveItemNome($item['tipo_item'], $item['id_item']);
                                        ?>
                                            <div class="item-with-actions">
                                                <div class="item-info">
                                                    <strong><?php echo ucfirst($item['tipo_item']); ?></strong> - 
                                                    <?php echo $nomeItem; ?> | 
                                                    Qtde: <?php echo $item['quantidade']; ?> | 
                                                    Preço: R$ <?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?>
                                                </div>
                                                <div class="item-actions">
                                                    <a href="VisualizarAvaliacoes.php?tipo=<?php echo $item['tipo_item']; ?>&id=<?php echo $item['id_item']; ?>" 
                                                       class="btn btn-info btn-sm btn-ver-avaliacoes" 
                                                       title="Ver avaliações">
                                                        <i class="fas fa-comments"></i> Ver Avaliações
                                                    </a>
                                                    <?php if (!$jaAvaliou && in_array($pedido['status'], ['processando', 'enviado', 'entregue'])): ?>
                                                        <a href="AvaliarProduto.php?tipo=<?php echo $item['tipo_item']; ?>&id=<?php echo $item['id_item']; ?>" 
                                                           class="btn btn-success btn-sm btn-avaliar" 
                                                           title="Avaliar produto">
                                                            <i class="fas fa-star"></i> Avaliar
                                                        </a>
                                                    <?php elseif ($jaAvaliou): ?>
                                                        <span class="badge badge-secondary">
                                                            <i class="fas fa-check"></i> Já avaliado
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
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
            <div class="list-group">
                <?php foreach ($historicoMoedas as $h): 
                    $jaAvaliou = $controllerAvaliacao->usuarioJaAvaliou($userId, $h['tipo_transacao'], $h['id_item']);
                    $nomeItem = $controller->resolveItemNome($h['tipo_transacao'], $h['id_item']);
                ?>
                    <div class="list-group-item">
                        <div class="item-with-actions">
                            <div class="item-info">
                                <strong><?php echo ucfirst($h['tipo_transacao']); ?></strong> - 
                                <?php echo $nomeItem; ?> | 
                                <?php echo intval($h['valor']); ?> moedas | 
                                <?php echo $h['data_transacao']; ?>
                            </div>
                            <div class="item-actions">
                                <a href="VisualizarAvaliacoes.php?tipo=<?php echo $h['tipo_transacao']; ?>&id=<?php echo $h['id_item']; ?>" 
                                   class="btn btn-info btn-sm btn-ver-avaliacoes" 
                                   title="Ver avaliações">
                                    <i class="fas fa-comments"></i> Ver Avaliações
                                </a>
                                <?php if (!$jaAvaliou): ?>
                                    <a href="AvaliarProduto.php?tipo=<?php echo $h['tipo_transacao']; ?>&id=<?php echo $h['id_item']; ?>" 
                                       class="btn btn-success btn-sm btn-avaliar" 
                                       title="Avaliar produto">
                                        <i class="fas fa-star"></i> Avaliar
                                    </a>
                                <?php else: ?>
                                    <span class="badge badge-secondary">
                                        <i class="fas fa-check"></i> Já avaliado
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
