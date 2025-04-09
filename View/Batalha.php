<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../Assets/style.css">
    <title>Document</title>
</head>
<body>
<button class="theme-toggle" onclick="toggleTheme()">🌓</button>
    <audio id="bgMusic" loop hidden>
        <source src="../Assets/music/musicafundo1.mp3" type="audio/mpeg">
    </audio>
 
    <!-- Embedding the YouTube Video -->
    <div class="container mt-5">
        <iframe width="560" height="315" src="https://www.youtube.com/embed/MuBcpzAh4o4" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
    </div>

    <!-- Botão Voltar -->
    <div class="container mt-3">
        <a href="Home.php" class="btn btn-primary">Voltar</a>
    </div>
 
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../Assets/script.js"></script>
</body>
</html>