<?php
/**
 * Interface SubjectInterface
 * Define o contrato para todos os Subjects (observáveis)
 * Padrão de Projeto: Observer
 */
interface SubjectInterface {
    /**
     * Adiciona um observador
     * @param ObserverInterface $observer
     */
    public function attach(ObserverInterface $observer): void;

    /**
     * Remove um observador
     * @param ObserverInterface $observer
     */
    public function detach(ObserverInterface $observer): void;

    /**
     * Notifica todos os observadores sobre um evento
     * @param string $evento Nome do evento
     * @param array $dados Dados do evento
     */
    public function notify(string $evento, array $dados): void;
}
