<?php
require_once __DIR__ . '/../Model/BattleState.php';
require_once __DIR__ . '/../Model/BancoDeDados.php';

class BattleService {
    private $conn;
    public function __construct() {
        $this->conn = BancoDeDados::getInstance()->getConnection();
    }

    public function buildDeckForUser(int $userId, array $cardIds): BattleState {
        // Monta o deck do jogador a partir de ids selecionados;
        // valida a posse de cada carta e limita em 3 slots
        $state = new BattleState();
        $ids = array_slice($cardIds, 0, 3); // máx 3
        foreach ($ids as $id) {
            // valida se a carta pertence ao usuário
            $card = $this->fetchCardForUser($userId, (int)$id);
            if ($card) {
                $state->deck[] = ['card'=>$card,'hp'=>(int)$card['vida']];
            }
        }
        if (empty($state->deck)) {
            throw new RuntimeException('Deck vazio. Selecione ao menos uma carta.');
        }
        return $state;
    }

    public function assignRandomEnemy(BattleState $state, int $count = 3) {
        // Gera um "deck" inimigo aleatório com $count cartas e posiciona o índice ativo
            $card = $this->randomCard();
            $state->enemyDeck = [];
            for ($i=0; $i < $count; $i++) {
                $card = $this->randomCard();
                if ($card) {
                    $state->enemyDeck[] = ['card'=>$card, 'hp'=>(int)$card['vida']];
                }
            }
            $state->enemyActiveIndex = $this->firstAliveIndexEnemy($state);
    }

    public function assignEnemyById(BattleState $state, int $cardId): void {
        $card = $this->fetchCard($cardId);
        if (!$card) {
            throw new RuntimeException('Carta de inimigo inválida.');
        }
            if (empty($state->enemyDeck)) { $state->enemyDeck = []; }
            // Garante 3 cartas, preenchendo as demais aleatoriamente
            $state->enemyDeck = array_merge([
                ['card'=>$card,'hp'=>(int)$card['vida']]
            ], $this->generateRandomEnemySlots(2));
            $state->enemyActiveIndex = $this->firstAliveIndexEnemy($state);
    }
        public function assignEnemyByIds(BattleState $state, array $cardIds): void {
            $state->enemyDeck = [];
            $ids = array_values($cardIds);
            for ($i=0; $i<3; $i++) {
                if (isset($ids[$i])) {
                    $c = $this->fetchCard((int)$ids[$i]);
                    if ($c) {
                        $state->enemyDeck[] = ['card'=>$c, 'hp'=>(int)$c['vida']];
                        continue;
                    }
                }
                // fallback aleatório
                $rc = $this->randomCard();
                if ($rc) $state->enemyDeck[] = ['card'=>$rc, 'hp'=>(int)$rc['vida']];
            }
            $state->enemyActiveIndex = $this->firstAliveIndexEnemy($state);
        }

    public function attack(BattleState $state, string $attackSlot) {
        // Turno do jogador: considera esquiva do inimigo e crítico do jogador;
        // troca de carta inimiga ao cair e finaliza quando todas caem
        if ($state->finished) return;
        if ($state->turn !== 'player') return;

        $active = $state->deck[$state->activeIndex];
        $card = $active['card'];
        $dano = 0; $nome = '';
        // Checa esquiva do inimigo
            $enemyActive = $state->enemyDeck[$state->enemyActiveIndex];
            $enemyCard = $enemyActive['card'];
        $evadeRoll = random_int(1, 100);
        if ($evadeRoll <= (int)$enemyCard['esquiva']) {
            $state->turn = 'enemy';
            return 'Você atacou, mas o inimigo esquivou! Turno do inimigo...';
        }
        if ($attackSlot === '1') {
            $dano = (int)$card['ataque1_dano'];
            $nome = $card['ataque1'];
        } else if ($attackSlot === '2') {
            $dano = (int)$card['ataque2_dano'];
            $nome = $card['ataque2'];
        }
        // Crítico
        $critLog = '';
        $critRoll = random_int(1, 100);
        if ($dano > 0 && $critRoll <= (int)$card['critico']) {
            $dano = (int)floor($dano * 1.5);
            $critLog = ' Acerto crítico!';
        }
            $state->enemyDeck[$state->enemyActiveIndex]['hp'] -= $dano;
        $log = "Você usou {$nome} causando {$dano} de dano." . $critLog;
            if ($state->enemyDeck[$state->enemyActiveIndex]['hp'] <= 0) {
                $state->enemyDeck[$state->enemyActiveIndex]['hp'] = 0;
                // Procura próxima carta inimiga viva
                $next = $this->firstAliveIndexEnemy($state);
                if ($next === -1) {
                    $state->finished = true;
                    $state->winner = 'player';
                    return $log . ' Você derrotou todas as cartas inimigas!';
                }
                $state->enemyActiveIndex = $next;
                $log .= ' Carta inimiga caiu! Nova carta inimiga entrou em campo.';
        }
        $state->turn = 'enemy';
        return $log . ' Turno do inimigo...';
    }

    public function enemyTurn(BattleState $state) {
        // Turno do inimigo: escolhe ataque válido aleatoriamente,
        // aplica esquiva e crítico, troca carta do jogador quando necessário
        if ($state->finished) return null;
        if ($state->turn !== 'enemy') return null;
        // Garante que há uma carta inimiga ativa
        if (empty($state->enemyDeck)) { return null; }
        if (!isset($state->enemyDeck[$state->enemyActiveIndex]) || $state->enemyDeck[$state->enemyActiveIndex]['hp'] <= 0) {
            $state->enemyActiveIndex = $this->firstAliveIndexEnemy($state);
            if ($state->enemyActiveIndex === -1) { return null; }
        }
        $enemyActive = $state->enemyDeck[$state->enemyActiveIndex];
        $enemyCard = $enemyActive['card'];
        // Escolhe ataque válido (se ataque2_dano for 0, prioriza ataque1)
        $slot = 1;
        if ((int)$enemyCard['ataque2_dano'] > 0) {
            $slot = random_int(1,2);
        }
        // Checa esquiva do jogador
        $playerActive = $state->deck[$state->activeIndex];
        $evadeRoll = random_int(1, 100);
        if ($evadeRoll <= (int)$playerActive['card']['esquiva']) {
            $state->turn = 'player';
            return 'Inimigo atacou, mas você esquivou! Seu turno.';
        }
        $dano = (int)($slot === 1 ? $enemyCard['ataque1_dano'] : $enemyCard['ataque2_dano']);
        $nome = $slot === 1 ? $enemyCard['ataque1'] : $enemyCard['ataque2'];
        // Crítico inimigo
        $critLog = '';
        $critRoll = random_int(1, 100);
        if ($dano > 0 && $critRoll <= (int)$enemyCard['critico']) {
            $dano = (int)floor($dano * 1.5);
            $critLog = ' Crítico do inimigo!';
        }
        $state->deck[$state->activeIndex]['hp'] -= $dano;
        $log = "Inimigo usou {$nome} causando {$dano} de dano." . $critLog;
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
        // Troca a carta ativa do jogador para um índice válido e com HP > 0
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

        private function firstAliveIndexEnemy(BattleState $state): int {
            foreach ($state->enemyDeck as $i => $c) {
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

    private function fetchCardForUser(int $userId, int $cardId) {
        // Retorna a carta somente se pertencer ao usuário
        $sql = 'SELECT c.* FROM cartas c INNER JOIN cartas_usuario cu ON cu.id_carta = c.id WHERE cu.id_usuario = ? AND c.id = ? LIMIT 1';
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ii', $userId, $cardId);
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

        private function generateRandomEnemySlots(int $count): array {
            $out = [];
            for ($i=0; $i<$count; $i++) {
                $c = $this->randomCard();
                if ($c) $out[] = ['card'=>$c, 'hp'=>(int)$c['vida']];
            }
            return $out;
        }

    public function calculateReward(BattleState $state): int {
        // Recompensa dinâmica baseada no "potencial" médio das cartas inimigas:
        // vida + maior dano + (critico * 0.5) - (esquiva * 0.2)
            if (empty($state->enemyDeck)) return 5;
            // Considera a soma do potencial das 3 cartas inimigas
            $scoreTotal = 0;
            foreach ($state->enemyDeck as $slot) {
                $c = $slot['card'];
                $vida = (int)($c['vida'] ?? 0);
                $atk1 = (int)($c['ataque1_dano'] ?? 0);
                $atk2 = (int)($c['ataque2_dano'] ?? 0);
                $crit = (int)($c['critico'] ?? 0);
                $esq  = (int)($c['esquiva'] ?? 0);
                $scoreTotal += $vida + max($atk1,$atk2) + (int)floor($crit * 0.5) - (int)floor($esq * 0.2);
            }
            $score = $scoreTotal / max(1, count($state->enemyDeck));
        // Mais generoso: base 10 e divisor menor para deixar a experiência mais recompensadora
        $reward = max(10, (int)round($score / 6));
        return $reward;
    }
}
