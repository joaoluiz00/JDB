<?php
/**
 * Widget de Notificações
 * Inclua este arquivo nas páginas onde deseja exibir o sino de notificações
 * Exemplo: include 'View/components/NotificacoesWidget.php';
 */

if (!isset($_SESSION['id'])) {
    return; // Não exibe widget se não estiver logado
}

require_once __DIR__ . '/../../Controller/ControllerNotificacao.php';

$controllerNotif = new ControllerNotificacao();
$totalNaoLidas = $controllerNotif->contarNaoLidas($_SESSION['id']);
$notificacoesRecentes = $controllerNotif->buscarNotificacoesUsuario($_SESSION['id'], 5);
?>

<div class="notificacoes-dropdown">
    <button class="btn btn-link position-relative" id="btnNotificacoes" title="Notificações">
        <i class="fas fa-bell fa-lg"></i>
        <?php if ($totalNaoLidas > 0): ?>
        <span class="notificacoes-badge"><?php echo $totalNaoLidas; ?></span>
        <?php endif; ?>
    </button>

    <div class="notificacoes-menu" id="menuNotificacoes">
        <div class="notificacoes-header">
            <span><i class="fas fa-bell"></i> Notificações</span>
            <button class="btn btn-sm btn-link text-primary" id="btnMarcarTodasLidasWidget">
                <i class="fas fa-check-double"></i>
            </button>
        </div>

        <div class="notificacoes-body">
            <?php if (empty($notificacoesRecentes)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="far fa-bell-slash fa-2x mb-2"></i>
                    <p class="mb-0">Nenhuma notificação</p>
                </div>
            <?php else: ?>
                <?php foreach ($notificacoesRecentes as $notif): ?>
                    <div class="notificacoes-item <?php echo !$notif->isLida() ? 'nao-lida' : ''; ?>" 
                         data-id="<?php echo $notif->getId(); ?>"
                         onclick="abrirNotificacao(<?php echo $notif->getId(); ?>, '<?php echo $notif->getLink(); ?>')">
                        <div class="d-flex align-items-start">
                            <div class="notificacao-icone-mini mr-2" style="background-color: <?php echo $notif->getCorFundo(); ?>">
                                <i class="fas fa-<?php echo $notif->getIcone(); ?>"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block"><?php echo htmlspecialchars($notif->getTitulo()); ?></strong>
                                <small class="text-muted d-block">
                                    <?php echo substr(htmlspecialchars($notif->getMensagem()), 0, 50) . '...'; ?>
                                </small>
                                <small class="text-muted">
                                    <i class="far fa-clock"></i> <?php echo $notif->getTempoDecorrido(); ?>
                                </small>
                            </div>
                            <?php if (!$notif->isLida()): ?>
                            <span class="badge badge-primary badge-pill">!</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="notificacoes-footer">
            <a href="Notificacoes.php">
                <i class="fas fa-list"></i> Ver todas as notificações
            </a>
        </div>
    </div>
</div>

<style>
.notificacao-icone-mini {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.9rem;
    flex-shrink: 0;
}

/* Espaçamento para não colar no botão de alternar tema */
.notificacoes-dropdown {
    margin-right: 30px;
}

.notificacoes-item {
    transition: all 0.2s;
}

.notificacoes-item:last-child {
    border-bottom: none;
}

.btn-link {
    text-decoration: none;
    color: #333;
}

.btn-link:hover {
    color: #007bff;
}

.position-relative {
    position: relative;
}
</style>

<script>
$(document).ready(function() {
    console.log('Widget de notificações carregado!');
    
    // Toggle menu de notificações
    $('#btnNotificacoes').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Botão de notificações clicado!');
        $('#menuNotificacoes').toggleClass('show');
        console.log('Menu tem classe show?', $('#menuNotificacoes').hasClass('show'));
    });

    // Fecha menu ao clicar fora
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.notificacoes-dropdown').length) {
            $('#menuNotificacoes').removeClass('show');
        }
    });

    // Marcar todas como lidas (widget)
    $('#btnMarcarTodasLidasWidget').on('click', function(e) {
        e.stopPropagation();
        marcarTodasComoLidasWidget();
    });

    // Atualiza contador a cada 30 segundos
    setInterval(function() {
        atualizarContadorWidget();
    }, 30000);
});

/**
 * Abre notificação e marca como lida
 */
function abrirNotificacao(idNotificacao, link) {
    // Marca como lida
    $.ajax({
        url: '../Processamento/ProcessNotificacao.php',
        type: 'POST',
        data: {
            action: 'marcar_lida',
            id_notificacao: idNotificacao
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Remove indicador visual
                $(`.notificacoes-item[data-id="${idNotificacao}"]`).removeClass('nao-lida');
                $(`.notificacoes-item[data-id="${idNotificacao}"] .badge-primary`).remove();
                
                // Atualiza contador
                atualizarContadorWidget();
                
                // Redireciona se tiver link
                if (link) {
                    window.location.href = link;
                }
            }
        }
    });
}

/**
 * Marca todas como lidas (widget)
 */
function marcarTodasComoLidasWidget() {
    $.ajax({
        url: '../Processamento/ProcessNotificacao.php',
        type: 'POST',
        data: {
            action: 'marcar_todas_lidas'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Remove indicadores visuais
                $('.notificacoes-item').removeClass('nao-lida');
                $('.notificacoes-item .badge-primary').remove();
                
                // Atualiza contador
                atualizarContadorWidget();
            }
        }
    });
}

/**
 * Atualiza contador de notificações não lidas (widget)
 */
function atualizarContadorWidget() {
    $.ajax({
        url: '../Processamento/ProcessNotificacao.php',
        type: 'GET',
        data: {
            action: 'contar_nao_lidas'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const total = response.total;
                const badge = $('.notificacoes-badge');
                
                if (total > 0) {
                    if (badge.length) {
                        badge.text(total).show();
                    } else {
                        $('#btnNotificacoes').append(`<span class="notificacoes-badge">${total}</span>`);
                    }
                } else {
                    badge.hide();
                }
            }
        }
    });
}
</script>
