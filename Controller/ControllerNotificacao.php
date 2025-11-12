<?php
require_once __DIR__ . '/../Model/BancoDeDados.php';
require_once __DIR__ . '/../Model/Notificacao.php';

/**
 * ControllerNotificacao
 * Gerencia operações CRUD de notificações
 */
class ControllerNotificacao {
    private $db;

    public function __construct() {
        $this->db = BancoDeDados::getInstance()->getConnection();
    }

    /**
     * Cria uma nova notificação
     * @param Notificacao $notificacao
     * @return bool
     */
    public function criarNotificacao(Notificacao $notificacao): bool {
        try {
            $sql = "INSERT INTO notificacoes 
                    (id_usuario, tipo, titulo, mensagem, lida, icone, cor_fundo, link, data_hora, dados_extra) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $lida = $notificacao->isLida() ? 1 : 0;
            
            $stmt->bind_param(
                "isssssssss",
                $notificacao->getIdUsuario(),
                $notificacao->getTipo(),
                $notificacao->getTitulo(),
                $notificacao->getMensagem(),
                $lida,
                $notificacao->getIcone(),
                $notificacao->getCorFundo(),
                $notificacao->getLink(),
                $notificacao->getDataHora(),
                json_encode($notificacao->getDadosExtra())
            );
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Erro ao criar notificação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca notificações de um usuário
     * @param int $idUsuario
     * @param int $limite
     * @param bool $apenasNaoLidas
     * @return array
     */
    public function buscarNotificacoesUsuario(int $idUsuario, int $limite = 50, bool $apenasNaoLidas = false): array {
        try {
            $sql = "SELECT * FROM notificacoes WHERE id_usuario = ?";
            
            if ($apenasNaoLidas) {
                $sql .= " AND lida = 0";
            }
            
            $sql .= " ORDER BY data_hora DESC LIMIT ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ii", $idUsuario, $limite);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $notificacoes = [];
            
            while ($row = $result->fetch_assoc()) {
                $notificacoes[] = new Notificacao(
                    $row['id'],
                    $row['id_usuario'],
                    $row['tipo'],
                    $row['titulo'],
                    $row['mensagem'],
                    $row['lida'] == 1,
                    $row['icone'],
                    $row['cor_fundo'],
                    $row['link'],
                    $row['data_hora'],
                    json_decode($row['dados_extra'], true)
                );
            }
            
            return $notificacoes;
        } catch (Exception $e) {
            error_log("Erro ao buscar notificações: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Conta notificações não lidas de um usuário
     * @param int $idUsuario
     * @return int
     */
    public function contarNaoLidas(int $idUsuario): int {
        try {
            $sql = "SELECT COUNT(*) as total FROM notificacoes WHERE id_usuario = ? AND lida = 0";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $idUsuario);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Erro ao contar notificações: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Marca uma notificação como lida
     * @param int $idNotificacao
     * @return bool
     */
    public function marcarComoLida(int $idNotificacao): bool {
        try {
            $sql = "UPDATE notificacoes SET lida = 1 WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $idNotificacao);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Erro ao marcar notificação como lida: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Marca todas as notificações de um usuário como lidas
     * @param int $idUsuario
     * @return bool
     */
    public function marcarTodasComoLidas(int $idUsuario): bool {
        try {
            $sql = "UPDATE notificacoes SET lida = 1 WHERE id_usuario = ? AND lida = 0";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $idUsuario);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Erro ao marcar todas como lidas: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deleta uma notificação
     * @param int $idNotificacao
     * @return bool
     */
    public function deletarNotificacao(int $idNotificacao): bool {
        try {
            $sql = "DELETE FROM notificacoes WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $idNotificacao);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Erro ao deletar notificação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deleta notificações antigas (mais de X dias)
     * @param int $dias
     * @return int Número de notificações deletadas
     */
    public function limparNotificacoesAntigas(int $dias = 30): int {
        try {
            $sql = "DELETE FROM notificacoes WHERE data_hora < DATE_SUB(NOW(), INTERVAL ? DAY)";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $dias);
            $stmt->execute();
            
            return $stmt->affected_rows;
        } catch (Exception $e) {
            error_log("Erro ao limpar notificações antigas: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Busca uma notificação específica
     * @param int $idNotificacao
     * @return Notificacao|null
     */
    public function buscarNotificacao(int $idNotificacao): ?Notificacao {
        try {
            $sql = "SELECT * FROM notificacoes WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $idNotificacao);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            if ($row) {
                return new Notificacao(
                    $row['id'],
                    $row['id_usuario'],
                    $row['tipo'],
                    $row['titulo'],
                    $row['mensagem'],
                    $row['lida'] == 1,
                    $row['icone'],
                    $row['cor_fundo'],
                    $row['link'],
                    $row['data_hora'],
                    json_decode($row['dados_extra'], true)
                );
            }
            
            return null;
        } catch (Exception $e) {
            error_log("Erro ao buscar notificação: " . $e->getMessage());
            return null;
        }
    }
}
