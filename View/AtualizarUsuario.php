<?php
require_once '../Model/BancoDeDados.php';

$db = new BancoDeDados('localhost', 'root', '', 'banco');
$id = $_GET['id'];
$usuario = $db->getUserById($id);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atualizar Usuário</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Atualizar Usuário</h1>
        <form method="POST" action="../Processamento/ProcessUsuario.php">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?= $usuario['nome'] ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= $usuario['email'] ?>" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" class="form-control" id="senha" name="senha" value="<?= $usuario['senha'] ?>" required>
            </div>
            <div class="form-group">
                <label for="coin">Moedas</label>
                <input type="number" class="form-control" id="coin" name="coin" value="<?= $usuario['coin'] ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Salvar</button>
            <a href="GerenciarUsuario.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</body>
</html>