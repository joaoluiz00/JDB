<?php
session_start();
// Aqui seria ideal verificar se é admin logado; por simplicidade, assumimos acesso direto

require_once __DIR__ . '/../Controller/ControllerVendas.php';

$controller = new ControllerVendas();
$pedidos = $controller->getAllPedidos();
$historicoMoedas = $controller->getAllHistoricoMoedas();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendas - Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../Assets/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="HomeAdmin.php">Admin - JDB</a>
        <div class="ml-auto">
            <a href="HomeAdmin.php" class="btn btn-secondary">Voltar</a>
        </div>
    </nav>

    <div class="container mt-4">
        <h1>Relatório de Vendas</h1>

        <h3 class="mt-4">Pedidos (Cartão/PIX)</h3>
        <?php if (empty($pedidos)): ?>
            <div class="alert alert-info">Nenhum pedido encontrado.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>ID</th>
                            <th>Usuário</th>
                            <th>Email</th>
                            <th>Total</th>
                            <th>Método</th>
                            <th>Status</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidos as $p): ?>
                        <tr>
                            <td><?php echo $p['id']; ?></td>
                            <td><?php echo htmlspecialchars($p['nome_usuario']); ?></td>
                            <td><?php echo htmlspecialchars($p['email']); ?></td>
                            <td>R$ <?php echo number_format($p['total'], 2, ',', '.'); ?></td>
                            <td><?php echo strtoupper($p['metodo_pagamento']); ?></td>
                            <td><?php echo $p['status']; ?></td>
                            <td><?php echo $p['data_pedido']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <h3 class="mt-5">Compras com Moedas</h3>
        <?php if (empty($historicoMoedas)): ?>
            <div class="alert alert-info">Nenhuma compra com moedas encontrada.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>ID</th>
                            <th>Usuário</th>
                            <th>Email</th>
                            <th>Tipo</th>
                            <th>Item</th>
                            <th>Moedas</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historicoMoedas as $h): ?>
                        <tr>
                            <td><?php echo $h['id']; ?></td>
                            <td><?php echo htmlspecialchars($h['nome_usuario']); ?></td>
                            <td><?php echo htmlspecialchars($h['email']); ?></td>
                            <td><?php echo ucfirst($h['tipo_transacao']); ?></td>
                            <td><?php echo htmlspecialchars($controller->resolveItemNome($h['tipo_transacao'], $h['id_item'])); ?></td>
                            <td><?php echo intval($h['valor']); ?></td>
                            <td><?php echo $h['data_transacao']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
