<?php
/**
 * BattleState representa o estado atual de uma batalha em andamento.
 * Armazenado em sessão para simplificar (poderia ir para tabela futuramente).
 */
class BattleState {
    public array $deck = [];          // Cada item: ['card'=>arrayDadosCarta,'hp'=>int]
    public int $activeIndex = 0;      // Índice da carta ativa do jogador
    public array $enemy = [];         // ['card'=>arrayDadosCarta,'hp'=>int]
    public string $turn = 'player';   // 'player' | 'enemy'
    public bool $finished = false;    // Flag de término
    public ?string $winner = null;    // 'player' | 'enemy' | null

    public function toArray(): array {
        return [
            'deck' => array_map(function($c){return ['card'=>$c['card'],'hp'=>$c['hp']];}, $this->deck),
            'activeIndex' => $this->activeIndex,
            'enemy' => $this->enemy,
            'turn' => $this->turn,
            'finished' => $this->finished,
            'winner' => $this->winner,
        ];
    }
}
