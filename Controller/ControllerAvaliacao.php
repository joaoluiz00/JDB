<?php
require_once __DIR__ . '/../Model/BancoDeDados.php';
require_once __DIR__ . '/../Model/Avaliacao.php';
require_once __DIR__ . '/../Service/SentimentAnalysisService.php';

class ControllerAvaliacao {
    private $db;
    private $sentimentService;

    public function __construct() {
        $this->db = BancoDeDados::getInstance('localhost', 'root', '', 'banco');
        $this->sentimentService = new SentimentAnalysisService();
    }

    /**
     * Cria uma nova avaliação
     * @param Avaliacao $avaliacao
     * @return int|false ID da avaliação criada ou false em caso de erro
     */
    public function criarAvaliacao($avaliacao) {
        $conn = $this->db->getConnection();
        
        // Analisa o sentimento do comentário usando IA
        $sentimento = $this->sentimentService->analisarSentimento($avaliacao->getComentario());
        $avaliacao->setSentimento($sentimento);

        $sql = "INSERT INTO avaliacoes (id_usuario, tipo_item, id_item, nota, comentario, sentimento, data_avaliacao) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        $idUsuario = $avaliacao->getIdUsuario();
        $tipoItem = $avaliacao->getTipoItem();
        $idItem = $avaliacao->getIdItem();
        $nota = $avaliacao->getNota();
        $comentario = $avaliacao->getComentario();
        
        $stmt->bind_param('isiiss', $idUsuario, $tipoItem, $idItem, $nota, $comentario, $sentimento);
        
        if ($stmt->execute()) {
            $idAvaliacao = $stmt->insert_id;
            $stmt->close();
            return $idAvaliacao;
        }
        
        $stmt->close();
        return false;
    }

    /**
     * Adiciona imagem a uma avaliação
     * @param int $idAvaliacao
     * @param string $pathImagem
     * @return bool
     */
    public function adicionarImagem($idAvaliacao, $pathImagem) {
        $conn = $this->db->getConnection();
        $sql = "INSERT INTO avaliacoes_imagens (id_avaliacao, path_imagem, data_upload) VALUES (?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('is', $idAvaliacao, $pathImagem);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Busca todas as avaliações de um produto específico
     * @param string $tipoItem
     * @param int $idItem
     * @return array Array de objetos Avaliacao
     */
    public function getAvaliacoesPorProduto($tipoItem, $idItem) {
        $conn = $this->db->getConnection();
        $sql = "SELECT a.*, u.nome as nome_usuario 
                FROM avaliacoes a 
                JOIN usuario u ON a.id_usuario = u.id 
                WHERE a.tipo_item = ? AND a.id_item = ? 
                ORDER BY a.data_avaliacao DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $tipoItem, $idItem);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $avaliacoes = [];
        while ($row = $result->fetch_assoc()) {
            // Busca as imagens da avaliação
            $imagens = $this->getImagensAvaliacao($row['id']);
            
            $avaliacao = new Avaliacao(
                $row['id'],
                $row['id_usuario'],
                $row['tipo_item'],
                $row['id_item'],
                $row['nota'],
                $row['comentario'],
                $row['sentimento'],
                $row['data_avaliacao'],
                $imagens,
                $row['nome_usuario']
            );
            $avaliacoes[] = $avaliacao;
        }
        
        $stmt->close();
        return $avaliacoes;
    }

    /**
     * Busca imagens de uma avaliação
     * @param int $idAvaliacao
     * @return array
     */
    public function getImagensAvaliacao($idAvaliacao) {
        $conn = $this->db->getConnection();
        $sql = "SELECT path_imagem FROM avaliacoes_imagens WHERE id_avaliacao = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $idAvaliacao);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $imagens = [];
        while ($row = $result->fetch_assoc()) {
            $imagens[] = $row['path_imagem'];
        }
        
        $stmt->close();
        return $imagens;
    }

    /**
     * Calcula a média de avaliações de um produto
     * @param string $tipoItem
     * @param int $idItem
     * @return array ['media' => float, 'total' => int]
     */
    public function getMediaAvaliacoes($tipoItem, $idItem) {
        $conn = $this->db->getConnection();
        $sql = "SELECT AVG(nota) as media, COUNT(*) as total 
                FROM avaliacoes 
                WHERE tipo_item = ? AND id_item = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $tipoItem, $idItem);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return [
            'media' => round($row['media'], 1),
            'total' => $row['total']
        ];
    }

    /**
     * Verifica se o usuário já avaliou um produto
     * @param int $idUsuario
     * @param string $tipoItem
     * @param int $idItem
     * @return bool
     */
    public function usuarioJaAvaliou($idUsuario, $tipoItem, $idItem) {
        $conn = $this->db->getConnection();
        $sql = "SELECT COUNT(*) as count FROM avaliacoes 
                WHERE id_usuario = ? AND tipo_item = ? AND id_item = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('isi', $idUsuario, $tipoItem, $idItem);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['count'] > 0;
    }

    /**
     * Verifica se o usuário comprou ou possui o produto
     * @param int $idUsuario
     * @param string $tipoItem
     * @param int $idItem
     * @return bool
     */
    public function usuarioComprouProduto($idUsuario, $tipoItem, $idItem) {
        $conn = $this->db->getConnection();
        
        // 1. Verifica se o usuário JÁ POSSUI o item no inventário
        switch ($tipoItem) {
            case 'carta':
                $sql = "SELECT COUNT(*) as count FROM cartas_usuario WHERE id_usuario = ? AND id_carta = ?";
                break;
            case 'icone':
                $sql = "SELECT COUNT(*) as count FROM icones_usuario WHERE id_usuario = ? AND id_icone = ?";
                break;
            case 'pacote':
                // Pacotes não ficam no inventário após abertura, então verifica apenas compras
                $sql = null;
                break;
            case 'papel_fundo':
                $sql = "SELECT COUNT(*) as count FROM papel_parede_usuario WHERE id_usuario = ? AND id_papel = ?";
                break;
            default:
                $sql = null;
        }
        
        // Verifica posse do item
        if ($sql !== null) {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ii', $idUsuario, $idItem);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            if ($row['count'] > 0) {
                return true; // Usuário possui o item!
            }
        }
        
        // 2. Verifica em pedidos de dinheiro (histórico de compras)
        $sql = "SELECT COUNT(*) as count FROM pedido_itens pi 
                JOIN pedidos p ON pi.id_pedido = p.id 
                WHERE p.id_usuario = ? AND pi.tipo_item = ? AND pi.id_item = ? 
                AND p.status IN ('processando', 'enviado', 'entregue')";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('isi', $idUsuario, $tipoItem, $idItem);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        if ($row['count'] > 0) {
            return true;
        }

        // 3. Verifica em compras com moedas (histórico_transacoes)
        $sql = "SELECT COUNT(*) as count FROM historico_transacoes 
                WHERE id_usuario = ? AND tipo_transacao = ? AND id_item = ? AND metodo_pagamento = 'moedas'";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('isi', $idUsuario, $tipoItem, $idItem);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['count'] > 0;
    }

    /**
     * Gera resumo IA das avaliações de um produto
     * @param string $tipoItem
     * @param int $idItem
     * @return string
     */
    public function gerarResumoAvaliacoes($tipoItem, $idItem) {
        $avaliacoes = $this->getAvaliacoesPorProduto($tipoItem, $idItem);
        
        if (empty($avaliacoes)) {
            return "Nenhuma avaliação disponível ainda. Seja o primeiro a avaliar!";
        }

        $comentarios = array_map(function($avaliacao) {
            return $avaliacao->getComentario();
        }, $avaliacoes);

        return $this->sentimentService->gerarResumo($comentarios);
    }

    /**
     * Busca avaliações de um usuário
     * @param int $idUsuario
     * @return array
     */
    public function getAvaliacoesPorUsuario($idUsuario) {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM avaliacoes WHERE id_usuario = ? ORDER BY data_avaliacao DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $idUsuario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $avaliacoes = [];
        while ($row = $result->fetch_assoc()) {
            $imagens = $this->getImagensAvaliacao($row['id']);
            
            $avaliacao = new Avaliacao(
                $row['id'],
                $row['id_usuario'],
                $row['tipo_item'],
                $row['id_item'],
                $row['nota'],
                $row['comentario'],
                $row['sentimento'],
                $row['data_avaliacao'],
                $imagens
            );
            $avaliacoes[] = $avaliacao;
        }
        
        $stmt->close();
        return $avaliacoes;
    }

    /**
     * Deleta uma avaliação (apenas o próprio usuário pode deletar)
     * @param int $idAvaliacao
     * @param int $idUsuario
     * @return bool
     */
    public function deletarAvaliacao($idAvaliacao, $idUsuario) {
        $conn = $this->db->getConnection();
        
        // Verifica se a avaliação pertence ao usuário
        $sql = "DELETE FROM avaliacoes WHERE id = ? AND id_usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $idAvaliacao, $idUsuario);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    /**
     * Resolve o nome do item
     * @param string $tipo
     * @param int $idItem
     * @return string
     */
    public function resolveItemNome($tipo, $idItem) {
        $conn = $this->db->getConnection();
        switch ($tipo) {
            case 'carta':
                $sql = 'SELECT nome FROM cartas WHERE id = ?'; 
                break;
            case 'icone':
                $sql = 'SELECT nome FROM img_perfil WHERE id = ?'; 
                break;
            case 'pacote':
                $sql = 'SELECT nome FROM pacote WHERE id = ?'; 
                break;
            case 'papel_fundo':
                $sql = 'SELECT nome FROM papel_parede WHERE id = ?'; 
                break;
            default:
                return 'Item #' . $idItem;
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $idItem);
        $stmt->execute();
        $res = $stmt->get_result();
        $nome = 'Item #' . $idItem;
        if ($row = $res->fetch_assoc()) { 
            $nome = $row['nome']; 
        }
        $stmt->close();
        return $nome;
    }
}
