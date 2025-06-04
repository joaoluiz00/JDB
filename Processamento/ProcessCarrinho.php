<?php
require_once "../Controller/ControllerCarrinho.php";
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../View/index.php");
    die();
}

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $idUsuario = $_SESSION['id'];
    $carrinhoController = new ControllerCarrinho();

    switch ($action) {
        case 'adicionar':
            if (isset($_POST['tipo_item'], $_POST['id_item'], $_POST['preco_unitario'])) {
                $tipoItem = $_POST['tipo_item'];
                $idItem = $_POST['id_item'];
                $precoUnitario = $_POST['preco_unitario'];
                $precoMoedas = $_POST['preco_moedas'] ?? 0;
                $quantidade = $_POST['quantidade'] ?? 1;

                $resultado = $carrinhoController->adicionarItem($idUsuario, $tipoItem, $idItem, $precoUnitario, $precoMoedas, $quantidade);
                
                if ($resultado === true) {
                    $_SESSION['success'] = "Item adicionado ao carrinho!";
                } elseif ($resultado === "already_owned") {
                    $_SESSION['error'] = "Você já possui este ícone!";
                } elseif ($resultado === "already_in_cart") {
                    $_SESSION['error'] = "Este ícone já está no seu carrinho!";
                } else {
                    $_SESSION['error'] = "Erro ao adicionar item ao carrinho.";
                }
            } else {
                $_SESSION['error'] = "Dados incompletos para adicionar item.";
            }
            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../View/Loja.php'));
            break;

        case 'alterar_pagamento':
            if (isset($_POST['id_carrinho'], $_POST['tipo_pagamento'])) {
                $idCarrinho = $_POST['id_carrinho'];
                $tipoPagamento = $_POST['tipo_pagamento'];
                $resultado = $carrinhoController->alterarTipoPagamento($idUsuario, $idCarrinho, $tipoPagamento);
                
                if ($resultado) {
                    $_SESSION['success'] = "Tipo de pagamento atualizado!";
                } else {
                    $_SESSION['error'] = "Erro ao atualizar tipo de pagamento.";
                }
            } else {
                $_SESSION['error'] = "Dados incompletos para alterar pagamento.";
            }
            header("Location: ../View/Carrinho.php");
            break;

        case 'atualizar_quantidade':
            if (isset($_POST['id_carrinho'], $_POST['quantidade'])) {
                $idCarrinho = $_POST['id_carrinho'];
                $quantidade = intval($_POST['quantidade']);
                $resultado = $carrinhoController->atualizarQuantidade($idUsuario, $idCarrinho, $quantidade);
                
                if ($resultado === true) {
                    $_SESSION['success'] = "Quantidade atualizada!";
                } elseif ($resultado === "icon_quantity_limit") {
                    $_SESSION['error'] = "Ícones só podem ter quantidade 1!";
                } else {
                    $_SESSION['error'] = "Erro ao atualizar quantidade.";
                }
            } else {
                $_SESSION['error'] = "Dados incompletos para atualizar quantidade.";
            }
            header("Location: ../View/Carrinho.php");
            break;

        case 'remover':
            if (isset($_POST['id_carrinho'])) {
                $idCarrinho = $_POST['id_carrinho'];
                $resultado = $carrinhoController->removerItem($idUsuario, $idCarrinho);
                
                if ($resultado) {
                    $_SESSION['success'] = "Item removido do carrinho!";
                } else {
                    $_SESSION['error'] = "Erro ao remover item do carrinho.";
                }
            } else {
                $_SESSION['error'] = "ID do item não informado.";
            }
            header("Location: ../View/Carrinho.php");
            break;

        case 'limpar':
            $resultado = $carrinhoController->limparCarrinho($idUsuario);
            if ($resultado) {
                $_SESSION['success'] = "Carrinho limpo!";
                // Limpar também o cupom aplicado
                unset($_SESSION['cupom_aplicado']);
                unset($_SESSION['desconto']);
            } else {
                $_SESSION['error'] = "Erro ao limpar carrinho.";
            }
            header("Location: ../View/Carrinho.php");
            break;
            
        default:
            $_SESSION['error'] = "Ação não reconhecida.";
            header("Location: ../View/Carrinho.php");
            break;
    }
    die();
} else {
    $_SESSION['error'] = "Nenhuma ação especificada.";
    header("Location: ../View/Carrinho.php");
    die();
}
?>