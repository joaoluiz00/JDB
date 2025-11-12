<?php
require_once __DIR__ . '/ObserverInterface.php';

/**
 * EmailNotificationObserver
 * Observador que envia notificações por email
 * (Implementação básica - pode ser expandida com PHPMailer ou similar)
 */
class EmailNotificationObserver implements ObserverInterface {
    private $emailsHabilitados = true; // Pode ser configurado
    
    /**
     * Recebe evento e envia email se necessário
     * @param string $evento
     * @param array $dados
     */
    public function update(string $evento, array $dados): void {
        if (!$this->emailsHabilitados) {
            return;
        }

        $email = $dados['email_usuario'] ?? null;
        
        if (!$email) {
            return; // Não há email para enviar
        }

        // Eventos que devem gerar emails
        $eventosEmail = [
            'compra_realizada',
            'compra_moedas',
            'presente_recebido',
            'conquista_desbloqueada'
        ];

        if (in_array($evento, $eventosEmail)) {
            $this->enviarEmail($evento, $email, $dados);
        }
    }

    /**
     * Envia email (implementação básica com mail() do PHP)
     * Para produção, recomenda-se usar PHPMailer ou SwiftMailer
     * @param string $evento
     * @param string $email
     * @param array $dados
     */
    private function enviarEmail(string $evento, string $email, array $dados): void {
        $assunto = $this->getAssunto($evento, $dados);
        $mensagem = $this->getMensagem($evento, $dados);
        $headers = $this->getHeaders();

        // Em ambiente de desenvolvimento, apenas loga
        // Em produção, descomente a linha abaixo:
        // mail($email, $assunto, $mensagem, $headers);
        
        // Log para desenvolvimento
        error_log("Email enviado para: $email | Assunto: $assunto");
    }

    /**
     * Retorna o assunto do email baseado no evento
     * @param string $evento
     * @param array $dados
     * @return string
     */
    private function getAssunto(string $evento, array $dados): string {
        $assuntos = [
            'compra_realizada' => 'JDB - Compra Realizada com Sucesso!',
            'compra_moedas' => 'JDB - Moedas Adicionadas à sua Conta',
            'presente_recebido' => 'JDB - Você Recebeu um Presente!',
            'conquista_desbloqueada' => 'JDB - Nova Conquista Desbloqueada!'
        ];

        return $assuntos[$evento] ?? 'JDB - Notificação';
    }

    /**
     * Retorna o corpo do email baseado no evento
     * @param string $evento
     * @param array $dados
     * @return string
     */
    private function getMensagem(string $evento, array $dados): string {
        $nomeUsuario = $dados['nome_usuario'] ?? 'Jogador';
        
        $mensagens = [
            'compra_realizada' => "
                Olá $nomeUsuario!
                
                Sua compra de " . ($dados['nome_item'] ?? 'item') . " foi realizada com sucesso!
                
                Acesse seu histórico de compras para mais detalhes.
                
                Obrigado por jogar JDB!
            ",
            'compra_moedas' => "
                Olá $nomeUsuario!
                
                Você comprou " . ($dados['quantidade'] ?? 0) . " moedas!
                Seu saldo foi atualizado.
                
                Aproveite suas moedas na nossa loja!
                
                Obrigado por jogar JDB!
            ",
            'presente_recebido' => "
                Olá $nomeUsuario!
                
                Você recebeu um presente especial!
                " . ($dados['descricao'] ?? 'Confira seu inventário para ver o que recebeu.') . "
                
                Obrigado por jogar JDB!
            ",
            'conquista_desbloqueada' => "
                Olá $nomeUsuario!
                
                Parabéns! Você desbloqueou uma nova conquista:
                🏆 " . ($dados['nome_conquista'] ?? 'Conquista Especial') . "
                
                Continue jogando para desbloquear mais conquistas!
                
                Obrigado por jogar JDB!
            "
        ];

        return $mensagens[$evento] ?? "Olá $nomeUsuario!\n\nVocê tem uma nova notificação no JDB!";
    }

    /**
     * Retorna os headers do email
     * @return string
     */
    private function getHeaders(): string {
        return "From: noreply@jdb.com\r\n" .
               "Reply-To: suporte@jdb.com\r\n" .
               "X-Mailer: PHP/" . phpversion() . "\r\n" .
               "Content-Type: text/plain; charset=UTF-8\r\n";
    }

    /**
     * Habilita ou desabilita envio de emails
     * @param bool $habilitado
     */
    public function setEmailsHabilitados(bool $habilitado): void {
        $this->emailsHabilitados = $habilitado;
    }
}
