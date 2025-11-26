<?php
function adicionarItemPedido(mysqli $conn, int $idPedido, string $tipoItem, int $idItem, int $quantidade, float $preco): void
{
    $sqlItem = "INSERT INTO pedido_itens (id_pedido, tipo_item, id_item, quantidade, preco_unitario) VALUES (?, ?, ?, ?, ?)";
    $stmtItem = $conn->prepare($sqlItem);
    $stmtItem->bind_param('isiid', $idPedido, $tipoItem, $idItem, $quantidade, $preco);
    $stmtItem->execute();
    $stmtItem->close();
}

function concederItemAoUsuario(mysqli $conn, int $userId, string $tipoItem, int $idItem, int $quantidade): void
{
    switch ($tipoItem) {
        case 'carta':
            for ($i = 0; $i < $quantidade; $i++) {
                $sqlCarta = "INSERT INTO cartas_usuario (id_usuario, id_carta) VALUES (?, ?)";
                $stmtCarta = $conn->prepare($sqlCarta);
                $stmtCarta->bind_param('ii', $userId, $idItem);
                $stmtCarta->execute();
                $stmtCarta->close();
            }
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
