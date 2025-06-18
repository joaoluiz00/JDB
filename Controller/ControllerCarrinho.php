<?php
require_once '../Model/BancoDeDados.php';

class ControllerCarrinho {
    private $db;

    public function __construct() {
        $this->db = BancoDeDados::getInstance('localhost', 'root', '', 'banco');
    }

    public function adicionarItem($idUsuario, $tipoItem, $idItem, $precoUnitario, $precoMoedas = 0, $quantidade = 1) {
        $conn = $this->db->getConnection();
        
        // Para ícones, verificar se o usuário já possui
        if ($tipoItem === 'icone') {
            $sqlVerificar = "SELECT id FROM icones_usuario WHERE id_usuario = ? AND id_icone = ?";
            $stmtVerificar = $conn->prepare($sqlVerificar);
            $stmtVerificar->bind_param("ii", $idUsuario, $idItem);
            $stmtVerificar->execute();
            $resultVerificar = $stmtVerificar->get_result();
            $stmtVerificar->close();
            
            if ($resultVerificar->num_rows > 0) {
                return "already_owned"; // Usuário já possui este ícone
            }
            
            // Para ícones, sempre quantidade = 1
            $quantidade = 1;
        }
        
        // Verificar se item já existe no carrinho
        $sqlCheck = "SELECT id, quantidade FROM carrinho WHERE id_usuario = ? AND tipo_item = ? AND id_item = ?";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bind_param("isi", $idUsuario, $tipoItem, $idItem);
        $stmtCheck->execute();
        $result = $stmtCheck->get_result();
        $stmtCheck->close();

        if ($result->num_rows > 0) {
            // Item já existe no carrinho
            if ($tipoItem === 'icone') {
                return "already_in_cart"; // Ícone já está no carrinho
            }
            
            // Para outros itens, atualizar quantidade
            $item = $result->fetch_assoc();
            $novaQuantidade = $item['quantidade'] + $quantidade;
            
            $sqlUpdate = "UPDATE carrinho SET quantidade = ? WHERE id = ?";
            $stmtUpdate = $conn->prepare($sqlUpdate);
            $stmtUpdate->bind_param("ii", $novaQuantidade, $item['id']);
            $resultado = $stmtUpdate->execute();
            $stmtUpdate->close();
            return $resultado;
        } else {
            // Novo item
            $sql = "INSERT INTO carrinho (id_usuario, tipo_item, id_item, quantidade, preco_unitario, preco_moedas, tipo_pagamento) VALUES (?, ?, ?, ?, ?, ?, 'dinheiro')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isiidi", $idUsuario, $tipoItem, $idItem, $quantidade, $precoUnitario, $precoMoedas);
            $resultado = $stmt->execute();
            $stmt->close();
            return $resultado;
        }
    }

    public function getItensCarrinho($idUsuario) {
        $conn = $this->db->getConnection();
        $sql = "SELECT c.*, 
                CASE 
                    WHEN c.tipo_item = 'carta' THEN ca.nome
                    WHEN c.tipo_item = 'icone' THEN ip.nome
                    WHEN c.tipo_item = 'pacote' THEN p.nome
                    WHEN c.tipo_item = 'papel_fundo' THEN pf.nome
                END as nome,
                CASE 
                    WHEN c.tipo_item = 'carta' THEN ca.path
                    WHEN c.tipo_item = 'icone' THEN ip.path
                    WHEN c.tipo_item = 'pacote' THEN p.path
                    WHEN c.tipo_item = 'papel_fundo' THEN pf.path
                END as path
                FROM carrinho c
                LEFT JOIN cartas ca ON c.tipo_item = 'carta' AND c.id_item = ca.id
                LEFT JOIN img_perfil ip ON c.tipo_item = 'icone' AND c.id_item = ip.id
                LEFT JOIN pacote p ON c.tipo_item = 'pacote' AND c.id_item = p.id
                LEFT JOIN papel_fundo pf ON c.tipo_item = 'papel_fundo' AND c.id_item = pf.id
                WHERE c.id_usuario = ?
                ORDER BY c.data_adicao DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    public function alterarTipoPagamento($idUsuario, $idCarrinho, $tipoPagamento) {
        $conn = $this->db->getConnection();
        $sql = "UPDATE carrinho SET tipo_pagamento = ? WHERE id = ? AND id_usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $tipoPagamento, $idCarrinho, $idUsuario);
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    public function atualizarQuantidade($idUsuario, $idCarrinho, $novaQuantidade) {
        $conn = $this->db->getConnection();
        
        // Verificar se é um ícone
        $sqlVerificar = "SELECT tipo_item FROM carrinho WHERE id = ? AND id_usuario = ?";
        $stmtVerificar = $conn->prepare($sqlVerificar);
        $stmtVerificar->bind_param("ii", $idCarrinho, $idUsuario);
        $stmtVerificar->execute();
        $resultVerificar = $stmtVerificar->get_result();
        $item = $resultVerificar->fetch_assoc();
        $stmtVerificar->close();
        
        // Se for ícone, não permitir quantidade > 1
        if ($item && $item['tipo_item'] === 'icone' && $novaQuantidade > 1) {
            return "icon_quantity_limit";
        }
        
        if ($novaQuantidade <= 0) {
            return $this->removerItem($idUsuario, $idCarrinho);
        }
        
        $sql = "UPDATE carrinho SET quantidade = ? WHERE id = ? AND id_usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $novaQuantidade, $idCarrinho, $idUsuario);
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    public function calcularTotal($idUsuario, $tipoPagamento = 'dinheiro') {
        $conn = $this->db->getConnection();
        
        if ($tipoPagamento === 'moedas') {
            $sql = "SELECT SUM(quantidade * preco_moedas) as total FROM carrinho WHERE id_usuario = ? AND tipo_pagamento = 'moedas'";
        } else {
            $sql = "SELECT SUM(quantidade * preco_unitario) as total FROM carrinho WHERE id_usuario = ? AND tipo_pagamento = 'dinheiro'";
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $total = $result->fetch_assoc()['total'] ?? 0;
        $stmt->close();
        return $total;
    }

    public function calcularTotalMisto($idUsuario) {
        $conn = $this->db->getConnection();
        
        // Total em dinheiro
        $sqlDinheiro = "SELECT SUM(quantidade * preco_unitario) as total FROM carrinho WHERE id_usuario = ? AND tipo_pagamento = 'dinheiro'";
        $stmt = $conn->prepare($sqlDinheiro);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $totalDinheiro = $result->fetch_assoc()['total'] ?? 0;
        $stmt->close();
        
        // Total em moedas
        $sqlMoedas = "SELECT SUM(quantidade * preco_moedas) as total FROM carrinho WHERE id_usuario = ? AND tipo_pagamento = 'moedas'";
        $stmt = $conn->prepare($sqlMoedas);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $totalMoedas = $result->fetch_assoc()['total'] ?? 0;
        $stmt->close();
        
        return [
            'dinheiro' => $totalDinheiro,
            'moedas' => $totalMoedas
        ];
    }

    public function removerItem($idUsuario, $idCarrinho) {
        $conn = $this->db->getConnection();
        $sql = "DELETE FROM carrinho WHERE id = ? AND id_usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $idCarrinho, $idUsuario);
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    public function limparCarrinho($idUsuario) {
        $conn = $this->db->getConnection();
        $sql = "DELETE FROM carrinho WHERE id_usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    public function validarCupom($codigo) {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM cupons WHERE codigo = ? AND ativo = 1 AND data_inicio <= NOW() AND data_fim >= NOW() AND (uso_maximo IS NULL OR uso_atual < uso_maximo)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $codigo);
        $stmt->execute();
        $result = $stmt->get_result();
        $cupom = $result->fetch_assoc();
        $stmt->close();
        return $cupom;
    }
}
?>