<?php
/**
 * Interface ObserverInterface
 * Define o contrato para todos os observadores do sistema
 * Padrão de Projeto: Observer
 */
interface ObserverInterface {
    /**
     * Método chamado quando o Subject notifica os observadores
     * @param string $evento Nome do evento ocorrido
     * @param array $dados Dados relacionados ao evento
     */
    public function update(string $evento, array $dados): void;
}
