<?php
require_once '../Model/BancoDeDados.php';

$db = new BancoDeDados('localhost', 'root', '', 'banco');
$usuarios = $db->getUsersList(); // Método que retorna todos os usuários
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../Assets/style.css">
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Gerenciar Usuários</h1>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Moedas</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?= $usuario['id'] ?></td>
                        <td><?= $usuario['nome'] ?></td>
                        <td><?= $usuario['email'] ?></td>
                        <td><?= $usuario['coin'] ?></td>
                        <td>
                            <form method="POST" action="../Processamento/ProcessUsuario.php" style="display: inline;">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                                <button type="submit" class="btn btn-warning btn-sm">Atualizar</button>
                            </form>
                            <form method="POST" action="../Processamento/ProcessUsuario.php" style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este usuário?')">Excluir</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="HomeAdmin.php" class="btn btn-secondary">Voltar</a>
    </div>
</body>
</html>