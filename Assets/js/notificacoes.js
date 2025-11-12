/**
 * Sistema de Notificações - JavaScript
 * Gerencia interações e atualizações em tempo real
 */

$(document).ready(function() {
    // Marcar notificação individual como lida
    $(document).on('click', '.btn-marcar-lida', function(e) {
        e.preventDefault();
        const idNotificacao = $(this).data('id');
        marcarComoLida(idNotificacao);
    });

    // Marcar todas como lidas
    $('#btnMarcarTodasLidas').on('click', function() {
        if (confirm('Deseja marcar todas as notificações como lidas?')) {
            marcarTodasComoLidas();
        }
    });

    // Deletar notificação
    $(document).on('click', '.btn-deletar', function(e) {
        e.preventDefault();
        const idNotificacao = $(this).data('id');
        
        if (confirm('Deseja deletar esta notificação?')) {
            deletarNotificacao(idNotificacao);
        }
    });

    // Aplicar filtros
    $('#btnAplicarFiltros').on('click', function() {
        aplicarFiltros();
    });

    // Filtros em tempo real
    $('#filtroTipo, #filtroStatus').on('change', function() {
        aplicarFiltros();
    });
});

/**
 * Marca uma notificação como lida
 */
function marcarComoLida(idNotificacao) {
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
                // Remove badge "Nova"
                const card = $(`.notificacao-card[data-id="${idNotificacao}"]`);
                card.removeClass('nao-lida');
                card.find('.badge-primary').remove();
                card.find('.btn-marcar-lida').remove();
                card.attr('data-lida', '1');
                
                // Atualiza contador
                atualizarContador();
                
                mostrarToast('Notificação marcada como lida', 'success');
            } else {
                mostrarToast('Erro ao marcar notificação', 'error');
            }
        },
        error: function() {
            mostrarToast('Erro de comunicação com servidor', 'error');
        }
    });
}

/**
 * Marca todas as notificações como lidas
 */
function marcarTodasComoLidas() {
    $.ajax({
        url: '../Processamento/ProcessNotificacao.php',
        type: 'POST',
        data: {
            action: 'marcar_todas_lidas'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Remove todas as badges e botões
                $('.notificacao-card').removeClass('nao-lida');
                $('.badge-primary').remove();
                $('.btn-marcar-lida').remove();
                $('.notificacao-card').attr('data-lida', '1');
                
                // Desabilita botão
                $('#btnMarcarTodasLidas').prop('disabled', true);
                
                // Atualiza contador
                atualizarContador();
                
                mostrarToast('Todas as notificações foram marcadas como lidas', 'success');
            } else {
                mostrarToast('Erro ao marcar notificações', 'error');
            }
        },
        error: function() {
            mostrarToast('Erro de comunicação com servidor', 'error');
        }
    });
}

/**
 * Deleta uma notificação
 */
function deletarNotificacao(idNotificacao) {
    $.ajax({
        url: '../Processamento/ProcessNotificacao.php',
        type: 'POST',
        data: {
            action: 'deletar',
            id_notificacao: idNotificacao
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Remove do DOM com animação
                const card = $(`.notificacao-card[data-id="${idNotificacao}"]`);
                card.fadeOut(300, function() {
                    $(this).remove();
                    
                    // Verifica se ficou vazio
                    if ($('.notificacao-card').length === 0) {
                        $('#listaNotificacoes').html(`
                            <div class="alert alert-light text-center">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Você não tem notificações.</p>
                            </div>
                        `);
                    }
                });
                
                // Atualiza contador
                atualizarContador();
                
                mostrarToast('Notificação deletada', 'success');
            } else {
                mostrarToast('Erro ao deletar notificação', 'error');
            }
        },
        error: function() {
            mostrarToast('Erro de comunicação com servidor', 'error');
        }
    });
}

/**
 * Aplica filtros de tipo e status
 */
function aplicarFiltros() {
    const tipo = $('#filtroTipo').val();
    const status = $('#filtroStatus').val();
    
    $('.notificacao-card').each(function() {
        const card = $(this);
        const tipoCard = card.data('tipo');
        const lidaCard = card.data('lida') == '1';
        
        let mostrar = true;
        
        // Filtro de tipo
        if (tipo && tipoCard !== tipo) {
            mostrar = false;
        }
        
        // Filtro de status
        if (status === 'nao_lida' && lidaCard) {
            mostrar = false;
        }
        if (status === 'lida' && !lidaCard) {
            mostrar = false;
        }
        
        if (mostrar) {
            card.fadeIn(300);
        } else {
            card.fadeOut(300);
        }
    });
}

/**
 * Atualiza contador de notificações não lidas
 */
function atualizarContador() {
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
                
                // Atualiza badge no ícone do sino (se existir)
                if (total > 0) {
                    $('.notificacoes-badge').text(total).show();
                } else {
                    $('.notificacoes-badge').hide();
                }
                
                // Atualiza alert
                const alert = $('.alert-info');
                if (total > 0) {
                    alert.html(`<i class="fas fa-info-circle"></i> Você tem <strong>${total}</strong> notificação(ões) não lida(s)`);
                    alert.show();
                } else {
                    alert.hide();
                }
            }
        }
    });
}

/**
 * Mostra toast de feedback
 */
function mostrarToast(mensagem, tipo = 'info') {
    // Se não tiver biblioteca de toast, usa alert simples
    // Para produção, recomenda-se usar Toastr ou similar
    
    const icones = {
        'success': '✓',
        'error': '✗',
        'info': 'ℹ'
    };
    
    const cores = {
        'success': '#28a745',
        'error': '#dc3545',
        'info': '#17a2b8'
    };
    
    const toast = $(`
        <div class="custom-toast" style="
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${cores[tipo]};
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 9999;
            animation: slideInRight 0.3s ease;
        ">
            <strong>${icones[tipo]}</strong> ${mensagem}
        </div>
    `);
    
    $('body').append(toast);
    
    setTimeout(function() {
        toast.fadeOut(300, function() {
            $(this).remove();
        });
    }, 3000);
}

// CSS para animação
if (!$('#toast-animation-style').length) {
    $('head').append(`
        <style id="toast-animation-style">
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
        </style>
    `);
}
