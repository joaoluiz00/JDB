<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../Assets/style.css">
    <title>Cadastro Admin</title>
</head>
<body>
    <button class="theme-toggle" onclick="toggleTheme()">🌓</button>
    <div class="container">
        <div class="logo-container">
            <img src="../Assets/img/logofoto1.png" alt="Logo Arcade" id="themeLogo" class="logo">
        </div>

        <div class="card">
            <h2 class="mb-4">NOVO ADMIN</h2>
            <form id="registerAdminForm" action="../Processamento/ProcessAdmin.php" method="POST">
                <input type="hidden" name="action" value="register">
                
                <div class="form-group">
                    <input type="text" class="form-control" id="nome" name="nome" placeholder="Nome do Admin" required>
                </div>

                <div class="form-group">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email do Admin" required>
                </div>

                <div class="form-group">
                    <input type="password" class="form-control" id="password" name="senha" placeholder="Senha" required>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">REGISTRAR</button>
                    <button type="button" class="btn btn-primary" onclick="window.location.href='Index.php'">VOLTAR</button>
                </div>
            </form>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../Assets/script.js"></script>
</body>
</html>