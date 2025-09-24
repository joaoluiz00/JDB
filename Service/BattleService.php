<?php
require_once __DIR__ . '/../Model/BattleState.php';
require_once __DIR__ . '/../Model/BancoDeDados.php';

class BattleService {
    private $conn;
    public function __construct() {
        $this->conn = BancoDeDados::getInstance()->getConnection();
    }

    public function buildDeckForUser(int $userId, array $cardIds): BattleState {
        $state = new BattleState();
        $ids = array_slice($cardIds, 0, 3); // máx 3
        foreach ($ids as $id) {
            $card = $this->fetchCard($id);
            if ($card) {
                $state->deck[] = ['card'=>$card,'hp'=>(int)$card['vida']];
            }
        }
        if (empty($state->deck)) {
            throw new RuntimeException('Deck vazio. Selecione ao menos uma carta.');
        }
        return $state;
    }

    public function assignRandomEnemy(BattleState $state, int $count = 1) {
        // Para suporte futuro a múltiplos inimigos, usamos count; agora 1 ativo.
        $card = $this->randomCard();
        $state->enemy = ['card'=>$card,'hp'=>(int)$card['vida']];
    }

    public function attack(BattleState $state, string $attackSlot) {
        if ($state->finished) return;
        if ($state->turn !== 'player') return;

        $active = $state->deck[$state->activeIndex];
        $card = $active['card'];
        $dano = 0; $nome = '';
        if ($attackSlot === '1') {
            $dano = (int)$card['ataque1_dano'];
            $nome = $card['ataque1'];
        } else if ($attackSlot === '2') {
            $dano = (int)$card['ataque2_dano'];
            $nome = $card['ataque2'];
        }
        $state->enemy['hp'] -= $dano;
        $log = "Você usou {$nome} causando {$dano} de dano.";
        if ($state->enemy['hp'] <= 0) {
            $state->enemy['hp'] = 0;
            $state->finished = true;
            $state->winner = 'player';
            return $log . ' Inimigo derrotado!';
        }
        $state->turn = 'enemy';
        return $log . ' Turno do inimigo...';
    }

    public function enemyTurn(BattleState $state) {
        if ($state->finished) return null;
        if ($state->turn !== 'enemy') return null;
        $enemyCard = $state->enemy['card'];
        // Escolhe ataque válido (se ataque2_dano for 0, prioriza ataque1)
        $slot = 1;
        if ((int)$enemyCard['ataque2_dano'] > 0) {
            $slot = random_int(1,2);
        }
        $dano = (int)($slot === 1 ? $enemyCard['ataque1_dano'] : $enemyCard['ataque2_dano']);
        $nome = $slot === 1 ? $enemyCard['ataque1'] : $enemyCard['ataque2'];
        $state->deck[$state->activeIndex]['hp'] -= $dano;
        $log = "Inimigo usou {$nome} causando {$dano} de dano.";
        if ($state->deck[$state->activeIndex]['hp'] <= 0) {
            $state->deck[$state->activeIndex]['hp'] = 0;
            // Tenta trocar para outra carta viva
            $alive = $this->firstAliveIndex($state);
            if ($alive === -1) {
                $state->finished = true;
                $state->winner = 'enemy';
                $state->turn = 'player';
                return $log . ' Todas as suas cartas foram derrotadas!';
            }
            $state->activeIndex = $alive;
            $log .= ' Sua carta caiu! Nova carta entrou em campo.';
        }
        $state->turn = 'player';
        return $log . ' Seu turno.';
    }

    public function switchActive(BattleState $state, int $index) {
        if ($state->finished) return 'Batalha terminou.';
        if ($index < 0 || $index >= count($state->deck)) return 'Índice inválido.';
        if ($state->deck[$index]['hp'] <= 0) return 'Carta derrotada não pode entrar.';
        $state->activeIndex = $index;
        return 'Carta ativa trocada.';
    }

    private function firstAliveIndex(BattleState $state): int {
        foreach ($state->deck as $i => $c) {
            if ($c['hp'] > 0) return $i;
        }
        return -1;
    }

    private function fetchCard(int $id) {
        $stmt = $this->conn->prepare('SELECT * FROM cartas WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $res;
    }

    private function randomCard() {
        $sql = 'SELECT * FROM cartas ORDER BY RAND() LIMIT 1';
        $res = $this->conn->query($sql);
        return $res->fetch_assoc();
    }
}
