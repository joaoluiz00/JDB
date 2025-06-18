<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Cupom</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../Assets/style.css">
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Adicionar Novo Cupom</h1>
        <form method="POST" action="../Processamento/ProcessAdmin.php">
            <input type="hidden" name="action" value="add_cupom">
            <div class="form-group">
                <label for="codigo">Código do Cupom</label>
                <input type="text" class="form-control" id="codigo" name="codigo" required>
            </div>
            <div class="form-group">
                <label for="descricao">Descrição</label>
                <input type="text" class="form-control" id="descricao" name="descricao" required>
            </div>
            <div class="form-group">
                <label for="tipo_desconto">Tipo de Desconto</label>
                <select class="form-control" id="tipo_desconto" name="tipo_desconto" required>
                    <option value="percentual">Percentual (%)</option>
                    <option value="valor_fixo">Valor Fixo (R$)</option>
                </select>
            </div>
            <div class="form-group">
                <label for="valor_desconto">Valor do Desconto</label>
                <input type="number" step="0.01" class="form-control" id="valor_desconto" name="valor_desconto" required>
            </div>
            <div class="form-group">
                <label for="valor_minimo">Valor Mínimo para Uso</label>
                <input type="number" step="0.01" class="form-control" id="valor_minimo" name="valor_minimo">
            </div>
            <div class="form-group">
                <label for="data_inicio">Data de Início</label>
                <input type="datetime-local" class="form-control" id="data_inicio" name="data_inicio" required>
            </div>
            <div class="form-group">
                <label for="data_fim">Data de Fim</label>
                <input type="datetime-local" class="form-control" id="data_fim" name="data_fim" required>
            </div>
            <div class="form-group">
                <label for="uso_maximo">Uso Máximo</label>
                <input type="number" class="form-control" id="uso_maximo" name="uso_maximo">
            </div>
            <button type="submit" class="btn btn-primary">Adicionar Cupom</button>
            <a href="HomeAdmin.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</body>
</html>
