<?php
require_once '../Controller/ControllerCartas.php';
require_once '../Controller/ControllerIcone.php';
require_once '../Controller/ControllerUsuario.php';
require_once __DIR__ . '/../Controller/ControllerPapelParede.php';
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../View/");
    exit();
}

$idUsuario = $_SESSION['id'];
$controllerCartas = new ControllerCartas();
$controllerIcone = new ControllerIcone();
$controllerUsuario = new ControllerUsuario();
$controllerPapel = new ControllerPapelParede();

// Inicializa as variáveis
$cartas = null;
$icones = null;
$papeisUsuario = [];

try {
    $cartas = $controllerCartas->getCartasUsuario($idUsuario);
    $icones = $controllerIcone->getIconesUsuario($idUsuario);

    $conn = BancoDeDados::getInstance()->getConnection();
    $result = $conn->query("SELECT pf.* FROM papel_fundo_usuario pfu JOIN papel_fundo pf ON pf.id = pfu.id_papel WHERE pfu.id_usuario = $idUsuario");
    while ($row = $result->fetch_assoc()) {
        $papeisUsuario[] = PapelParede::factory($row);
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Forçar retorno vazio se houver erro
if (!$cartas instanceof mysqli_result) {
    $cartas = new mysqli_result(new mysqli_stmt(), 0);
}
if (!$icones instanceof mysqli_result) {
    $icones = new mysqli_result(new mysqli_stmt(), 0);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Inventário</title>
    <link rel="stylesheet" href="../Assets/style.css">
</head>
<body>
    <style>
            /* Estilos Gerais para os Grids */
            .cards-grid, .icons-grid, .wallpapers-grid {
                display: grid; /* Ativa o layout de grid */
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); /* Define colunas automáticas que se ajustam */
                /* repeat(auto-fill, minmax(180px, 1fr)): Significa que o grid vai criar quantas colunas couberem na tela.
                Cada coluna terá no mínimo 180px de largura e, no máximo, ocupará uma fração igual do espaço disponível (1fr). */
                gap: 20px; /* Espaçamento de 20px entre os itens do grid (horizontal e vertical) */
                margin-bottom: 40px; /* Margem inferior de 40px para separar bem as seções (cartas, ícones, papéis de parede) */
            }

            /* Estilos Comuns para os Itens Individuais (Cartas, Ícones, Papéis de Parede) */
            .card-item, .icon-item, .wallpaper-item {
                background-color: #f8f9fa; /* Cor de fundo bem clara para cada item, quase branco */
                border: 1px solid #e2e6ea; /* Borda fina e discreta ao redor de cada item */
                border-radius: 8px; /* Cantos levemente arredondados para um visual suave */
                padding: 15px; /* Preenchimento interno de 15px para dar espaço ao conteúdo dentro do item */
                text-align: center; /* Centraliza o texto e as imagens dentro do item */
                box-shadow: 0 4px 8px rgba(0,0,0,0.05); /* Uma sombra sutil para dar um efeito de profundidade e destaque */
                transition: transform 0.2s ease-in-out; /* Adiciona uma transição suave para o efeito de "levantar" ao passar o mouse */
            }

            .card-item:hover, .icon-item:hover, .wallpaper-item:hover {
                transform: translateY(-5px); /* Ao passar o mouse, o item "levanta" 5px, criando um efeito interativo */
            }

            /* Estilos das Imagens */
            .card-image, .icon-image, .wallpaper-image {
                max-width: 100%; /* Garante que a imagem nunca ultrapasse a largura do seu contêiner */
                height: 120px; /* Altura fixa para todas as imagens, garantindo uniformidade visual */
                object-fit: contain; /* Redimensiona a imagem para caber na área, mantendo sua proporção original, sem cortar */
                border-radius: 4px; /* Cantos levemente arredondados para as imagens */
                margin-bottom: 10px; /* Margem inferior de 10px abaixo da imagem */
            }

            /* Estilos do Texto */
            .card-item h3, .icon-item h3, .wallpaper-item h4 {
                margin-top: 5px; /* Pequena margem superior */
                margin-bottom: 8px; /* Pequena margem inferior */
                color: #343a40; /* Cor de texto mais escura para os títulos */
            }

            .card-item p {
                font-size: 0.9em; /* Tamanho da fonte ligeiramente menor para os parágrafos */
                color: #6c757d; /* Cor de texto mais clara para os detalhes dos parágrafos */
                margin-bottom: 5px; /* Pequena margem inferior para parágrafos */
            }

            /* Ajustes Gerais de Layout */
            .container {
                max-width: 960px; /* Limita a largura máxima do contêiner principal para melhor leitura em telas grandes */
            }

            h1, h2 {
                color: #343a40; /* Cor de texto escura para os títulos principais */
                margin-bottom: 25px; /* Margem inferior para separar os títulos do conteúdo */
                text-align: center; /* Centraliza todos os títulos (H1 e H2) */
            }

            /* Linha Horizontal para Separação Visual */
            hr {
                border: 0; /* Remove a borda padrão */
                height: 1px; /* Define a altura da linha */
                background-image: linear-gradient(to right, rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.25), rgba(0, 0, 0, 0));
                /* Cria um gradiente que vai de transparente para cinza e volta para transparente,
                dando um efeito de linha mais suave e moderna */
                margin: 40px 0; /* Margem de 40px acima e abaixo da linha para separação */
            }
    </style>
    <div class="container mt-4">
        <h1 class="mb-4">Seu Inventário</h1>

        <h2>Cartas</h2>
        <div class="cards-grid">
            <?php while ($carta = $cartas->fetch_assoc()): ?>
                <div class="card-item">
                    <img src="<?php echo $carta['path']; ?>" alt="<?php echo $carta['nome']; ?>" class="card-image">
                    <h3><?php echo $carta['nome']; ?></h3>
                    <p><strong>Vida:</strong> <?php echo $carta['vida']; ?></p>
                    <p><strong>Ataque 1:</strong> <?php echo $carta['ataque1']; ?> (<?php echo $carta['ataque1_dano']; ?> dano)</p>
                    <p><strong>Ataque 2:</strong> <?php echo $carta['ataque2']; ?> (<?php echo $carta['ataque2_dano']; ?> dano)</p>
                </div>
            <?php endwhile; ?>
        </div>


        <h2>Ícones</h2>
        <div class="icons-grid">
            <?php while ($icone = $icones->fetch_assoc()): ?>
                <div class="icon-item">
                    <img src="<?php echo $icone['path']; ?>" alt="<?php echo $icone['nome']; ?>" class="icon-image">
                    <h3><?php echo $icone['nome']; ?></h3>
                    <form action="../Processamento/ProcessUsuario.php" method="POST">
                        <input type="hidden" name="action" value="set_profile_icon">
                        <input type="hidden" name="id_icone" value="<?php echo $icone['id']; ?>">
                        <button type="submit" class="btn btn-info btn-sm mt-2">Definir como Foto de Perfil</button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>


        <h2>Papéis de Parede</h2>
        <div class="wallpapers-grid">
            <?php foreach ($papeisUsuario as $papel): ?>
                <div class="wallpaper-item">
                    <img src="<?= $papel->getPath() ?>" alt="<?= $papel->getNome() ?>" class="wallpaper-image">
                    <h4><?= $papel->getNome() ?></h4>
                    <form method="post" action="../Processamento/ProcessPapelParede.php">
                        <input type="hidden" name="id_papel" value="<?= $papel->getId() ?>">
                        <button type="submit" name="equipar" class="btn btn-success btn-sm mt-2">Equipar</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>

        <a href="Home.php" class="btn btn-secondary mt-4">Voltar para Home</a>
    </div>
</body>
</html>