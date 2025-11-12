<?php
session_start();

// Simula usuário logado
if (!isset($_SESSION['id'])) {
    $_SESSION['id'] = 1;
    $_SESSION['nome'] = 'Teste Usuário';
    $_SESSION['email'] = 'teste@example.com';
}

require_once __DIR__ . '/../Controller/ControllerNotificacao.php';

$controller = new ControllerNotificacao();
$idUsuario = $_SESSION['id'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de Notificações</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../Assets/notificacoes.css">
</head>
<body>
    <div class="container mt-5">
        <h1>🔍 Diagnóstico de Notificações</h1>
        
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5>Informações da Sessão</h5>
            </div>
            <div class="card-body">
                <p><strong>ID do Usuário:</strong> <?php echo $_SESSION['id']; ?></p>
                <p><strong>Nome:</strong> <?php echo $_SESSION['nome']; ?></p>
                <p><strong>Email:</strong> <?php echo $_SESSION['email']; ?></p>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                <h5>Status da Tabela</h5>
            </div>
            <div class="card-body">
                <?php
                try {
                    $db = require_once __DIR__ . '/../Model/BancoDeDados.php';
                    $conn = BancoDeDados::getInstance()->getConnection();
                    
                    // Verifica se a tabela existe
                    $result = $conn->query("SHOW TABLES LIKE 'notificacoes'");
                    if ($result->num_rows > 0) {
                        echo '<p class="text-success"><i class="fas fa-check"></i> Tabela "notificacoes" existe no banco de dados</p>';
                        
                        // Conta total de notificações
                        $result = $conn->query("SELECT COUNT(*) as total FROM notificacoes WHERE id_usuario = " . $idUsuario);
                        $row = $result->fetch_assoc();
                        echo '<p><strong>Total de notificações:</strong> ' . $row['total'] . '</p>';
                        
                        // Conta não lidas
                        $result = $conn->query("SELECT COUNT(*) as total FROM notificacoes WHERE id_usuario = " . $idUsuario . " AND lida = 0");
                        $row = $result->fetch_assoc();
                        echo '<p><strong>Notificações não lidas:</strong> ' . $row['total'] . '</p>';
                    } else {
                        echo '<p class="text-danger"><i class="fas fa-times"></i> Tabela "notificacoes" NÃO existe no banco de dados</p>';
                        echo '<div class="alert alert-warning">Você precisa executar o arquivo <code>Banco/banco.sql</code> ou <code>Banco/notificacoes.sql</code></div>';
                    }
                } catch (Exception $e) {
                    echo '<p class="text-danger"><i class="fas fa-times"></i> Erro: ' . $e->getMessage() . '</p>';
                }
                ?>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-success text-white">
                <h5>Buscar Notificações (via Controller)</h5>
            </div>
            <div class="card-body">
                <?php
                try {
                    $totalNaoLidas = $controller->contarNaoLidas($idUsuario);
                    $notificacoes = $controller->buscarNotificacoesUsuario($idUsuario, 10);
                    
                    echo '<p><strong>Total não lidas (via Controller):</strong> ' . $totalNaoLidas . '</p>';
                    echo '<p><strong>Total de notificações (via Controller):</strong> ' . count($notificacoes) . '</p>';
                    
                    if (!empty($notificacoes)) {
                        echo '<hr><h6>Últimas notificações:</h6>';
                        echo '<ul class="list-group">';
                        foreach (array_slice($notificacoes, 0, 5) as $notif) {
                            $badge = $notif->isLida() ? '<span class="badge badge-secondary">Lida</span>' : '<span class="badge badge-primary">Não lida</span>';
                            echo '<li class="list-group-item">';
                            echo '<strong>' . htmlspecialchars($notif->getTitulo()) . '</strong> ' . $badge;
                            echo '<br><small>' . htmlspecialchars($notif->getMensagem()) . '</small>';
                            echo '<br><small class="text-muted">' . $notif->getTempoDecorrido() . '</small>';
                            echo '</li>';
                        }
                        echo '</ul>';
                    }
                } catch (Exception $e) {
                    echo '<p class="text-danger"><i class="fas fa-times"></i> Erro ao buscar: ' . $e->getMessage() . '</p>';
                }
                ?>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-warning text-white">
                <h5>Teste do Widget</h5>
            </div>
            <div class="card-body">
                <p>Widget de notificações abaixo (deve aparecer o sino):</p>
                <div class="bg-light p-3 text-center">
                    <?php include __DIR__ . '/components/NotificacoesWidget.php'; ?>
                </div>
                <hr>
                <p class="mt-3"><strong>Instruções:</strong></p>
                <ol>
                    <li>O sino deve aparecer acima</li>
                    <li>Se houver notificações não lidas, deve ter um badge vermelho com número</li>
                    <li>Clique no sino para abrir o menu dropdown</li>
                    <li>As notificações devem aparecer no dropdown</li>
                </ol>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-secondary text-white">
                <h5>Ações Rápidas</h5>
            </div>
            <div class="card-body">
                <a href="criar_notificacao_teste.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Criar Notificação de Teste
                </a>
                <a href="teste_observer.php" class="btn btn-success">
                    <i class="fas fa-vial"></i> Criar Várias Notificações de Teste
                </a>
                <a href="Notificacoes.php" class="btn btn-info">
                    <i class="fas fa-list"></i> Ver Página de Notificações
                </a>
                <a href="Loja.php" class="btn btn-warning">
                    <i class="fas fa-store"></i> Ir para Loja
                </a>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-dark text-white">
                <h5>Verificação de Arquivos</h5>
            </div>
            <div class="card-body">
                <?php
                $arquivos = [
                    'CSS' => '../Assets/notificacoes.css',
                    'JS' => '../Assets/js/notificacoes.js',
                    'Widget' => 'components/NotificacoesWidget.php',
                    'Controller' => '../Controller/ControllerNotificacao.php',
                    'Service' => '../Service/NotificationService.php',
                    'Model' => '../Model/Notificacao.php'
                ];

                echo '<ul class="list-group">';
                foreach ($arquivos as $nome => $caminho) {
                    $existe = file_exists(__DIR__ . '/' . $caminho);
                    $icone = $existe ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>';
                    $status = $existe ? 'Existe' : 'NÃO ENCONTRADO';
                    echo '<li class="list-group-item">' . $icone . ' <strong>' . $nome . ':</strong> ' . $status . '</li>';
                }
                echo '</ul>';
                ?>
            </div>
        </div>

        <div class="alert alert-info mt-4">
            <h5><i class="fas fa-lightbulb"></i> Dica:</h5>
            <p>Se tudo estiver OK mas o dropdown não abrir, pressione <kbd>F12</kbd> no navegador e veja se há erros JavaScript no console.</p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../Assets/js/notificacoes.js"></script>
</body>
</html>
