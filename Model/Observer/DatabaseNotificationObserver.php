<?php
require_once __DIR__ . '/ObserverInterface.php';
require_once __DIR__ . '/../Notificacao.php';
require_once __DIR__ . '/../../Controller/ControllerNotificacao.php';

/**
 * DatabaseNotificationObserver
 * Observador que salva notificações no banco de dados
 */
class DatabaseNotificationObserver implements ObserverInterface {
    private $controller;

    public function __construct() {
        $this->controller = new ControllerNotificacao();
    }

    /**
     * Recebe evento e cria notificação no banco de dados
     * @param string $evento
     * @param array $dados
     */
    public function update(string $evento, array $dados): void {
        $idUsuario = $dados['id_usuario'] ?? null;
        
        if (!$idUsuario) {
            return; // Não há usuário para notificar
        }

        // Configurações de notificação baseadas no tipo de evento
        $config = $this->getNotificationConfig($evento, $dados);
        
        if ($config) {
            $notificacao = new Notificacao(
                null,
                $idUsuario,
                $config['tipo'],
                $config['titulo'],
                $config['mensagem'],
                false,
                $config['icone'],
                $config['cor'],
                $config['link'] ?? null,
                null,
                $dados
            );
            
            $this->controller->criarNotificacao($notificacao);
        }
    }

    /**
     * Retorna configurações de notificação baseadas no evento
     * @param string $evento
     * @param array $dados
     * @return array|null
     */
    private function getNotificationConfig(string $evento, array $dados): ?array {
        $configs = [
            'compra_realizada' => [
                'tipo' => 'compra',
                'titulo' => '🛒 Compra Realizada!',
                'mensagem' => 'Sua compra de ' . ($dados['nome_item'] ?? 'item') . ' foi realizada com sucesso!',
                'icone' => 'shopping-cart',
                'cor' => '#28a745',
                'link' => 'HistoricoCompras.php'
            ],
            'compra_moedas' => [
                'tipo' => 'compra',
                'titulo' => '💰 Moedas Compradas!',
                'mensagem' => 'Você comprou ' . ($dados['quantidade'] ?? 0) . ' moedas!',
                'icone' => 'coins',
                'cor' => '#ffc107',
                'link' => 'LojaMoedas.php'
            ],
            'batalha_vencida' => [
                'tipo' => 'batalha',
                'titulo' => '⚔️ Vitória na Batalha!',
                'mensagem' => 'Parabéns! Você venceu a batalha e ganhou ' . ($dados['recompensa'] ?? 0) . ' moedas!',
                'icone' => 'trophy',
                'cor' => '#ff6b6b',
                'link' => 'Batalha.php'
            ],
            'batalha_perdida' => [
                'tipo' => 'batalha',
                'titulo' => '⚔️ Batalha Perdida',
                'mensagem' => 'Você foi derrotado! Continue treinando para a próxima batalha.',
                'icone' => 'shield-alt',
                'cor' => '#6c757d',
                'link' => 'Batalha.php'
            ],
            'conquista_desbloqueada' => [
                'tipo' => 'conquista',
                'titulo' => '🏆 Conquista Desbloqueada!',
                'mensagem' => 'Parabéns! Você desbloqueou: ' . ($dados['nome_conquista'] ?? 'Nova Conquista'),
                'icone' => 'award',
                'cor' => '#9c27b0',
                'link' => 'Perfil.php'
            ],
            'pacote_aberto' => [
                'tipo' => 'presente',
                'titulo' => '🎁 Pacote Aberto!',
                'mensagem' => 'Você abriu um pacote e recebeu novas cartas!',
                'icone' => 'gift',
                'cor' => '#ff9800',
                'link' => 'Inventario.php'
            ],
            'nivel_aumentado' => [
                'tipo' => 'sistema',
                'titulo' => '⬆️ Subiu de Nível!',
                'mensagem' => 'Parabéns! Você alcançou o nível ' . ($dados['nivel'] ?? 0) . '!',
                'icone' => 'level-up-alt',
                'cor' => '#00bcd4',
                'link' => 'Perfil.php'
            ],
            'presente_recebido' => [
                'tipo' => 'presente',
                'titulo' => '🎁 Presente Recebido!',
                'mensagem' => 'Você recebeu um presente! ' . ($dados['descricao'] ?? 'Confira seu inventário'),
                'icone' => 'gift',
                'cor' => '#e91e63',
                'link' => 'Inventario.php'
            ],
            'avaliacao_aprovada' => [
                'tipo' => 'sistema',
                'titulo' => '⭐ Avaliação Publicada!',
                'mensagem' => 'Sua avaliação de ' . ($dados['nome_item'] ?? 'produto') . ' foi publicada!',
                'icone' => 'star',
                'cor' => '#ff9800',
                'link' => 'VisualizarAvaliacoes.php?tipo=' . ($dados['tipo_item'] ?? '') . '&id=' . ($dados['id_item'] ?? '')
            ],
            'saldo_baixo' => [
                'tipo' => 'aviso',
                'titulo' => '⚠️ Saldo Baixo',
                'mensagem' => 'Seu saldo de moedas está baixo. Compre mais moedas para continuar jogando!',
                'icone' => 'exclamation-triangle',
                'cor' => '#ff5722',
                'link' => 'LojaMoedas.php'
            ],
            'bem_vindo' => [
                'tipo' => 'sistema',
                'titulo' => '👋 Bem-vindo!',
                'mensagem' => 'Bem-vindo ao JDB! Explore a loja e comece sua jornada!',
                'icone' => 'hand-sparkles',
                'cor' => '#007bff',
                'link' => 'Home.php'
            ]
        ];

        return $configs[$evento] ?? null;
    }
}
