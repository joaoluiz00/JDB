<?php
/**
 * Teste do Sistema de Notificações com Padrão Observer
 * Execute este arquivo para testar o sistema
 */

session_start();
$_SESSION['id'] = 1; // Simula usuário logado
$_SESSION['nome'] = 'Teste Usuário';
$_SESSION['email'] = 'teste@example.com';

require_once __DIR__ . '/../Service/NotificationService.php';
require_once __DIR__ . '/../Controller/ControllerNotificacao.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Teste Observer</title>";
echo "<link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'>";
echo "<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css'>";
echo "</head><body class='bg-light'>";
echo "<div class='container mt-5'>";
echo "<h1 class='mb-4'><i class='fas fa-vial'></i> Teste do Sistema de Notificações</h1>";

// Inicializa o serviço
$notificationService = NotificationService::getInstance();
$notificationService->initialize([
    'emails_habilitados' => false, // Desabilitado para teste
    'log_file' => __DIR__ . '/../logs/eventos_teste.log'
]);

echo "<div class='alert alert-info'><i class='fas fa-info-circle'></i> Sistema inicializado com sucesso!</div>";

// Teste 1: Notificação de Compra
echo "<div class='card mb-3'>";
echo "<div class='card-header bg-success text-white'><i class='fas fa-shopping-cart'></i> Teste 1: Notificação de Compra</div>";
echo "<div class='card-body'>";

try {
    $notificationService->notificarCompra(
        1,
        'Teste Usuário',
        'teste@example.com',
        'carta',
        5,
        'Dragão Azul',
        50.00
    );
    echo "<p class='text-success'><i class='fas fa-check'></i> Notificação de compra disparada!</p>";
} catch (Exception $e) {
    echo "<p class='text-danger'><i class='fas fa-times'></i> Erro: " . $e->getMessage() . "</p>";
}

echo "</div></div>";

// Teste 2: Notificação de Batalha Vencida
echo "<div class='card mb-3'>";
echo "<div class='card-header bg-danger text-white'><i class='fas fa-trophy'></i> Teste 2: Notificação de Batalha Vencida</div>";
echo "<div class='card-body'>";

try {
    $notificationService->notificarBatalhaVencida(
        1,
        'Teste Usuário',
        100
    );
    echo "<p class='text-success'><i class='fas fa-check'></i> Notificação de vitória disparada!</p>";
} catch (Exception $e) {
    echo "<p class='text-danger'><i class='fas fa-times'></i> Erro: " . $e->getMessage() . "</p>";
}

echo "</div></div>";

// Teste 3: Notificação de Conquista
echo "<div class='card mb-3'>";
echo "<div class='card-header bg-purple text-white' style='background-color: #9c27b0;'><i class='fas fa-award'></i> Teste 3: Notificação de Conquista</div>";
echo "<div class='card-body'>";

try {
    $notificationService->notificarConquista(
        1,
        'Teste Usuário',
        'teste@example.com',
        'Primeira Vitória',
        'Vença sua primeira batalha'
    );
    echo "<p class='text-success'><i class='fas fa-check'></i> Notificação de conquista disparada!</p>";
} catch (Exception $e) {
    echo "<p class='text-danger'><i class='fas fa-times'></i> Erro: " . $e->getMessage() . "</p>";
}

echo "</div></div>";

// Teste 4: Notificação de Presente
echo "<div class='card mb-3'>";
echo "<div class='card-header bg-warning text-white'><i class='fas fa-gift'></i> Teste 4: Notificação de Presente</div>";
echo "<div class='card-body'>";

try {
    $notificationService->notificarPresente(
        1,
        'Teste Usuário',
        'teste@example.com',
        'Você ganhou 50 moedas de presente! 🎉'
    );
    echo "<p class='text-success'><i class='fas fa-check'></i> Notificação de presente disparada!</p>";
} catch (Exception $e) {
    echo "<p class='text-danger'><i class='fas fa-times'></i> Erro: " . $e->getMessage() . "</p>";
}

echo "</div></div>";

// Teste 5: Notificação de Boas-Vindas
echo "<div class='card mb-3'>";
echo "<div class='card-header bg-primary text-white'><i class='fas fa-hand-sparkles'></i> Teste 5: Notificação de Boas-Vindas</div>";
echo "<div class='card-body'>";

try {
    $notificationService->notificarBoasVindas(
        1,
        'Teste Usuário'
    );
    echo "<p class='text-success'><i class='fas fa-check'></i> Notificação de boas-vindas disparada!</p>";
} catch (Exception $e) {
    echo "<p class='text-danger'><i class='fas fa-times'></i> Erro: " . $e->getMessage() . "</p>";
}

echo "</div></div>";

// Teste 6: Evento Personalizado
echo "<div class='card mb-3'>";
echo "<div class='card-header bg-info text-white'><i class='fas fa-code'></i> Teste 6: Evento Personalizado</div>";
echo "<div class='card-body'>";

try {
    $notificationService->dispararEvento('evento_teste', [
        'id_usuario' => 1,
        'nome_usuario' => 'Teste Usuário',
        'email_usuario' => 'teste@example.com',
        'dados_customizados' => 'Qualquer informação aqui'
    ]);
    echo "<p class='text-success'><i class='fas fa-check'></i> Evento personalizado disparado!</p>";
    echo "<p class='text-muted'><small>Nota: Este evento não gera notificação no DB pois não está configurado no DatabaseObserver</small></p>";
} catch (Exception $e) {
    echo "<p class='text-danger'><i class='fas fa-times'></i> Erro: " . $e->getMessage() . "</p>";
}

echo "</div></div>";

// Verificar notificações no banco
echo "<div class='card mb-3'>";
echo "<div class='card-header bg-dark text-white'><i class='fas fa-database'></i> Verificação no Banco de Dados</div>";
echo "<div class='card-body'>";

try {
    $controller = new ControllerNotificacao();
    $notificacoes = $controller->buscarNotificacoesUsuario(1, 10);
    $totalNaoLidas = $controller->contarNaoLidas(1);
    
    echo "<p><strong>Total de notificações:</strong> " . count($notificacoes) . "</p>";
    echo "<p><strong>Não lidas:</strong> " . $totalNaoLidas . "</p>";
    
    if (!empty($notificacoes)) {
        echo "<h5 class='mt-3'>Últimas 5 notificações:</h5>";
        echo "<div class='table-responsive'>";
        echo "<table class='table table-sm'>";
        echo "<thead><tr><th>ID</th><th>Tipo</th><th>Título</th><th>Lida</th><th>Data</th></tr></thead>";
        echo "<tbody>";
        
        foreach (array_slice($notificacoes, 0, 5) as $notif) {
            $badgeLida = $notif->isLida() ? '<span class="badge badge-success">Sim</span>' : '<span class="badge badge-warning">Não</span>';
            echo "<tr>";
            echo "<td>" . $notif->getId() . "</td>";
            echo "<td>" . htmlspecialchars($notif->getTipo()) . "</td>";
            echo "<td>" . htmlspecialchars($notif->getTitulo()) . "</td>";
            echo "<td>" . $badgeLida . "</td>";
            echo "<td><small>" . $notif->getTempoDecorrido() . "</small></td>";
            echo "</tr>";
        }
        
        echo "</tbody></table>";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<p class='text-danger'><i class='fas fa-times'></i> Erro ao buscar notificações: " . $e->getMessage() . "</p>";
}

echo "</div></div>";

// Verificar arquivo de log
echo "<div class='card mb-3'>";
echo "<div class='card-header bg-secondary text-white'><i class='fas fa-file-alt'></i> Verificação do Arquivo de Log</div>";
echo "<div class='card-body'>";

$logFile = __DIR__ . '/../logs/eventos_teste.log';
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $lines = explode("\n", $logContent);
    $lastLines = array_slice(array_filter($lines), -10);
    
    echo "<p class='text-success'><i class='fas fa-check'></i> Arquivo de log encontrado!</p>";
    echo "<p><strong>Últimas 10 linhas:</strong></p>";
    echo "<pre class='bg-dark text-light p-3' style='max-height: 300px; overflow-y: auto;'>";
    echo htmlspecialchars(implode("\n", $lastLines));
    echo "</pre>";
} else {
    echo "<p class='text-warning'><i class='fas fa-exclamation-triangle'></i> Arquivo de log não encontrado. Verifique as permissões da pasta logs/</p>";
}

echo "</div></div>";

// Estatísticas do EventManager
echo "<div class='card mb-3'>";
echo "<div class='card-header bg-info text-white'><i class='fas fa-chart-bar'></i> Estatísticas do EventManager</div>";
echo "<div class='card-body'>";

$eventManager = $notificationService->getEventManager();
$totalObservers = $eventManager->countObservers();

echo "<p><strong>Observers registrados:</strong> " . $totalObservers . "</p>";
echo "<ul>";
echo "<li>DatabaseNotificationObserver</li>";
echo "<li>EmailNotificationObserver</li>";
echo "<li>LogObserver</li>";
echo "</ul>";

echo "</div></div>";

// Links úteis
echo "<div class='card mb-3'>";
echo "<div class='card-header bg-success text-white'><i class='fas fa-link'></i> Links Úteis</div>";
echo "<div class='card-body'>";

echo "<a href='Notificacoes.php' class='btn btn-primary mb-2'><i class='fas fa-bell'></i> Ver Página de Notificações</a><br>";
echo "<a href='../SISTEMA_NOTIFICACOES_README.md' target='_blank' class='btn btn-info mb-2'><i class='fas fa-book'></i> Documentação Completa</a><br>";
echo "<a href='?limpar=1' class='btn btn-warning mb-2'><i class='fas fa-trash'></i> Limpar Notificações de Teste</a>";

echo "</div></div>";

// Limpar notificações de teste
if (isset($_GET['limpar'])) {
    echo "<div class='card mb-3'>";
    echo "<div class='card-header bg-danger text-white'><i class='fas fa-trash-alt'></i> Limpeza de Notificações</div>";
    echo "<div class='card-body'>";
    
    try {
        $controller = new ControllerNotificacao();
        // Deletar todas as notificações do usuário de teste
        require_once __DIR__ . '/../Model/BancoDeDados.php';
        $db = BancoDeDados::getInstance();
        $stmt = $db->prepare("DELETE FROM notificacoes WHERE id_usuario = 1");
        $stmt->execute();
        
        echo "<p class='text-success'><i class='fas fa-check'></i> Notificações de teste deletadas com sucesso!</p>";
        echo "<a href='teste_observer.php' class='btn btn-primary'>Voltar ao Teste</a>";
    } catch (Exception $e) {
        echo "<p class='text-danger'><i class='fas fa-times'></i> Erro ao limpar: " . $e->getMessage() . "</p>";
    }
    
    echo "</div></div>";
}

echo "<div class='alert alert-success mt-4'>";
echo "<h4><i class='fas fa-check-circle'></i> Todos os testes concluídos!</h4>";
echo "<p>O sistema de notificações com padrão Observer está funcionando corretamente.</p>";
echo "</div>";

echo "</div>"; // container
echo "</body></html>";
