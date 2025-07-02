<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Retro</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../Assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
</head>

<body>
    
    <button class="theme-toggle" onclick="toggleTheme()">🌓</button>
    <audio id="bgMusic" loop hidden>
        <source src="../Assets/music/musicafundo1.mp3" type="audio/mpeg">
    </audio>

    <div class="container"> 
        <div class="logo-container">
            <img src="../Assets/img/logofoto1.png" alt="Logo Arcade" id="themeLogo" class="logo">
        </div>

        <div class="card">
            <h2 class="mb-4">LOGIN</h2>
            
            <form id="loginForm" action="../Processamento/ProcessUsuario.php" method="POST">
    <input type="hidden" name="action" value="login">
    
    <div class="form-group">
        <input type="email" class="form-control" id="email" placeholder="EMAIL" name="email" required>
    </div>

    <div class="form-group">
        <input type="password" class="form-control" id="password" placeholder="SENHA" name="senha" required>
    </div>
    
    <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary">START GAME</button>
        <button type="button" class="btn btn-primary" onclick="window.location.href='Cadastro.php'">NEW PLAYER</button>
        <button type="button" class="btn btn-secondary" onclick="window.location.href='LoginAdmin.php'">ADMIN</button>
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