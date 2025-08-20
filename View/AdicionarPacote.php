<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Pacote</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../Assets/style.css">
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Adicionar Novo Pacote</h1>
        <form method="POST" action="../Processamento/ProcessAdmin.php">
            <input type="hidden" name="action" value="add_pacote">
            <div class="form-group">
                <label for="nome">Nome do Pacote</label>
                <input type="text" class="form-control" id="nome" name="nome" required>
            </div>
            <div class="form-group">
                <label for="descricao">Descrição</label>
                <textarea class="form-control" id="descricao" name="descricao" required></textarea>
            </div>
            <div class="form-group">
                <label for="imagem">Nome da Imagem (salva em /JDB/Assets/img/)</label>
                <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text">/JDB/Assets/img/</span></div>
                    <input type="text" class="form-control" id="imagem" name="imagem" placeholder="ex: pacote_novo.png" required>
                </div>
                <small class="form-text text-muted">Informe apenas o nome do arquivo.</small>
            </div>
            <div class="form-group">
                <label for="preco">Preço em Moedas</label>
                <input type="number" class="form-control" id="preco" name="preco" required>
            </div>
            <div class="form-group">
                <label for="preco_dinheiro">Preço em Dinheiro</label>
                <input type="number" class="form-control" id="preco_dinheiro" name="preco_dinheiro" required>
            </div>
            <div class="form-group">
                <label for="cor">Cor</label>
                <input type="text" class="form-control" id="cor" name="cor" value="todos">
            </div>
            <button type="submit" class="btn btn-primary">Adicionar Pacote</button>
            <a href="HomeAdmin.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</body>
</html>
