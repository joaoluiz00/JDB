<?php
session_start();
if (!isset($_SESSION['id'])) { header('Location: ../View/index.php'); die(); }

require_once __DIR__ . '/../Controller/ControllerCarrinho.php';
require_once __DIR__ . '/../Model/BancoDeDados.php';

$userId = $_SESSION['id'];
$tipoCompra = $_POST['tipo_compra'] ?? 'individual';
$hash = $_POST['hash_transacao'] ?? md5($userId . time());
$total = floatval($_POST['total'] ?? 0);

$conn = BancoDeDados::getInstance('localhost', 'root', '', 'banco')->getConnection();
$carrinhoController = new ControllerCarrinho();

try {
    $conn->begin_transaction();

    // Criar pedido
    $sqlPedido = "INSERT INTO pedidos (id_usuario, total, metodo_pagamento, status, hash_transacao, data_pedido) VALUES (?, ?, 'pix', 'processando', ?, NOW())";
    $stmtPedido = $conn->prepare($sqlPedido);
    $stmtPedido->bind_param('ids', $userId, $total, $hash);
    $stmtPedido->execute();
    $idPedido = $conn->insert_id;
    $stmtPedido->close();

    // Endereço (opcional)
    $endereco = isset($_POST['endereco']) && is_array($_POST['endereco']) ? $_POST['endereco'] : [];
    if (!empty($endereco)) {
        $sqlEnd = "INSERT INTO enderecos_entrega (id_pedido, cep, rua, numero, complemento, bairro, cidade, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtEnd = $conn->prepare($sqlEnd);
        $cep = $endereco['cep'] ?? '';
        $rua = $endereco['rua'] ?? '';
        $numero = $endereco['numero'] ?? '';
        $complemento = $endereco['complemento'] ?? null;
        $bairro = $endereco['bairro'] ?? '';
        $cidade = $endereco['cidade'] ?? '';
        $estado = $endereco['estado'] ?? '';
        $stmtEnd->bind_param('isssssss', $idPedido, $cep, $rua, $numero, $complemento, $bairro, $cidade, $estado);
        $stmtEnd->execute();
        $stmtEnd->close();
    }

    if ($tipoCompra === 'carrinho') {
        $itens = $carrinhoController->getItensCarrinho($userId);
        while ($item = $itens->fetch_assoc()) {
            if ($item['tipo_pagamento'] === 'dinheiro') {
                // Registrar item no pedido
                $sqlItem = "INSERT INTO pedido_itens (id_pedido, tipo_item, id_item, quantidade, preco_unitario) VALUES (?, ?, ?, ?, ?)";
                $stmtItem = $conn->prepare($sqlItem);
                $stmtItem->bind_param('isiid', $idPedido, $item['tipo_item'], $item['id_item'], $item['quantidade'], $item['preco_unitario']);
                $stmtItem->execute();
                $stmtItem->close();

                // Conceder item ao usuário
                switch ($item['tipo_item']) {
                    case 'carta':
                        for ($i = 0; $i < $item['quantidade']; $i++) {
                            $sqlCarta = "INSERT INTO cartas_usuario (id_usuario, id_carta) VALUES (?, ?)";
                            $stmtCarta = $conn->prepare($sqlCarta);
                            $stmtCarta->bind_param('ii', $userId, $item['id_item']);
                            $stmtCarta->execute();
                            $stmtCarta->close();
                        }
                        break;
                    case 'icone':
                        $sqlIcone = "INSERT INTO icones_usuario (id_usuario, id_icone) VALUES (?, ?)";
                        $stmtIcone = $conn->prepare($sqlIcone);
                        $stmtIcone->bind_param('ii', $userId, $item['id_item']);
                        $stmtIcone->execute();
                        $stmtIcone->close();
                        break;
                    case 'papel_fundo':
                        $sqlPapel = "INSERT INTO papel_fundo_usuario (id_usuario, id_papel) VALUES (?, ?)";
                        $stmtPapel = $conn->prepare($sqlPapel);
                        $stmtPapel->bind_param('ii', $userId, $item['id_item']);
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
        $idItem = intval($_POST['id_item'] ?? 0);
        $tipoItem = $_POST['tipo_item'] ?? 'carta';
        $preco = floatval($_POST['preco_dinheiro'] ?? 0);

        $sqlItem = "INSERT INTO pedido_itens (id_pedido, tipo_item, id_item, quantidade, preco_unitario) VALUES (?, ?, ?, 1, ?)";
        $stmtItem = $conn->prepare($sqlItem);
        $stmtItem->bind_param('isid', $idPedido, $tipoItem, $idItem, $preco);
        $stmtItem->execute();
        $stmtItem->close();

        // Conceder item ao usuário
        switch ($tipoItem) {
            case 'carta':
                $sqlCarta = "INSERT INTO cartas_usuario (id_usuario, id_carta) VALUES (?, ?)";
                $stmtCarta = $conn->prepare($sqlCarta);
                $stmtCarta->bind_param('ii', $userId, $idItem);
                $stmtCarta->execute();
                $stmtCarta->close();
                break;
            case 'icone':
                $sqlIcone = "INSERT INTO icones_usuario (id_usuario, id_icone) VALUES (?, ?)";
                $stmtIcone = $conn->prepare($sqlIcone);
                $stmtIcone->bind_param('ii', $userId, $idItem);
                $stmtIcone->execute();
                $stmtIcone->close();
                break;
            case 'papel_fundo':
                $sqlPapel = "INSERT INTO papel_fundo_usuario (id_usuario, id_papel) VALUES (?, ?)";
                $stmtPapel = $conn->prepare($sqlPapel);
                $stmtPapel->bind_param('ii', $userId, $idItem);
                $stmtPapel->execute();
                $stmtPapel->close();
                break;
        }
    }

    // Marcar pedido como aprovado
    $conn->query("UPDATE pedidos SET status = 'enviado' WHERE id = " . intval($idPedido));

    $conn->commit();
    $_SESSION['success'] = 'Pagamento aprovado e pedido registrado!';
    header('Location: ../View/HistoricoCompras.php');
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = 'Erro ao processar pagamento PIX: ' . $e->getMessage();
    header('Location: ../View/Carrinho.php');
}
die();
