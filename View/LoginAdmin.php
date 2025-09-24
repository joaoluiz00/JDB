<!DOCTYPE html>
<html lang="pt-br" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Jogo dos Bichos</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../Assets/style.css">
    <link rel="stylesheet" href="../Assets/login.css">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
</head>

<body>
    <!-- Botão de tema com efeito de vidro -->
    <button class="theme-toggle" onclick="toggleTheme()">
        <img src="../Assets/img/modoescuro.PNG" alt="Alternar tema" class="theme-icon dark-icon">
        <img src="../Assets/img/modoclaro.PNG" alt="Alternar tema" class="theme-icon light-icon">
    </button>

    <!-- Música de fundo -->
    <audio id="bgMusic" loop hidden>
        <source src="../Assets/music/musicafundo1.mp3" type="audio/mpeg">
    </audio>

    <!-- Container principal -->
    <div class="container">
        <!-- Logo animada -->
        <div class="logo-container">
            <img src="../Assets/img/logofoto1.png" alt="Logo Jogo dos Bichos" id="themeLogo" class="logo">
        </div>

        <!-- Card de login com efeito de vidro -->
        <div class="card">
            <h2>ADMIN LOGIN</h2>
            
            <form id="loginForm" action="../Processamento/ProcessAdmin.php" method="POST">
                <input type="hidden" name="action" value="login">
                
                <div class="form-group">
                    <input type="email" class="form-control" id="email" placeholder="EMAIL" name="email" required 
                           autocomplete="email">
                </div>

                <div class="form-group">
                    <input type="password" class="form-control" id="password" placeholder="SENHA" name="senha" required
                           autocomplete="current-password">
                </div>
                
                <div class="buttons-container">
                    <button type="submit" class="btn btn-primary">START GAME</button>
                    <button type="button" class="btn btn-primary" onclick="window.location.href='CadastroAdmin.php'">NEW ADMIN</button>
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='index.php'">USER</button>
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