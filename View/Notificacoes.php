<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    die();
}

require_once __DIR__ . '/../Controller/ControllerNotificacao.php';

$controller = new ControllerNotificacao();
$idUsuario = $_SESSION['id'];

// Buscar notificações
$notificacoes = $controller->buscarNotificacoesUsuario($idUsuario, 100);
$totalNaoLidas = $controller->contarNaoLidas($idUsuario);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificações - JDB</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../Assets/style.css">
    <link rel="stylesheet" href="../Assets/notificacoes.css">
</head>
<body>
    <!-- Navbar (você pode incluir a navbar existente do projeto) -->
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <!-- Cabeçalho -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-bell"></i> Notificações</h2>
                    <div>
                        <button class="btn btn-sm btn-info" id="btnMarcarTodasLidas" <?php echo $totalNaoLidas == 0 ? 'disabled' : ''; ?>>
                            <i class="fas fa-check-double"></i> Marcar todas como lidas
                        </button>
                        <a href="Home.php" class="btn btn-sm btn-secondary">
                            <i class="fas fa-home"></i> Voltar
                        </a>
                    </div>
                </div>

                <!-- Badge de não lidas -->
                <?php if ($totalNaoLidas > 0): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Você tem <strong><?php echo $totalNaoLidas; ?></strong> notificação(ões) não lida(s)
                </div>
                <?php endif; ?>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <label>Filtrar por tipo:</label>
                                <select class="form-control form-control-sm" id="filtroTipo">
                                    <option value="">Todos</option>
                                    <option value="compra">Compras</option>
                                    <option value="batalha">Batalhas</option>
                                    <option value="conquista">Conquistas</option>
                                    <option value="presente">Presentes</option>
                                    <option value="sistema">Sistema</option>
                                    <option value="aviso">Avisos</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Filtrar por status:</label>
                                <select class="form-control form-control-sm" id="filtroStatus">
                                    <option value="">Todas</option>
                                    <option value="nao_lida">Não lidas</option>
                                    <option value="lida">Lidas</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>&nbsp;</label>
                                <button class="btn btn-primary btn-sm btn-block" id="btnAplicarFiltros">
                                    <i class="fas fa-filter"></i> Aplicar Filtros
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de Notificações -->
                <div id="listaNotificacoes">
                    <?php if (empty($notificacoes)): ?>
                        <div class="alert alert-light text-center">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Você não tem notificações ainda.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($notificacoes as $notif): ?>
                            <div class="notificacao-card <?php echo $notif->getClasseCSS(); ?> <?php echo !$notif->isLida() ? 'nao-lida' : ''; ?>" 
                                 data-id="<?php echo $notif->getId(); ?>"
                                 data-tipo="<?php echo $notif->getTipo(); ?>"
                                 data-lida="<?php echo $notif->isLida() ? '1' : '0'; ?>">
                                
                                <div class="notificacao-icone" style="background-color: <?php echo $notif->getCorFundo(); ?>">
                                    <i class="fas fa-<?php echo $notif->getIcone(); ?>"></i>
                                </div>
                                
                                <div class="notificacao-conteudo">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h5 class="notificacao-titulo"><?php echo htmlspecialchars($notif->getTitulo()); ?></h5>
                                            <p class="notificacao-mensagem"><?php echo htmlspecialchars($notif->getMensagem()); ?></p>
                                        </div>
                                        <?php if (!$notif->isLida()): ?>
                                        <span class="badge badge-primary">Nova</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="notificacao-footer">
                                        <small class="text-muted">
                                            <i class="far fa-clock"></i> <?php echo $notif->getTempoDecorrido(); ?>
                                        </small>
                                        
                                        <div class="notificacao-acoes">
                                            <?php if ($notif->getLink()): ?>
                                            <a href="<?php echo $notif->getLink(); ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-external-link-alt"></i> Ver
                                            </a>
                                            <?php endif; ?>
                                            
                                            <?php if (!$notif->isLida()): ?>
                                            <button class="btn btn-sm btn-outline-success btn-marcar-lida" data-id="<?php echo $notif->getId(); ?>">
                                                <i class="fas fa-check"></i> Marcar lida
                                            </button>
                                            <?php endif; ?>
                                            
                                            <button class="btn btn-sm btn-outline-danger btn-deletar" data-id="<?php echo $notif->getId(); ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../Assets/js/notificacoes.js"></script>
</body>
</html>
