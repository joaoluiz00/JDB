<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../Model/BancoDeDados.php';

// Resposta padrão
$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['id'])) {
    $response['message'] = 'Usuário não autenticado.';
    echo json_encode($response);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? null;
$conn = BancoDeDados::getInstance()->getConnection();
$idUsuario = $_SESSION['id'];

switch ($action) {
    case 'iniciarBatalha':
        try {
            // Lógica para selecionar a carta do jogador (a primeira equipada)
            $sqlJogador = "SELECT c.* FROM cartas_usuario cu JOIN cartas c ON cu.id_carta = c.id WHERE cu.id_usuario = ? AND cu.equipada = 1 LIMIT 1";
            $stmt = $conn->prepare($sqlJogador);
            $stmt->bind_param('i', $idUsuario);
            $stmt->execute();
            $resultJogador = $stmt->get_result();
            $cartaJogador = $resultJogador->fetch_assoc();
            $stmt->close();
            
            if (!$cartaJogador) {
                $response['message'] = 'Nenhuma carta equipada. Por favor, equipe uma no seu perfil.';
                echo json_encode($response);
                exit;
            }

            // Lógica para selecionar a carta do oponente (aleatória)
            $sqlOponente = "SELECT * FROM cartas ORDER BY RAND() LIMIT 1";
            $resultOponente = $conn->query($sqlOponente);
            $cartaOponente = $resultOponente->fetch_assoc();

            // Salva o estado inicial da batalha no banco de dados
            $sqlInsertBattle = "INSERT INTO batalhas_ativas (id_jogador1, id_jogador2, id_carta_jogador1, vida_atual_jogador1, id_carta_jogador2, vida_atual_jogador2, turno_de) VALUES (?, ?, ?, ?, ?, ?, 'jogador1')";
            $stmtInsert = $conn->prepare($sqlInsertBattle);
            // Simulação de um jogador2 (oponente, ID 0)
            $oponenteId = 0;
            $stmtInsert->bind_param('iiiiii', $idUsuario, $oponenteId, $cartaJogador['id'], $cartaJogador['vida'], $cartaOponente['id'], $cartaOponente['vida']);
            $stmtInsert->execute();
            $batalhaId = $stmtInsert->insert_id;
            $stmtInsert->close();

            $response['success'] = true;
            $response['battleState'] = [
                'id_batalha' => $batalhaId,
                'player' => ['carta' => $cartaJogador, 'vidaAtual' => $cartaJogador['vida']],
                'opponent' => ['carta' => $cartaOponente, 'vidaAtual' => $cartaOponente['vida']],
                'turno' => 'player',
            ];
            $response['message'] = 'Batalha iniciada!';

        } catch (Exception $e) {
            $response['message'] = 'Erro ao iniciar a batalha: ' . $e->getMessage();
        }
        echo json_encode($response);
        break;

    case 'processarTurno':
        $batalhaId = $_POST['batalhaId'] ?? null;
        $attackId = $_POST['attackId'] ?? null;
        
        if (!$batalhaId || !$attackId) {
            $response['message'] = 'Dados inválidos.';
            echo json_encode($response);
            exit;
        }

        // Busca o estado atual da batalha no banco de dados
        $sqlBattle = "SELECT * FROM batalhas_ativas WHERE id = ? AND id_jogador1 = ?";
        $stmtBattle = $conn->prepare($sqlBattle);
        $stmtBattle->bind_param('ii', $batalhaId, $idUsuario);
        $stmtBattle->execute();
        $battleData = $stmtBattle->get_result()->fetch_assoc();
        $stmtBattle->close();

        if (!$battleData) {
            $response['message'] = 'Batalha não encontrada ou inválida.';
            echo json_encode($response);
            exit;
        }
        
        // Lógica de turno do jogador
        $sqlJogador = "SELECT * FROM cartas WHERE id = ?";
        $stmtJ = $conn->prepare($sqlJogador);
        $stmtJ->bind_param('i', $battleData['id_carta_jogador1']);
        $stmtJ->execute();
        $cartaJogador = $stmtJ->get_result()->fetch_assoc();
        $stmtJ->close();

        $sqlOponente = "SELECT * FROM cartas WHERE id = ?";
        $stmtO = $conn->prepare($sqlOponente);
        $stmtO->bind_param('i', $battleData['id_carta_jogador2']);
        $stmtO->execute();
        $cartaOponente = $stmtO->get_result()->fetch_assoc();
        $stmtO->close();
        
        $dano = 0;
        $ataqueNome = '';
        if ($attackId == 'attack1') {
            $dano = $cartaJogador['ataque1_dano'];
            $ataqueNome = $cartaJogador['ataque1'];
        } elseif ($attackId == 'attack2') {
            $dano = $cartaJogador['ataque2_dano'];
            $ataqueNome = $cartaJogador['ataque2'];
        }

        $vidaOponente = $battleData['vida_atual_jogador2'] - $dano;

        $message = "Você usou " . $ataqueNome . "! Causa " . $dano . " de dano.";
        $winner = '';

        if ($vidaOponente <= 0) {
            $vidaOponente = 0;
            $winner = 'player';
            $message .= " O oponente foi derrotado!";
        } else {
            // Lógica de turno do oponente (simplificada)
            $danoOponente = $cartaOponente['ataque1_dano'];
            $vidaJogador = $battleData['vida_atual_jogador1'] - $danoOponente;

            if ($vidaJogador <= 0) {
                $vidaJogador = 0;
                $winner = 'opponent';
                $message .= " Oponente usou " . $cartaOponente['ataque1'] . " causando " . $danoOponente . " de dano. Você foi derrotado!";
            } else {
                $message .= " Oponente usou " . $cartaOponente['ataque1'] . " causando " . $danoOponente . " de dano.";
                $vidaJogador = max(0, $vidaJogador);
            }
        }

        // Atualiza o estado da batalha no banco de dados
        $sqlUpdate = "UPDATE batalhas_ativas SET vida_atual_jogador1 = ?, vida_atual_jogador2 = ? WHERE id = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param('iii', $vidaJogador, $vidaOponente, $batalhaId);
        $stmtUpdate->execute();
        $stmtUpdate->close();

        $response['success'] = true;
        $response['battleState'] = [
            'player' => ['vidaAtual' => $vidaJogador],
            'opponent' => ['vidaAtual' => $vidaOponente],
            'winner' => $winner,
        ];
        $response['message'] = $message;
        
        echo json_encode($response);
        break;

    default:
        $response['message'] = 'Ação desconhecida.';
        echo json_encode($response);
        break;
}
?>