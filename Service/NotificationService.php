<?php
require_once __DIR__ . '/../Model/Observer/EventManager.php';
require_once __DIR__ . '/../Model/Observer/DatabaseNotificationObserver.php';
require_once __DIR__ . '/../Model/Observer/EmailNotificationObserver.php';
require_once __DIR__ . '/../Model/Observer/LogObserver.php';

/**
 * NotificationService
 * Service Layer para gerenciar o sistema de notificações
 * Inicializa o EventManager e registra os observers
 */
class NotificationService {
    private static $instance = null;
    private $eventManager;
    private $initialized = false;

    private function __construct() {
        $this->eventManager = EventManager::getInstance();
    }

    /**
     * Retorna a instância única do NotificationService
     * @return NotificationService
     */
    public static function getInstance(): NotificationService {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Inicializa o sistema de notificações registrando todos os observers
     * @param array $config Configurações opcionais (ex: habilitar/desabilitar email)
     */
    public function initialize(array $config = []): void {
        if ($this->initialized) {
            return; // Já inicializado
        }

        // Observer para salvar no banco de dados
        $dbObserver = new DatabaseNotificationObserver();
        $this->eventManager->attach($dbObserver);

        // Observer para enviar emails
        $emailObserver = new EmailNotificationObserver();
        if (isset($config['emails_habilitados'])) {
            $emailObserver->setEmailsHabilitados($config['emails_habilitados']);
        }
        $this->eventManager->attach($emailObserver);

        // Observer para registrar logs
        $logFile = $config['log_file'] ?? null;
        $logObserver = new LogObserver($logFile);
        $this->eventManager->attach($logObserver);

        $this->initialized = true;
    }

    /**
     * Dispara um evento para notificar todos os observers
     * @param string $evento Nome do evento
     * @param array $dados Dados do evento
     */
    public function dispararEvento(string $evento, array $dados): void {
        if (!$this->initialized) {
            $this->initialize();
        }

        $this->eventManager->notify($evento, $dados);
    }

    /**
     * Métodos auxiliares para disparar eventos específicos
     */

    /**
     * Notifica sobre uma compra realizada
     * @param int $idUsuario
     * @param string $nomeUsuario
     * @param string $emailUsuario
     * @param string $tipoItem
     * @param int $idItem
     * @param string $nomeItem
     * @param float $valor
     */
    public function notificarCompra(
        int $idUsuario, 
        string $nomeUsuario, 
        string $emailUsuario,
        string $tipoItem,
        int $idItem,
        string $nomeItem,
        float $valor
    ): void {
        $this->dispararEvento('compra_realizada', [
            'id_usuario' => $idUsuario,
            'nome_usuario' => $nomeUsuario,
            'email_usuario' => $emailUsuario,
            'tipo_item' => $tipoItem,
            'id_item' => $idItem,
            'nome_item' => $nomeItem,
            'valor' => $valor
        ]);
    }

    /**
     * Notifica sobre compra de moedas
     * @param int $idUsuario
     * @param string $nomeUsuario
     * @param string $emailUsuario
     * @param int $quantidade
     * @param float $valorPago
     */
    public function notificarCompraMoedas(
        int $idUsuario,
        string $nomeUsuario,
        string $emailUsuario,
        int $quantidade,
        float $valorPago
    ): void {
        $this->dispararEvento('compra_moedas', [
            'id_usuario' => $idUsuario,
            'nome_usuario' => $nomeUsuario,
            'email_usuario' => $emailUsuario,
            'quantidade' => $quantidade,
            'valor_pago' => $valorPago
        ]);
    }

    /**
     * Notifica vitória em batalha
     * @param int $idUsuario
     * @param string $nomeUsuario
     * @param int $recompensa
     */
    public function notificarBatalhaVencida(
        int $idUsuario,
        string $nomeUsuario,
        int $recompensa
    ): void {
        $this->dispararEvento('batalha_vencida', [
            'id_usuario' => $idUsuario,
            'nome_usuario' => $nomeUsuario,
            'recompensa' => $recompensa
        ]);
    }

    /**
     * Notifica derrota em batalha
     * @param int $idUsuario
     * @param string $nomeUsuario
     */
    public function notificarBatalhaPerdida(
        int $idUsuario,
        string $nomeUsuario
    ): void {
        $this->dispararEvento('batalha_perdida', [
            'id_usuario' => $idUsuario,
            'nome_usuario' => $nomeUsuario
        ]);
    }

    /**
     * Notifica conquista desbloqueada
     * @param int $idUsuario
     * @param string $nomeUsuario
     * @param string $emailUsuario
     * @param string $nomeConquista
     * @param string $descricao
     */
    public function notificarConquista(
        int $idUsuario,
        string $nomeUsuario,
        string $emailUsuario,
        string $nomeConquista,
        string $descricao = ''
    ): void {
        $this->dispararEvento('conquista_desbloqueada', [
            'id_usuario' => $idUsuario,
            'nome_usuario' => $nomeUsuario,
            'email_usuario' => $emailUsuario,
            'nome_conquista' => $nomeConquista,
            'descricao' => $descricao
        ]);
    }

    /**
     * Notifica abertura de pacote
     * @param int $idUsuario
     * @param string $nomeUsuario
     * @param string $nomePacote
     * @param array $cartasRecebidas
     */
    public function notificarPacoteAberto(
        int $idUsuario,
        string $nomeUsuario,
        string $nomePacote,
        array $cartasRecebidas
    ): void {
        $this->dispararEvento('pacote_aberto', [
            'id_usuario' => $idUsuario,
            'nome_usuario' => $nomeUsuario,
            'nome_pacote' => $nomePacote,
            'cartas_recebidas' => $cartasRecebidas
        ]);
    }

    /**
     * Notifica aumento de nível
     * @param int $idUsuario
     * @param string $nomeUsuario
     * @param int $novoNivel
     */
    public function notificarNivelAumentado(
        int $idUsuario,
        string $nomeUsuario,
        int $novoNivel
    ): void {
        $this->dispararEvento('nivel_aumentado', [
            'id_usuario' => $idUsuario,
            'nome_usuario' => $nomeUsuario,
            'nivel' => $novoNivel
        ]);
    }

    /**
     * Notifica recebimento de presente
     * @param int $idUsuario
     * @param string $nomeUsuario
     * @param string $emailUsuario
     * @param string $descricao
     */
    public function notificarPresente(
        int $idUsuario,
        string $nomeUsuario,
        string $emailUsuario,
        string $descricao
    ): void {
        $this->dispararEvento('presente_recebido', [
            'id_usuario' => $idUsuario,
            'nome_usuario' => $nomeUsuario,
            'email_usuario' => $emailUsuario,
            'descricao' => $descricao
        ]);
    }

    /**
     * Notifica avaliação aprovada
     * @param int $idUsuario
     * @param string $nomeUsuario
     * @param string $tipoItem
     * @param int $idItem
     * @param string $nomeItem
     */
    public function notificarAvaliacaoAprovada(
        int $idUsuario,
        string $nomeUsuario,
        string $tipoItem,
        int $idItem,
        string $nomeItem
    ): void {
        $this->dispararEvento('avaliacao_aprovada', [
            'id_usuario' => $idUsuario,
            'nome_usuario' => $nomeUsuario,
            'tipo_item' => $tipoItem,
            'id_item' => $idItem,
            'nome_item' => $nomeItem
        ]);
    }

    /**
     * Notifica saldo baixo
     * @param int $idUsuario
     * @param string $nomeUsuario
     * @param int $saldoAtual
     */
    public function notificarSaldoBaixo(
        int $idUsuario,
        string $nomeUsuario,
        int $saldoAtual
    ): void {
        $this->dispararEvento('saldo_baixo', [
            'id_usuario' => $idUsuario,
            'nome_usuario' => $nomeUsuario,
            'saldo_atual' => $saldoAtual
        ]);
    }

    /**
     * Notifica novo usuário (boas-vindas)
     * @param int $idUsuario
     * @param string $nomeUsuario
     */
    public function notificarBoasVindas(
        int $idUsuario,
        string $nomeUsuario
    ): void {
        $this->dispararEvento('bem_vindo', [
            'id_usuario' => $idUsuario,
            'nome_usuario' => $nomeUsuario
        ]);
    }

    /**
     * Retorna o EventManager para acesso direto se necessário
     * @return EventManager
     */
    public function getEventManager(): EventManager {
        return $this->eventManager;
    }
}
