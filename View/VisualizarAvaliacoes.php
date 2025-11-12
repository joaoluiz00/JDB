<?php
session_start();

require_once __DIR__ . '/../Controller/ControllerAvaliacao.php';

$controller = new ControllerAvaliacao();

// Obtém parâmetros
$tipoItem = $_GET['tipo'] ?? null;
$idItem = $_GET['id'] ?? null;

if (!$tipoItem || !$idItem) {
    header('Location: Home.php');
    die();
}

// Valida tipo de item
if (!in_array($tipoItem, ['carta', 'pacote', 'icone', 'papel_fundo'])) {
    header('Location: Home.php');
    die();
}

// Obtém dados do produto
$nomeProduto = $controller->resolveItemNome($tipoItem, $idItem);
$mediaInfo = $controller->getMediaAvaliacoes($tipoItem, $idItem);
$avaliacoes = $controller->getAvaliacoesPorProduto($tipoItem, $idItem);
$resumoIA = $controller->gerarResumoAvaliacoes($tipoItem, $idItem);

// Verifica se usuário está logado e se pode avaliar
$podeAvaliar = false;
if (isset($_SESSION['id'])) {
    $userId = $_SESSION['id'];
    $comprouProduto = $controller->usuarioComprouProduto($userId, $tipoItem, $idItem);
    $jaAvaliou = $controller->usuarioJaAvaliou($userId, $tipoItem, $idItem);
    $podeAvaliar = $comprouProduto && !$jaAvaliou;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avaliações - <?php echo htmlspecialchars($nomeProduto); ?> - JDB</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../Assets/style.css">
    <style>
        .rating-summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .rating-number {
            font-size: 4rem;
            font-weight: bold;
        }
        .stars-display {
            color: #ffc107;
            font-size: 2rem;
        }
        .resumo-ia {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 5px;
        }
        .resumo-ia-title {
            color: #667eea;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .avaliacao-card {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            transition: box-shadow 0.3s;
        }
        .avaliacao-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .user-info {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #667eea;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
            margin-right: 15px;
        }
        .sentimento-badge {
            display: inline-flex;
            align-items: center;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
            margin-left: 10px;
        }
        .sentimento-POSITIVO {
            background: #d4edda;
            color: #155724;
        }
        .sentimento-NEGATIVO {
            background: #f8d7da;
            color: #721c24;
        }
        .sentimento-NEUTRO {
            background: #e2e3e5;
            color: #383d41;
        }
        .avaliacao-imagens {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 15px;
        }
        .avaliacao-imagens img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .avaliacao-imagens img:hover {
            transform: scale(1.05);
        }
        .no-avaliacoes {
            text-align: center;
            padding: 50px;
            color: #999;
        }
        .no-avaliacoes i {
            font-size: 5rem;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="Home.php">JDB</a>
        <div class="ml-auto">
            <?php if (isset($_SESSION['id'])): ?>
                <a href="Perfil.php" class="btn btn-outline-primary mr-2">
                    <i class="fas fa-user"></i> Perfil
                </a>
            <?php endif; ?>
            <a href="javascript:history.back()" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Cabeçalho do Produto -->
        <div class="row mb-4">
            <div class="col-12">
                <h2><i class="fas fa-box"></i> <?php echo htmlspecialchars($nomeProduto); ?></h2>
                <hr>
            </div>
        </div>

        <!-- Resumo de Avaliações -->
        <div class="rating-summary text-center">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="rating-number"><?php echo $mediaInfo['media'] ?: 'N/A'; ?></div>
                    <div class="stars-display">
                        <?php 
                        $media = $mediaInfo['media'];
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= floor($media)) {
                                echo '★';
                            } elseif ($i - 0.5 <= $media) {
                                echo '⯨';
                            } else {
                                echo '☆';
                            }
                        }
                        ?>
                    </div>
                    <p class="mt-2"><?php echo $mediaInfo['total']; ?> avaliações</p>
                </div>
                <div class="col-md-8 text-left">
                    <h5>Distribuição de Notas</h5>
                    <?php
                    $distribuicao = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
                    foreach ($avaliacoes as $av) {
                        $distribuicao[$av->getNota()]++;
                    }
                    foreach ([5, 4, 3, 2, 1] as $nota):
                        $count = $distribuicao[$nota];
                        $percent = $mediaInfo['total'] > 0 ? ($count / $mediaInfo['total']) * 100 : 0;
                    ?>
                        <div class="d-flex align-items-center mb-2">
                            <span style="width: 60px;"><?php echo $nota; ?> ★</span>
                            <div class="progress flex-grow-1 mx-2" style="height: 20px;">
                                <div class="progress-bar bg-warning" style="width: <?php echo $percent; ?>%"></div>
                            </div>
                            <span style="width: 40px;"><?php echo $count; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Botão para Avaliar -->
        <?php if ($podeAvaliar): ?>
            <div class="text-center mb-4">
                <a href="AvaliarProduto.php?tipo=<?php echo $tipoItem; ?>&id=<?php echo $idItem; ?>" 
                   class="btn btn-success btn-lg">
                    <i class="fas fa-star"></i> Avaliar este Produto
                </a>
            </div>
        <?php elseif (isset($_SESSION['id']) && $jaAvaliou): ?>
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle"></i> Você já avaliou este produto.
            </div>
        <?php elseif (isset($_SESSION['id']) && !$comprouProduto): ?>
            <div class="alert alert-warning text-center">
                <i class="fas fa-shopping-cart"></i> Compre este produto para poder avaliá-lo!
            </div>
        <?php endif; ?>

        <!-- Resumo IA -->
        <?php if (!empty($avaliacoes)): ?>
            <div class="resumo-ia">
                <div class="resumo-ia-title">
                    <i class="fas fa-robot"></i> Resumo Inteligente das Avaliações (IA)
                </div>
                <p class="mb-0"><?php echo nl2br(htmlspecialchars($resumoIA)); ?></p>
            </div>
        <?php endif; ?>

        <!-- Lista de Avaliações -->
        <h3 class="mb-4">
            <i class="fas fa-comments"></i> Avaliações dos Clientes 
            <?php if (!empty($avaliacoes)): ?>
                <small class="text-muted">(<?php echo count($avaliacoes); ?>)</small>
            <?php endif; ?>
        </h3>

        <?php if (empty($avaliacoes)): ?>
            <div class="no-avaliacoes">
                <i class="fas fa-comment-slash"></i>
                <h4>Nenhuma avaliação ainda</h4>
                <p>Seja o primeiro a avaliar este produto!</p>
            </div>
        <?php else: ?>
            <?php foreach ($avaliacoes as $avaliacao): ?>
                <div class="avaliacao-card">
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($avaliacao->getNomeUsuario(), 0, 1)); ?>
                        </div>
                        <div class="flex-grow-1">
                            <strong><?php echo htmlspecialchars($avaliacao->getNomeUsuario()); ?></strong>
                            <div class="stars-display" style="font-size: 1.2rem; color: #ffc107;">
                                <?php echo $avaliacao->getNotaEstrelas(); ?>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="sentimento-badge sentimento-<?php echo $avaliacao->getSentimento(); ?>">
                                <span style="font-size: 1.2rem; margin-right: 5px;">
                                    <?php echo $avaliacao->getIconeSentimento(); ?>
                                </span>
                                <?php echo $avaliacao->getSentimento(); ?>
                            </span>
                            <br>
                            <small class="text-muted">
                                <?php 
                                $data = new DateTime($avaliacao->getDataAvaliacao());
                                echo $data->format('d/m/Y H:i'); 
                                ?>
                            </small>
                        </div>
                    </div>

                    <div class="comentario">
                        <p><?php echo nl2br(htmlspecialchars($avaliacao->getComentario())); ?></p>
                    </div>

                    <?php if (!empty($avaliacao->getImagens())): ?>
                        <div class="avaliacao-imagens">
                            <?php foreach ($avaliacao->getImagens() as $imagem): ?>
                                <img src="<?php echo htmlspecialchars($imagem); ?>" 
                                     alt="Imagem da avaliação" 
                                     onclick="window.open(this.src, '_blank')">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
