<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../View/index.php");
    die();
}

require_once '../Controller/ControllerCarrinho.php';
require_once '../Controller/ControllerUsuario.php';
require_once '../Model/BancoDeDados.php';

$userId = $_SESSION['id'];
$userController = new ControllerUsuario();
$carrinhoController = new ControllerCarrinho();

// Dados do cartão
$numero = $_POST['numero'] ?? '';
$portador = $_POST['portador'] ?? '';
$validade = $_POST['validade'] ?? '';
$cvv = $_POST['cvv'] ?? '';

// Dados da compra
$isCarrinho = ($_POST['is_carrinho'] ?? '0') === '1';

try {
    $conn = BancoDeDados::getInstance('localhost', 'root', '', 'banco')->getConnection();
    
    // Salvar cartão (criptografado seria ideal em produção)
    $numeroHash = hash('sha256', $numero); // Simular criptografia
    $sqlCartao = "INSERT INTO cartoes (id_usuario, numero, portador, validade, cvv) VALUES (?, ?, ?, ?, ?)";
    $stmtCartao = $conn->prepare($sqlCartao);
    $stmtCartao->bind_param("issss", $userId, $numeroHash, $portador, $validade, $cvv);
    $stmtCartao->execute();
    $idCartao = $conn->insert_id;
    $stmtCartao->close();
    
    // Iniciar transação para o pedido
    $conn->begin_transaction();
    
    if ($isCarrinho) {
        // Processar compra do carrinho
        $total = $_POST['total'] ?? 0;
        $itensCarrinho = $carrinhoController->getItensCarrinho($userId);
        
        // Criar pedido
    $sqlPedido = "INSERT INTO pedidos (id_usuario, total, metodo_pagamento, status, hash_transacao, data_pedido) VALUES (?, ?, 'cartao', 'enviado', ?, NOW())";
        $hashTransacao = md5($userId . time() . $total);
    $stmtPedido = $conn->prepare($sqlPedido);
    $stmtPedido->bind_param("ids", $userId, $total, $hashTransacao);
        $stmtPedido->execute();
        $idPedido = $conn->insert_id;
        $stmtPedido->close();
        
        // Adicionar itens do pedido e dar os itens ao usuário
        while ($item = $itensCarrinho->fetch_assoc()) {
            if ($item['tipo_pagamento'] === 'dinheiro') {
                // Adicionar ao pedido
                $sqlItem = "INSERT INTO pedido_itens (id_pedido, tipo_item, id_item, quantidade, preco_unitario) VALUES (?, ?, ?, ?, ?)";
                $stmtItem = $conn->prepare($sqlItem);
                $stmtItem->bind_param("isiid", $idPedido, $item['tipo_item'], $item['id_item'], $item['quantidade'], $item['preco_unitario']);
                $stmtItem->execute();
                $stmtItem->close();
                
                // Dar o item ao usuário
                switch ($item['tipo_item']) {
                    case 'carta':
                        for ($i = 0; $i < $item['quantidade']; $i++) {
                            $sqlCarta = "INSERT INTO cartas_usuario (id_usuario, id_carta) VALUES (?, ?)";
                            $stmtCarta = $conn->prepare($sqlCarta);
                            $stmtCarta->bind_param("ii", $userId, $item['id_item']);
                            $stmtCarta->execute();
                            $stmtCarta->close();
                        }
                        break;
                    case 'icone':
                        $sqlIcone = "INSERT INTO icones_usuario (id_usuario, id_icone) VALUES (?, ?)";
                        $stmtIcone = $conn->prepare($sqlIcone);
                        $stmtIcone->bind_param("ii", $userId, $item['id_item']);
                        $stmtIcone->execute();
                        $stmtIcone->close();
                        break;
                    case 'papel_fundo':
                        $sqlPapel = "INSERT INTO papel_fundo_usuario (id_usuario, id_papel) VALUES (?, ?)";
                        $stmtPapel = $conn->prepare($sqlPapel);
                        $stmtPapel->bind_param("ii", $userId, $item['id_item']);
                        $stmtPapel->execute();
                        $stmtPapel->close();
                        break;
                }
            }
        }
        
        // Limpar carrinho
        $carrinhoController->limparCarrinho($userId);
        
    } else {
        // Compra individual
        $idItem = $_POST['id_item'] ?? 0;
        $tipoItem = $_POST['tipo_item'] ?? '';
        $precoUnitario = $_POST['preco_dinheiro'] ?? 0;
        
        // Criar pedido
    $sqlPedido = "INSERT INTO pedidos (id_usuario, total, metodo_pagamento, status, hash_transacao, data_pedido) VALUES (?, ?, 'cartao', 'enviado', ?, NOW())";
        $hashTransacao = md5($userId . time() . $precoUnitario);
    $stmtPedido = $conn->prepare($sqlPedido);
    $stmtPedido->bind_param("ids", $userId, $precoUnitario, $hashTransacao);
        $stmtPedido->execute();
        $idPedido = $conn->insert_id;
        $stmtPedido->close();
        
        // Adicionar item do pedido
        $sqlItem = "INSERT INTO pedido_itens (id_pedido, tipo_item, id_item, quantidade, preco_unitario) VALUES (?, ?, ?, 1, ?)";
        $stmtItem = $conn->prepare($sqlItem);
        $stmtItem->bind_param("isid", $idPedido, $tipoItem, $idItem, $precoUnitario);
        $stmtItem->execute();
        $stmtItem->close();
        
        // Dar o item ao usuário
        switch ($tipoItem) {
            case 'carta':
                $sqlCarta = "INSERT INTO cartas_usuario (id_usuario, id_carta) VALUES (?, ?)";
                $stmtCarta = $conn->prepare($sqlCarta);
                $stmtCarta->bind_param("ii", $userId, $idItem);
                $stmtCarta->execute();
                $stmtCarta->close();
                break;
            case 'icone':
                $sqlIcone = "INSERT INTO icones_usuario (id_usuario, id_icone) VALUES (?, ?)";
                $stmtIcone = $conn->prepare($sqlIcone);
                $stmtIcone->bind_param("ii", $userId, $idItem);
                $stmtIcone->execute();
                $stmtIcone->close();
                break;
            case 'papel_fundo':
                $sqlPapel = "INSERT INTO papel_fundo_usuario (id_usuario, id_papel) VALUES (?, ?)";
                $stmtPapel = $conn->prepare($sqlPapel);
                $stmtPapel->bind_param("ii", $userId, $idItem);
                $stmtPapel->execute();
                $stmtPapel->close();
                break;
        }
    }
    
    // Confirmar transação
    $conn->commit();
    
    $_SESSION['success'] = "Compra realizada com sucesso! Cartão salvo e itens adicionados à sua conta.";
    header("Location: ../View/Home.php");
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    $_SESSION['error'] = "Erro ao processar compra: " . $e->getMessage();
    header("Location: ../View/Carrinho.php");
}

die();
?>