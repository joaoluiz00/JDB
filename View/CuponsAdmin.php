<?php
session_start();
require_once '../Model/BancoDeDados.php';

$banco = BancoDeDados::getInstance();
$conn = $banco->getConnection();

// Buscar cupons ativos
$sql = "SELECT * FROM cupons WHERE ativo = 1 ORDER BY data_fim DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Cupons</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../Assets/style.css">
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Cupons Ativos</h1>
        <a href="AdicionarCupom.php" class="btn btn-success mb-3">Adicionar Novo Cupom</a>
        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Código</th>
                    <th>Descrição</th>
                    <th>Tipo</th>
                    <th>Valor</th>
                    <th>Valor Mínimo</th>
                    <th>Início</th>
                    <th>Fim</th>
                    <th>Uso Máximo</th>
                    <th>Uso Atual</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($cupom = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $cupom['id'] ?></td>
                    <td><?= htmlspecialchars($cupom['codigo']) ?></td>
                    <td><?= htmlspecialchars($cupom['descricao']) ?></td>
                    <td><?= $cupom['tipo_desconto'] ?></td>
                    <td><?= $cupom['valor_desconto'] ?></td>
                    <td><?= $cupom['valor_minimo'] ?></td>
                    <td><?= $cupom['data_inicio'] ?></td>
                    <td><?= $cupom['data_fim'] ?></td>
                    <td><?= $cupom['uso_maximo'] ?></td>
                    <td><?= $cupom['uso_atual'] ?></td>
                    <td>
                        <a href="EditarCupom.php?id=<?= $cupom['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                        <form action="../Processamento/ProcessAdmin.php" method="POST" style="display:inline-block">
                            <input type="hidden" name="action" value="delete_cupom">
                            <input type="hidden" name="id" value="<?= $cupom['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este cupom?')">Excluir</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <a href="HomeAdmin.php" class="btn btn-secondary">Voltar</a>
    </div>
</body>
</html>
