<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../Assets/style.css">
</head>
<body>
    <!-- Botão de alternância de tema -->
    <button class="theme-toggle" onclick="toggleTheme()">🌓</button>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="HomeAdmin.php">Admin - JDB</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link btn btn-danger text-white" href="Index.php">Sair</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Conteúdo principal -->
    <div class="container mt-5 text-center">
        <div class="row">
            <div class="col-12">
                <h1 class="display-4">Bem-vindo, Administrador!</h1>
                <p class="lead">Gerencie usuários, cartas e muito mais!</p>
            </div>
        </div>

        <!-- Seções com ícones e links -->
        <div class="row mt-4">
            <div class="col-md-4">
                <a href="GerenciarUsuario.php" class="text-decoration-none">
                    <div class="card-custom shadow-sm">
                        <div class="card-body">
                            <img src="../Assets/img/gerencia.jpg" alt="Gerenciar Usuários" class="img-fluid mb-3" style="max-width: 100px;">
                            <h5 class="card-title">Gerenciar Usuários</h5>
                            <p class="card-text">Adicione, edite ou remova usuários.</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="AdicionarCarta.php" class="text-decoration-none">
                    <div class="card-custom shadow-sm">
                        <div class="card-body">
                            <img src="../Assets/img/adicionar.png" alt="Adicionar Carta" class="img-fluid mb-3" style="max-width: 100px;">
                            <h5 class="card-title">Adicionar Carta</h5>
                            <p class="card-text">Crie novas cartas para o jogo.</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="AdicionarIcone.php" class="text-decoration-none">
                    <div class="card-custom shadow-sm">
                        <div class="card-body">
                            <img src="../Assets/img/logofoto2.png" alt="Adicionar Ícone" class="img-fluid mb-3" style="max-width: 100px;">
                            <h5 class="card-title">Adicionar Ícone</h5>
                            <p class="card-text">Adicione novos ícones de perfil.</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-4">
                <a href="AdicionarPacote.php" class="text-decoration-none">
                    <div class="card-custom shadow-sm">
                        <div class="card-body">
                            <img src="../Assets/img/pacoteGeral.png" alt="Adicionar Pacote" class="img-fluid mb-3" style="max-width: 100px;">
                            <h5 class="card-title">Adicionar Pacote</h5>
                            <p class="card-text">Crie novos pacotes de cartas.</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="AdicionarPapelParede.php" class="text-decoration-none">
                    <div class="card-custom shadow-sm">
                        <div class="card-body">
                            <img src="../Assets/img/papelfundo.jpg" alt="Adicionar Papel de Parede" class="img-fluid mb-3" style="max-width: 100px;">
                            <h5 class="card-title">Adicionar Papel de Parede</h5>
                            <p class="card-text">Adicione novos papéis de parede para perfis.</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="CuponsAdmin.php" class="text-decoration-none">
                    <div class="card-custom shadow-sm">
                        <div class="card-body">
                            <img src="../Assets/img/moeda.png" alt="Gerenciar Cupons" class="img-fluid mb-3" style="max-width: 100px;">
                            <h5 class="card-title">Gerenciar Cupons</h5>
                            <p class="card-text">Crie, edite e exclua cupons de desconto.</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../Assets/script.js"></script>
</body>