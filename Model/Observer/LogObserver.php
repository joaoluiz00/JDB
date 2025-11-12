<?php
require_once __DIR__ . '/ObserverInterface.php';

/**
 * LogObserver
 * Observador que registra todos os eventos em arquivo de log
 */
class LogObserver implements ObserverInterface {
    private $logFile;

    public function __construct($logFile = null) {
        $this->logFile = $logFile ?? __DIR__ . '/../../logs/eventos.log';
        
        // Cria diretório de logs se não existir
        $logDir = dirname($this->logFile);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }
    }

    /**
     * Registra o evento no arquivo de log
     * @param string $evento
     * @param array $dados
     */
    public function update(string $evento, array $dados): void {
        $timestamp = date('Y-m-d H:i:s');
        $idUsuario = $dados['id_usuario'] ?? 'N/A';
        $nomeUsuario = $dados['nome_usuario'] ?? 'N/A';
        
        $logEntry = sprintf(
            "[%s] EVENTO: %s | USUÁRIO: %s (ID: %s) | DADOS: %s\n",
            $timestamp,
            strtoupper($evento),
            $nomeUsuario,
            $idUsuario,
            json_encode($dados, JSON_UNESCAPED_UNICODE)
        );

        // Adiciona separador visual para facilitar leitura
        $logEntry .= str_repeat('-', 100) . "\n";

        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }

    /**
     * Retorna as últimas N linhas do log
     * @param int $linhas
     * @return array
     */
    public function getUltimosLogs(int $linhas = 50): array {
        if (!file_exists($this->logFile)) {
            return [];
        }

        $file = new SplFileObject($this->logFile, 'r');
        $file->seek(PHP_INT_MAX);
        $lastLine = $file->key();
        $lines = [];

        $startLine = max(0, $lastLine - $linhas);
        $file->seek($startLine);

        while (!$file->eof()) {
            $lines[] = $file->current();
            $file->next();
        }

        return array_filter($lines);
    }

    /**
     * Limpa o arquivo de log
     */
    public function limparLog(): void {
        if (file_exists($this->logFile)) {
            file_put_contents($this->logFile, '');
        }
    }
}
