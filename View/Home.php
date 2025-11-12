<?php
session_start();
require_once __DIR__ . '/../Model/BancoDeDados.php';
require_once __DIR__ . '/../Model/PapelParede.php';

$backgroundUrl = '';
if (isset($_SESSION['id'])) {
    $idUsuario = $_SESSION['id'];
    $conn = BancoDeDados::getInstance()->getConnection();
    $sql = "SELECT pf.path FROM usuario u LEFT JOIN papel_fundo pf ON u.id_papel_fundo = pf.id WHERE u.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $idUsuario);
    $stmt->execute();
    $stmt->bind_result($bgPath);
    $stmt->fetch();
    $stmt->close();
    if ($bgPath) {
        $backgroundUrl = $bgPath;
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - JDB</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../Assets/style.css">
    <link rel="stylesheet" href="../Assets/card.css">
    <link rel="stylesheet" href="../Assets/notificacoes.css">
    <!-- jQuery DEVE ser carregado ANTES do widget -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</head>
<body class="home-background">
    

    <!-- Música de fundo -->
    <audio id="bgMusic" loop hidden>
        <source src="../Assets/music/musicafundo1.mp3" type="audio/mpeg">
    </audio>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="Home.php">
            <img id="themeLogo" src="../Assets/img/logofoto1.png" alt="JDB" class="navbar-logo">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="ml-auto d-flex align-items-center">
            <?php if (isset($_SESSION['id'])): ?>
                <!-- Widget de Notificações -->
                <?php include __DIR__ . '/components/NotificacoesWidget.php'; ?>
            <?php endif; ?>
            
            <button class="theme-toggle ml-2" onclick="toggleTheme()">
                <img src="../Assets/img/modoescuro.PNG" alt="Alternar tema" class="theme-icon dark-icon">
                <img src="../Assets/img/modoclaro.PNG" alt="Alternar tema" class="theme-icon light-icon">
            </button>
        </div>
    </nav>

    <!-- Conteúdo principal -->
    <div class="container mt-5 text-center">
        <div class="row">
            <div class="col-12">
                <h1 class="display-4">Bem-vindo ao JOGO DOS BICHOS!</h1>
                <p class="lead">Explore o mundo das cartas, personalize seu perfil e participe de batalhas épicas!</p>
            </div>
        </div>

        <!-- Cards principais -->
        <div class="row mt-4 justify-content-center">
            <div class="col-12 col-sm-6 col-md-3 mb-4 d-flex justify-content-center">
                <a href="Perfil.php" class="text-decoration-none w-100">
                    <div class="card-custom card-home shadow-lg">
                        <div class="card-body text-center">
                            <img src="../Assets/img/garra.png" alt="Perfil" class="img-fluid mb-3 card-img-home theme-icon-card" data-light="../Assets/img/garra.png" data-dark="../Assets/img/garrabranco.png">
                            <h5 class="card-title">Perfil</h5>
                            <p class="card-text">Gerencie suas informações e personalize seu avatar.</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-md-3 mb-4 d-flex justify-content-center">
                <a href="Batalha.php" class="text-decoration-none w-100">
                    <div class="card-custom card-home shadow-lg">
                        <div class="card-body text-center">
                            <img src="../Assets/img/espada.png" alt="Batalha" class="img-fluid mb-3 card-img-home theme-icon-card" data-light="../Assets/img/espada.png" data-dark="../Assets/img/espadabranco.png">
                            <h5 class="card-title">Batalha</h5>
                            <p class="card-text">Enfrente outros jogadores e prove sua força.</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-md-3 mb-4 d-flex justify-content-center">
                <a href="Loja.php" class="text-decoration-none w-100">
                    <div class="card-custom card-home shadow-lg">
                        <div class="card-body text-center">
                            <img src="../Assets/img/loja.png" alt="Loja" class="img-fluid mb-3 card-img-home theme-icon-card" data-light="../Assets/img/loja.png" data-dark="../Assets/img/lojabranco.png">
                            <h5 class="card-title">Loja</h5>
                            <p class="card-text">Compre cartas, ícones e muito mais.</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-md-3 mb-4 d-flex justify-content-center">
                <a href="HistoricoCompras.php" class="text-decoration-none w-100">
                    <div class="card-custom card-home shadow-lg">
                        <div class="card-body text-center">
                            <img src="../Assets/img/compras.png" alt="Histórico" class="img-fluid mb-3 card-img-home theme-icon-card" data-light="../Assets/img/compras.png" data-dark="../Assets/img/comprasbranco.png">
                            <h5 class="card-title">Histórico de Compras</h5>
                            <p class="card-text">Veja todas as suas compras já realizadas.</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../Assets/script.js"></script>
    <script src="../Assets/js/notificacoes.js"></script>
</body>
</html>