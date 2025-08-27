<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../View/index.php");
    die();
}

require_once '../Controller/ControllerCarrinho.php';
require_once '../Controller/ControllerUsuario.php';
require_once '../Controller/ControllerCartas.php';
require_once '../Controller/ControllerIcone.php';
require_once '../Model/BancoDeDados.php';

$userId = $_SESSION['id'];
$userController = new ControllerUsuario();
$carrinhoController = new ControllerCarrinho();
$cartasController = new ControllerCartas();
$iconeController = new ControllerIcone();

$user = $userController->readUser($userId);

if (isset($_POST['action'])) {
    $action = $_POST['action'];

    try {
        switch ($action) {
            case 'comprar_carrinho_moedas':
                // Obter itens do carrinho que são pagos com moedas
                $itensCarrinho = $carrinhoController->getItensCarrinho($userId);
                $totalMoedas = $carrinhoController->calcularTotal($userId, 'moedas');
                
                if ($totalMoedas <= 0) {
                    throw new Exception("Nenhum item para comprar com moedas!");
                }
                
                if ($user->getCoin() < $totalMoedas) {
                    throw new Exception("Moedas insuficientes! Você tem " . $user->getCoin() . " moedas, mas precisa de " . $totalMoedas . " moedas.");
                }
                
                // Conectar ao banco para operações diretas
                $conn = BancoDeDados::getInstance('localhost', 'root', '', 'banco')->getConnection();
                
                // Iniciar transação para garantir consistência
                $conn->begin_transaction();
                
                try {
                    // Processar cada item do carrinho que é pago com moedas
                    $itensProcessados = [];
                    $itensCarrinho->data_seek(0); // Reset do ponteiro
                    
                    while ($item = $itensCarrinho->fetch_assoc()) {
                        if ($item['tipo_pagamento'] === 'moedas') {
                            $sucesso = false;
                            
                            switch ($item['tipo_item']) {
                                case 'carta':
                                    // Adicionar cada quantidade da carta diretamente no banco
                                    for ($i = 0; $i < $item['quantidade']; $i++) {
                                        $sqlCarta = "INSERT INTO cartas_usuario (id_usuario, id_carta) VALUES (?, ?)";
                                        $stmtCarta = $conn->prepare($sqlCarta);
                                        $stmtCarta->bind_param("ii", $userId, $item['id_item']);
                                        $sucesso = $stmtCarta->execute();
                                        $stmtCarta->close();
                                        
                                        if (!$sucesso) {
                                            throw new Exception("Erro ao adicionar carta ao inventário");
                                        }
                                    }
                                    break;
                                    
                                case 'icone':
                                    // Verificar se já possui o ícone
                                    $sqlVerificar = "SELECT id FROM icones_usuario WHERE id_usuario = ? AND id_icone = ?";
                                    $stmtVerificar = $conn->prepare($sqlVerificar);
                                    $stmtVerificar->bind_param("ii", $userId, $item['id_item']);
                                    $stmtVerificar->execute();
                                    $resultVerificar = $stmtVerificar->get_result();
                                    $stmtVerificar->close();
                                    
                                    if ($resultVerificar->num_rows > 0) {
                                        throw new Exception("Você já possui este ícone: " . $item['nome']);
                                    }
                                    
                                    // Adicionar ícone ao inventário
                                    $sqlIcone = "INSERT INTO icones_usuario (id_usuario, id_icone) VALUES (?, ?)";
                                    $stmtIcone = $conn->prepare($sqlIcone);
                                    $stmtIcone->bind_param("ii", $userId, $item['id_item']);
                                    $sucesso = $stmtIcone->execute();
                                    $stmtIcone->close();
                                    
                                    if (!$sucesso) {
                                        throw new Exception("Erro ao adicionar ícone ao inventário");
                                    }
                                    break;
                                    
                                case 'pacote':
                                    // Implementar lógica para pacotes
                                    throw new Exception("Compra de pacotes com moedas ainda não implementada");
                                    break;
                                    
                                default:
                                    throw new Exception("Tipo de item não suportado: " . $item['tipo_item']);
                            }
                            
                            if ($sucesso) {
                                $itensProcessados[] = $item['nome'] . " (x" . $item['quantidade'] . ")";
                                // Registrar histórico deste item já processado
                                $valor = $item['preco_moedas'] * $item['quantidade'];
                                $sqlHist = "INSERT INTO historico_transacoes (id_usuario, tipo_transacao, id_item, valor, metodo_pagamento, data_transacao) VALUES (?, ?, ?, ?, 'moedas', NOW())";
                                $stmtHist = $conn->prepare($sqlHist);
                                $stmtHist->bind_param("isid", $userId, $item['tipo_item'], $item['id_item'], $valor);
                                $stmtHist->execute();
                                $stmtHist->close();
                                
                                // Remover o item do carrinho
                                $sqlRemover = "DELETE FROM carrinho WHERE id = ? AND id_usuario = ?";
                                $stmtRemover = $conn->prepare($sqlRemover);
                                $stmtRemover->bind_param("ii", $item['id'], $userId);
                                $stmtRemover->execute();
                                $stmtRemover->close();
                            }
                        }
                    }
                    
                    if (empty($itensProcessados)) {
                        throw new Exception("Nenhum item foi processado!");
                    }
                    
                    // Debitar as moedas do usuário
                    $novoSaldo = $user->getCoin() - $totalMoedas;
                    $sqlUpdate = "UPDATE usuario SET coin = ? WHERE id = ?";
                    $stmtUpdate = $conn->prepare($sqlUpdate);
                    $stmtUpdate->bind_param("ii", $novoSaldo, $userId);
                    $updateResult = $stmtUpdate->execute();
                    $stmtUpdate->close();
                    
                    if (!$updateResult) {
                        throw new Exception("Erro ao debitar moedas do usuário!");
                    }
                    
                    // Confirmar transação
                    $conn->commit();
                    
                    $_SESSION['success'] = "Compra realizada com sucesso! Itens adquiridos: " . implode(", ", $itensProcessados) . ". Moedas gastas: " . $totalMoedas;
                    header("Location: ../View/Loja.php");
                    
                } catch (Exception $e) {
                    // Desfazer transação em caso de erro
                    $conn->rollback();
                    throw $e;
                }
                break;
                
            case 'comprar_item_moedas':
                // Para compra individual de item com moedas
                $tipoItem = $_POST['tipo_item'] ?? '';
                $idItem = $_POST['id_item'] ?? 0;
                $precoMoedas = $_POST['preco_moedas'] ?? 0;
                
                if (!$tipoItem || !$idItem || !$precoMoedas) {
                    throw new Exception("Dados incompletos para a compra!");
                }
                
                if ($user->getCoin() < $precoMoedas) {
                    throw new Exception("Moedas insuficientes! Você tem " . $user->getCoin() . " moedas, mas precisa de " . $precoMoedas . " moedas.");
                }
                
                // Conectar ao banco para operações diretas
                $conn = BancoDeDados::getInstance('localhost', 'root', '', 'banco')->getConnection();
                
                // Iniciar transação
                $conn->begin_transaction();
                
                try {
                    $sucesso = false;
                    $nomeItem = "Item";
                    
                    switch ($tipoItem) {
                        case 'carta':
                            // Buscar nome da carta
                            $sqlNome = "SELECT nome FROM cartas WHERE id = ?";
                            $stmtNome = $conn->prepare($sqlNome);
                            $stmtNome->bind_param("i", $idItem);
                            $stmtNome->execute();
                            $result = $stmtNome->get_result();
                            if ($row = $result->fetch_assoc()) {
                                $nomeItem = $row['nome'];
                            }
                            $stmtNome->close();
                            
                            // Adicionar carta ao inventário
                            $sqlCarta = "INSERT INTO cartas_usuario (id_usuario, id_carta) VALUES (?, ?)";
                            $stmtCarta = $conn->prepare($sqlCarta);
                            $stmtCarta->bind_param("ii", $userId, $idItem);
                            $sucesso = $stmtCarta->execute();
                            $stmtCarta->close();
                            break;
                            
                        case 'icone':
                            // Verificar se já possui o ícone
                            $sqlVerificar = "SELECT id FROM icones_usuario WHERE id_usuario = ? AND id_icone = ?";
                            $stmtVerificar = $conn->prepare($sqlVerificar);
                            $stmtVerificar->bind_param("ii", $userId, $idItem);
                            $stmtVerificar->execute();
                            $resultVerificar = $stmtVerificar->get_result();
                            $stmtVerificar->close();
                            
                            if ($resultVerificar->num_rows > 0) {
                                throw new Exception("Você já possui este ícone!");
                            }
                            
                            // Buscar nome do ícone
                            $sqlNome = "SELECT nome FROM img_perfil WHERE id = ?";
                            $stmtNome = $conn->prepare($sqlNome);
                            $stmtNome->bind_param("i", $idItem);
                            $stmtNome->execute();
                            $result = $stmtNome->get_result();
                            if ($row = $result->fetch_assoc()) {
                                $nomeItem = $row['nome'];
                            }
                            $stmtNome->close();
                            
                            // Adicionar ícone ao inventário
                            $sqlIcone = "INSERT INTO icones_usuario (id_usuario, id_icone) VALUES (?, ?)";
                            $stmtIcone = $conn->prepare($sqlIcone);
                            $stmtIcone->bind_param("ii", $userId, $idItem);
                            $sucesso = $stmtIcone->execute();
                            $stmtIcone->close();
                            break;
                            
                        default:
                            throw new Exception("Tipo de item não suportado!");
                    }
                    
                    if (!$sucesso) {
                        throw new Exception("Erro ao processar a compra do item!");
                    }
                    
                    // Debitar as moedas do usuário
                    $novoSaldo = $user->getCoin() - $precoMoedas;
                    $sqlUpdate = "UPDATE usuario SET coin = ? WHERE id = ?";
                    $stmtUpdate = $conn->prepare($sqlUpdate);
                    $stmtUpdate->bind_param("ii", $novoSaldo, $userId);
                    $updateResult = $stmtUpdate->execute();
                    $stmtUpdate->close();
                    
                    if (!$updateResult) {
                        throw new Exception("Erro ao debitar moedas do usuário!");
                    }
                    
                    // Registrar histórico
                    $sqlHist = "INSERT INTO historico_transacoes (id_usuario, tipo_transacao, id_item, valor, metodo_pagamento, data_transacao) VALUES (?, ?, ?, ?, 'moedas', NOW())";
                    $stmtHist = $conn->prepare($sqlHist);
                    $stmtHist->bind_param("isid", $userId, $tipoItem, $idItem, $precoMoedas);
                    $stmtHist->execute();
                    $stmtHist->close();

                    // Confirmar transação
                    $conn->commit();
                    
                    $_SESSION['success'] = "Compra realizada com sucesso! Você adquiriu: " . $nomeItem . ". Moedas gastas: " . $precoMoedas;
                    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../View/Home.php'));
                    
                } catch (Exception $e) {
                    // Desfazer transação em caso de erro
                    $conn->rollback();
                    throw $e;
                }
                break;
                
            default:
                throw new Exception("Ação não reconhecida!");
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../View/Carrinho.php'));
    }
    
} else {
    $_SESSION['error'] = "Nenhuma ação especificada!";
    header("Location: ../View/Carrinho.php");
}

die();
?>