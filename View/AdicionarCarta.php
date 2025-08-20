<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Carta</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../Assets/style.css">
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Adicionar Nova Carta</h1>
        <form method="POST" action="../Processamento/ProcessAdmin.php">
            <input type="hidden" name="action" value="add_card">
            <div class="form-group">
                <label for="nome">Nome da Carta</label>
                <input type="text" class="form-control" id="nome" name="nome" required>
            </div>
            <div class="form-group">
                <label for="imagem">Nome da Imagem</label>
                <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text">/JDB/Assets/img/</span></div>
                    <input type="text" class="form-control" id="imagem" name="imagem" placeholder="ex: nova_carta.jpg" required>
                </div>
                <small class="form-text text-muted">Informe apenas o nome do arquivo com extensão.</small>
            </div>
            <div class="form-group">
                <label for="vida">Vida</label>
                <input type="number" class="form-control" id="vida" name="vida" required>
            </div>
            <div class="form-group">
                <label for="ataque1">Nome do Ataque 1</label>
                <input type="text" class="form-control" id="ataque1" name="ataque1" required>
            </div>
            <div class="form-group">
                <label for="ataque1_dano">Dano do Ataque 1</label>
                <input type="number" class="form-control" id="ataque1_dano" name="ataque1_dano" required>
            </div>
            <div class="form-group">
                <label for="ataque2">Nome do Ataque 2</label>
                <input type="text" class="form-control" id="ataque2" name="ataque2">
            </div>
            <div class="form-group">
                <label for="ataque2_dano">Dano do Ataque 2</label>
                <input type="number" class="form-control" id="ataque2_dano" name="ataque2_dano">
            </div>
            <div class="form-group">
                <label for="esquiva">Esquiva</label>
                <input type="number" class="form-control" id="esquiva" name="esquiva" required>
            </div>
            <div class="form-group">
                <label for="critico">Crítico</label>
                <input type="number" class="form-control" id="critico" name="critico" required>
            </div>
            <div class="form-group">
                <label for="preco">Preço</label>
                <input type="number" class="form-control" id="preco" name="preco" required>
            </div>

            <div class="form-group">
                <label for="preco_dinheiro">Preço em Dinheiro</label>
                <input type="number" step="0.01" class="form-control" id="preco_dinheiro" name="preco_dinheiro" required>
            </div>
            <div class="form-group">
                <label for="cor">Cor / Tipo</label>
                <select class="form-control" id="cor" name="cor">
                    <option value="neutro">Neutro</option>
                    <option value="psiquico">Psíquico</option>
                    <option value="dark">Dark</option>
                    <option value="luta">Luta</option>
                    <option value="fogo">Fogo</option>
                    <option value="agua">Água</option>
                    <option value="eletrico">Elétrico</option>
                    <option value="normal">Normal</option>
                    <option value="planta">Planta</option>
                    <option value="metal">Metal</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Adicionar Carta</button>
            <a href="HomeAdmin.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</body>
</html>