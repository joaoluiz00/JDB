<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../Service/BattleService.php';

$response = ['success'=>false,'message'=>''];
if (!isset($_SESSION['id'])) { $response['message']='Usuário não autenticado.'; echo json_encode($response); exit; }

$service = new BattleService();
$action = $_GET['action'] ?? $_POST['action'] ?? null;

// Recupera estado da sessão (se houver)
if (!isset($_SESSION['battle_state'])) { $_SESSION['battle_state'] = null; }
$state = null;
if ($_SESSION['battle_state']) {
    $raw = $_SESSION['battle_state'];
    $state = new BattleState();
    $state->deck = $raw['deck'];
    $state->activeIndex = $raw['activeIndex'];
    $state->enemy = $raw['enemy'];
    $state->turn = $raw['turn'];
    $state->finished = $raw['finished'];
    $state->winner = $raw['winner'];
}

try {
    switch ($action) {
        case 'setupDeck':
            // Recebe lista de cartas selecionadas pelo usuário
            $ids = isset($_POST['cards']) ? (array)$_POST['cards'] : [];
            $ids = array_map('intval', $ids);
            $state = $service->buildDeckForUser($_SESSION['id'], $ids);
            $response['success'] = true;
            $response['message'] = 'Deck configurado.';
            break;
        case 'selectEnemy':
            if (!$state) { throw new RuntimeException('Configure o deck primeiro.'); }
            $service->assignRandomEnemy($state);
            $response['success'] = true;
            $response['message'] = 'Inimigo definido.';
            break;
        case 'attack':
            if (!$state) { throw new RuntimeException('Sem batalha ativa.'); }
            $slot = $_POST['slot'] ?? '1';
            $msg = $service->attack($state, $slot);
            if ($state->turn === 'enemy' && !$state->finished) {
                // Executa turno inimigo automaticamente
                $enemyMsg = $service->enemyTurn($state);
                $msg .= ' ' . $enemyMsg;
            }
            $response['success'] = true;
            $response['message'] = $msg;
            break;
        case 'switch':
            if (!$state) { throw new RuntimeException('Sem batalha ativa.'); }
            $index = (int)($_POST['index'] ?? 0);
            $msg = $service->switchActive($state, $index);
            $response['success'] = true;
            $response['message'] = $msg;
            break;
        case 'state':
            if (!$state) { throw new RuntimeException('Nenhum estado.'); }
            $response['success'] = true;
            $response['message'] = 'OK';
            break;
        case 'reset':
            $_SESSION['battle_state'] = null; $state = null;
            $response['success'] = true; $response['message'] = 'Batalha resetada.'; break;
        default:
            throw new RuntimeException('Ação desconhecida.');
    }
} catch (Throwable $e) {
    $response['message'] = $e->getMessage();
}

// Persiste estado atualizado
if ($state) { $_SESSION['battle_state'] = $state->toArray(); }
if ($state) { $response['battleState'] = $state->toArray(); }

echo json_encode($response);
?>