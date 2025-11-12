<?php
require_once __DIR__ . '/ObserverInterface.php';
require_once __DIR__ . '/SubjectInterface.php';

/**
 * Classe EventManager
 * Gerencia eventos e notificações usando o padrão Observer
 * Implementa Singleton para garantir uma única instância
 */
class EventManager implements SubjectInterface {
    private static $instance = null;
    private $observers = [];

    // Construtor privado para Singleton
    private function __construct() {}

    // Previne clonagem
    private function __clone() {}

    /**
     * Retorna a instância única do EventManager
     * @return EventManager
     */
    public static function getInstance(): EventManager {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Adiciona um observador
     * @param ObserverInterface $observer
     */
    public function attach(ObserverInterface $observer): void {
        $observerClass = get_class($observer);
        
        if (!isset($this->observers[$observerClass])) {
            $this->observers[$observerClass] = $observer;
        }
    }

    /**
     * Remove um observador
     * @param ObserverInterface $observer
     */
    public function detach(ObserverInterface $observer): void {
        $observerClass = get_class($observer);
        
        if (isset($this->observers[$observerClass])) {
            unset($this->observers[$observerClass]);
        }
    }

    /**
     * Notifica todos os observadores sobre um evento
     * @param string $evento Nome do evento (ex: 'compra_realizada', 'batalha_vencida')
     * @param array $dados Dados relacionados ao evento
     */
    public function notify(string $evento, array $dados): void {
        foreach ($this->observers as $observer) {
            try {
                $observer->update($evento, $dados);
            } catch (Exception $e) {
                // Log do erro mas continua notificando outros observers
                error_log("Erro ao notificar observer: " . $e->getMessage());
            }
        }
    }

    /**
     * Remove todos os observadores
     */
    public function clearObservers(): void {
        $this->observers = [];
    }

    /**
     * Retorna o número de observadores registrados
     * @return int
     */
    public function countObservers(): int {
        return count($this->observers);
    }
}
