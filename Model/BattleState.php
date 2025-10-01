<?php
/**
 * BattleState representa o estado atual de uma batalha em andamento.
 * Armazenado em sessão para simplificar (poderia ir para tabela futuramente).
 */
class BattleState {
    public array $deck = [];          // Cada item: ['card'=>arrayDadosCarta,'hp'=>int]
    public int $activeIndex = 0;      // Índice da carta ativa do jogador
    // Inimigo agora também é um deck de até 3 cartas
    public array $enemyDeck = [];     // Cada item: ['card'=>arrayDadosCarta,'hp'=>int]
    public int $enemyActiveIndex = 0; // Índice da carta ativa do inimigo
    public string $turn = 'player';   // 'player' | 'enemy'
    public bool $finished = false;    // Flag de término
    public ?string $winner = null;    // 'player' | 'enemy' | null
    public bool $rewarded = false;    // recompensa já entregue
    public int $enemyProgress = 0;    // 0..2 - maior estágio desbloqueado (0 = só primeiro disponível)
    public ?int $currentEnemyStage = null; // Estágio atual selecionado (0..2)

    public function toArray(): array {
        return [
            'deck' => array_map(function($c){return ['card'=>$c['card'],'hp'=>$c['hp']];}, $this->deck),
            'activeIndex' => $this->activeIndex,
            'enemyDeck' => array_map(function($c){return ['card'=>$c['card'],'hp'=>$c['hp']];}, $this->enemyDeck),
            'enemyActiveIndex' => $this->enemyActiveIndex,
            'turn' => $this->turn,
            'finished' => $this->finished,
            'winner' => $this->winner,
            'rewarded' => $this->rewarded,
            'enemyProgress' => $this->enemyProgress,
            'currentEnemyStage' => $this->currentEnemyStage,
        ];
    }
}
