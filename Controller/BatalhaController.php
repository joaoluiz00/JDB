<?php
/**
 * Controlador de Batalha
 *
 * Endpoints principais usados pelo front (Assets/js/battle.js):
 * - listUserCards: Lista cartas do usuário para montagem do deck
 * - setupDeck: Monta o deck do usuário e carrega progresso salvo (progresso_batalha)
 * - selectEnemy: Seleção de inimigo por estágio (0..2) com gating por progresso
 * - attack / enemyTurn: Turnos separados do jogador e inimigo (dano, esquiva, crítico)
 * - newBattle: Reinicia a batalha mantendo o deck, gerando novo inimigo
 * - backToEnemySelect: Sai da batalha e retorna para a seleção de inimigos sem recarregar a página
 * - balance: Exibe saldo de moedas
 * - switch: Troca a carta ativa do jogador
 * - state / reset: Utilitários de sessão
 */
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../Service/BattleService.php';
require_once __DIR__ . '/../Model/BancoDeDados.php';
require_once __DIR__ . '/../Model/BattleState.php';

$response = ['success'=>false,'message'=>''];
if (!isset($_SESSION['id'])) { $response['message']='Usuário não autenticado.'; echo json_encode($response); exit; }

$service = new BattleService();
$action = $_GET['action'] ?? $_POST['action'] ?? null;

// Recupera estado da sessão (se houver)
// Obs.: o estado é serializado em array via BattleState::toArray() e reconstituído aqui
if (!isset($_SESSION['battle_state'])) { $_SESSION['battle_state'] = null; }
$state = null;
if (is_array($_SESSION['battle_state'])) {
    $raw = $_SESSION['battle_state'];
    $state = new BattleState();
    $state->deck = $raw['deck'] ?? [];
    $state->activeIndex = isset($raw['activeIndex']) ? (int)$raw['activeIndex'] : 0;
    $state->enemyDeck = $raw['enemyDeck'] ?? [];
    $state->enemyActiveIndex = isset($raw['enemyActiveIndex']) ? (int)$raw['enemyActiveIndex'] : 0;
    $state->turn = $raw['turn'] ?? 'player';
    $state->finished = (bool)($raw['finished'] ?? false);
    $state->winner = $raw['winner'] ?? null;
    $state->rewarded = (bool)($raw['rewarded'] ?? false);
    $state->enemyProgress = (int)($raw['enemyProgress'] ?? 0);
    $state->currentEnemyStage = isset($raw['currentEnemyStage']) ? (int)$raw['currentEnemyStage'] : null;
}

try {
    switch ($action) {
        case 'listUserCards':
            // Lista cartas do usuário para montar o deck
            $conn = BancoDeDados::getInstance()->getConnection();
            $stmt = $conn->prepare('SELECT cu.id_carta, c.nome, c.path, c.vida FROM cartas_usuario cu INNER JOIN cartas c ON c.id = cu.id_carta WHERE cu.id_usuario = ?');
            $stmt->bind_param('i', $_SESSION['id']);
            $stmt->execute();
            $res = $stmt->get_result();
            $items = [];
            while ($row = $res->fetch_assoc()) { $items[] = $row; }
            $stmt->close();
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['items'] = $items;
            break;
        case 'randomCard':
            // Retorna uma carta aleatória (para gerar a lista de inimigos)
            $conn = BancoDeDados::getInstance()->getConnection();
            $res = $conn->query('SELECT * FROM cartas ORDER BY RAND() LIMIT 1');
            $card = $res ? $res->fetch_assoc() : null;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['card'] = $card;
            break;
        case 'setupDeck':
            // Recebe lista de cartas selecionadas pelo usuário
            // Valida propriedade das cartas e limita o deck a 3 no serviço
            $ids = isset($_POST['cards']) ? (array)$_POST['cards'] : [];
            $ids = array_map('intval', $ids);
            $state = $service->buildDeckForUser($_SESSION['id'], $ids);
            // Carrega progresso salvo do usuário (se existir)
            // Mantém a progressão entre sessões/visitas (desbloqueia estágios)
            try {
                $conn = BancoDeDados::getInstance()->getConnection();
                if ($conn) {
                    $stmt = $conn->prepare('SELECT enemy_progress FROM progresso_batalha WHERE id_usuario = ? LIMIT 1');
                    if ($stmt) {
                        $stmt->bind_param('i', $_SESSION['id']);
                        $stmt->execute();
                        $res = $stmt->get_result();
                        if ($row = $res->fetch_assoc()) {
                            $state->enemyProgress = (int)$row['enemy_progress'];
                        }
                        $stmt->close();
                    }
                }
            } catch (Throwable $e) { /* silencioso: se der erro, continua com progresso 0 */ }
            $response['success'] = true;
            $response['message'] = 'Deck configurado.';
            break;
        case 'selectEnemy':
            if (!$state) { throw new RuntimeException('Configure o deck primeiro.'); }
            // Modo por estágio (0..2), por lista de IDs, por ID único, ou aleatório
            if (isset($_POST['stage'])) {
                $stage = max(0, min(2, (int)$_POST['stage']));
                if ($stage > $state->enemyProgress) { throw new RuntimeException('Este inimigo ainda está bloqueado.'); }
                $state->currentEnemyStage = $stage;
                $service->assignRandomEnemy($state, 3);
            } elseif (!empty($_POST['enemyIds']) && is_array($_POST['enemyIds'])) {
                $ids = array_map('intval', $_POST['enemyIds']);
                $service->assignEnemyByIds($state, $ids);
            } elseif (isset($_POST['enemyId'])) {
                $service->assignEnemyById($state, (int)$_POST['enemyId']);
            } else {
                $service->assignRandomEnemy($state, 3);
            }
            $response['success'] = true;
            $response['message'] = 'Inimigo definido.';
            break;
        case 'attack':
            if (!$state) { throw new RuntimeException('Sem batalha ativa.'); }
            $slot = $_POST['slot'] ?? '1';
            $msg = $service->attack($state, $slot);
            // Se terminou e jogador venceu, dar recompensa
            if ($state->finished && $state->winner === 'player' && !$state->rewarded) {
                $coins = max(5, $service->calculateReward($state));
                $db = BancoDeDados::getInstance();
                $db->addCoins($_SESSION['id'], $coins);
                $state->rewarded = true;
                $msg .= ' Você ganhou ' . $coins . ' moedas!';
                $response['reward'] = $coins;
                    // Se terminou e jogador venceu, dar recompensa e registrar histórico
                if ($state->currentEnemyStage !== null && $state->currentEnemyStage >= $state->enemyProgress) {
                    $state->enemyProgress = min($state->currentEnemyStage + 1, 2);
                    // Persiste progresso em banco (UPSERT)
                    try {
                        $conn = $db->getConnection();
                        $stmt = $conn->prepare('INSERT INTO progresso_batalha (id_usuario, enemy_progress, updated_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE enemy_progress = GREATEST(enemy_progress, VALUES(enemy_progress)), updated_at = NOW()');
                        if ($stmt) {
                            $stmt->bind_param('ii', $_SESSION['id'], $state->enemyProgress);
                            $stmt->execute();
                            $stmt->close();
                        }
                    } catch (Throwable $e) { /* manter silencioso */ }
                }
                // registra histórico simples
                $conn = $db->getConnection();
                // registra as 3 cartas inimigas usadas
                if (!empty($state->enemyDeck)) {
                    foreach ($state->enemyDeck as $slot) {
                        if (empty($slot['card']['id'])) continue;
                        $stmt = $conn->prepare('INSERT INTO historico_batalhas (id_usuario, id_carta_inimiga, resultado, recompensa, data_batalha) VALUES (?,?,?,?, NOW())');
                        if ($stmt) {
                            $enemyId = (int)$slot['card']['id'];
                            $resultado = 'vitoria';
                            $stmt->bind_param('iisi', $_SESSION['id'], $enemyId, $resultado, $coins);
                            $stmt->execute();
                            $stmt->close();
                        }
                    }
                }
            }
            $response['success'] = true;
            $response['message'] = $msg;
            break;
        case 'enemyTurn':
            if (!$state) { throw new RuntimeException('Sem batalha ativa.'); }
            $msg = $service->enemyTurn($state);
            // Se terminou aqui e por acaso jogador venceu (não deve ocorrer no turno inimigo, mas fica a salvaguarda)
            if ($state->finished && $state->winner === 'player' && !$state->rewarded) {
                $coins = max(5, $service->calculateReward($state));
                $db = BancoDeDados::getInstance();
                $db->addCoins($_SESSION['id'], $coins);
                $state->rewarded = true;
                $msg .= ' Você ganhou ' . $coins . ' moedas!';
                    // Salvaguarda: se a batalha terminar com vitória do jogador no turno do inimigo (improvável),
                    // aplica recompensa, persiste progresso e registra histórico
                if ($state->currentEnemyStage !== null && $state->currentEnemyStage >= $state->enemyProgress) {
                    $state->enemyProgress = min($state->currentEnemyStage + 1, 2);
                    try {
                        $conn = $db->getConnection();
                        $stmt = $conn->prepare('INSERT INTO progresso_batalha (id_usuario, enemy_progress, updated_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE enemy_progress = GREATEST(enemy_progress, VALUES(enemy_progress)), updated_at = NOW()');
                        if ($stmt) {
                            $stmt->bind_param('ii', $_SESSION['id'], $state->enemyProgress);
                            $stmt->execute();
                            $stmt->close();
                        }
                    } catch (Throwable $e) { }
                }
                // registra histórico
                $conn = $db->getConnection();
                if (!empty($state->enemyDeck)) {
                    foreach ($state->enemyDeck as $slot) {
                        if (empty($slot['card']['id'])) continue;
                        $stmt = $conn->prepare('INSERT INTO historico_batalhas (id_usuario, id_carta_inimiga, resultado, recompensa, data_batalha) VALUES (?,?,?,?, NOW())');
                        if ($stmt) {
                            $enemyId = (int)$slot['card']['id'];
                            $resultado = 'vitoria';
                            $stmt->bind_param('iisi', $_SESSION['id'], $enemyId, $resultado, $coins);
                            $stmt->execute();
                            $stmt->close();
                        }
                    }
                }
            }
            $response['success'] = true;
            $response['message'] = $msg ?? '';
            break;
        case 'backToEnemySelect':
            if (!$state) { throw new RuntimeException('Sem batalha ativa.'); }
            // Limpa o inimigo atual e restaura HP do jogador, voltando para tela de seleção
            if (!empty($state->deck)) {
                foreach ($state->deck as $i => $c) {
                    if (!empty($c['card']['vida'])) {
                        $state->deck[$i]['hp'] = (int)$c['card']['vida'];
                    }
                }
            }
                    // Restaura HP do jogador e reseta estado da batalha mantendo deck e progresso
            $state->enemyDeck = [];
            $state->enemyActiveIndex = 0;
            $state->currentEnemyStage = null;
            $state->finished = false;
            $state->winner = null;
            $state->rewarded = false;
            $state->turn = 'player';
            $response['success'] = true;
            $response['message'] = 'Retornando à seleção de inimigos.';
            break;
        case 'newBattle':
            if (!$state) { throw new RuntimeException('Sem batalha ativa.'); }
            // Restaura HP do jogador e reseta estado da batalha
            if (!empty($state->deck)) {
                foreach ($state->deck as $i => $c) {
                    if (!empty($c['card']['vida'])) {
                        $state->deck[$i]['hp'] = (int)$c['card']['vida'];
                    }
                }
            }
            $state->activeIndex = 0;
            $state->turn = 'player';
            $state->finished = false;
            $state->winner = null;
            $state->rewarded = false;
            // Gera novo inimigo aleatório
            $service->assignRandomEnemy($state, 3);
            $response['success'] = true;
            $response['message'] = 'Nova batalha iniciada!';
            break;
        case 'balance':
            $db = BancoDeDados::getInstance();
            $user = $db->getUserById($_SESSION['id']);
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['coin'] = $user ? (int)$user['coin'] : 0;
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
// Persiste estado atualizado na sessão
if ($state) { $_SESSION['battle_state'] = $state->toArray(); }
if ($state) { $response['battleState'] = $state->toArray(); }

echo json_encode($response);
?>