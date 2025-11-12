<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

require_once __DIR__ . '/../Controller/ControllerNotificacao.php';

$controller = new ControllerNotificacao();
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$idUsuario = $_SESSION['id'];

try {
    switch ($action) {
        case 'listar':
            $limite = $_GET['limite'] ?? 50;
            $apenasNaoLidas = isset($_GET['nao_lidas']) && $_GET['nao_lidas'] == '1';
            
            $notificacoes = $controller->buscarNotificacoesUsuario($idUsuario, $limite, $apenasNaoLidas);
            
            $notificacoesArray = array_map(function($notif) {
                return [
                    'id' => $notif->getId(),
                    'tipo' => $notif->getTipo(),
                    'titulo' => $notif->getTitulo(),
                    'mensagem' => $notif->getMensagem(),
                    'lida' => $notif->isLida(),
                    'icone' => $notif->getIcone(),
                    'cor_fundo' => $notif->getCorFundo(),
                    'link' => $notif->getLink(),
                    'tempo_decorrido' => $notif->getTempoDecorrido(),
                    'classe_css' => $notif->getClasseCSS(),
                    'data_hora' => $notif->getDataHora()
                ];
            }, $notificacoes);
            
            echo json_encode([
                'success' => true,
                'notificacoes' => $notificacoesArray,
                'total' => count($notificacoesArray)
            ]);
            break;

        case 'contar_nao_lidas':
            $total = $controller->contarNaoLidas($idUsuario);
            
            echo json_encode([
                'success' => true,
                'total' => $total
            ]);
            break;

        case 'marcar_lida':
            $idNotificacao = $_POST['id_notificacao'] ?? null;
            
            if (!$idNotificacao) {
                echo json_encode(['success' => false, 'message' => 'ID da notificação não fornecido']);
                exit;
            }
            
            $success = $controller->marcarComoLida($idNotificacao);
            
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Notificação marcada como lida' : 'Erro ao marcar notificação'
            ]);
            break;

        case 'marcar_todas_lidas':
            $success = $controller->marcarTodasComoLidas($idUsuario);
            
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Todas as notificações foram marcadas como lidas' : 'Erro ao marcar notificações'
            ]);
            break;

        case 'deletar':
            $idNotificacao = $_POST['id_notificacao'] ?? null;
            
            if (!$idNotificacao) {
                echo json_encode(['success' => false, 'message' => 'ID da notificação não fornecido']);
                exit;
            }
            
            $success = $controller->deletarNotificacao($idNotificacao);
            
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Notificação deletada' : 'Erro ao deletar notificação'
            ]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Ação inválida']);
            break;
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro no servidor: ' . $e->getMessage()
    ]);
}
