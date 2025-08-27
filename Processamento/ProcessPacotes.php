<?php
// Inicia a sessão
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['id'])) {
    header("Location: ../View/index.php");
    die();
}

require_once '../Model/BancoDeDados.php';

// Definir o ID do usuário logo no início do script
$idUsuario = $_SESSION['id'];

if (isset($_POST['action']) && $_POST['action'] === 'comprar_moedas') {
    $idPacote = $_POST['id_pacote'];
    $preco = $_POST['preco'];
    $cor = isset($_POST['cor']) ? $_POST['cor'] : 'todos';
    
    // Obtém a instância do banco de dados
    $db = BancoDeDados::getInstance();
    
    // Verifica se o usuário tem moedas suficientes
    $user = $db->getUserById($idUsuario);
    
    if ($user && $user['coin'] >= $preco) {
        // Deduz o preço do pacote
        $conn = $db->getConnection();
        $sql = "UPDATE usuario SET coin = coin - ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $preco, $idUsuario);
        $stmt->execute();
        $stmt->close();
        
        // Sorteia 1 carta aleatória da cor especificada
        $resultado = $db->sortearCartasAleatoriasPorCor($idUsuario, $cor, 1);

        // Registrar histórico da compra com moedas (tipo 'pacote')
        $conn->query(sprintf(
            "INSERT INTO historico_transacoes (id_usuario, tipo_transacao, id_item, valor, metodo_pagamento, data_transacao) VALUES (%d, 'pacote', %d, %d, 'moedas', NOW())",
            intval($idUsuario), intval($idPacote), intval($preco)
        ));

    if ($resultado) {
            $_SESSION['success'] = "Pacote comprado com sucesso! Uma carta foi adicionada ao seu inventário.";
            header("Location: ../View/LojaPacote.php?success=1");
        } else {
            $_SESSION['error'] = "Erro ao processar a compra do pacote.";
            header("Location: ../View/LojaPacote.php?error=1");
        }
    } else {
        $_SESSION['error'] = "Moedas insuficientes para comprar este pacote.";
        header("Location: ../View/LojaPacote.php?error=1");
    }
}
// Similar update no bloco de pagamento com dinheiro real
elseif (isset($_POST['id_pacote']) && isset($_POST['preco_dinheiro'])) {
    $idPacote = $_POST['id_pacote'];
    $precoDinheiro = $_POST['preco_dinheiro'];
    $pagamento = $_POST['pagamento'];
    $cor = isset($_POST['cor']) ? $_POST['cor'] : 'todos';

    $db = BancoDeDados::getInstance();

    // Registra dados do cartão se for pagamento por cartão
    if ($pagamento === 'cartao') {
        $numero = $_POST['numero'];
        $portador = $_POST['portador'];
        $validade = $_POST['validade'];
        $cvv = $_POST['cvv'];

        // Salvar os dados do cartão (implementar este método no BancoDeDados)
        $db->salvarCartao($idUsuario, $numero, $portador, $validade, $cvv);
    }

    // Registrar a transação no histórico (opcional)
    $db->registrarTransacaoPacote($idUsuario, $idPacote, $precoDinheiro, $pagamento);

    // Sorteia 3 cartas aleatórias da cor especificada
    $resultado = $db->sortearCartasAleatoriasPorCor($idUsuario, $cor, 1);
    
    if ($resultado) {
        $_SESSION['success'] = "Pacote comprado com sucesso! 1 carta foi adicionada ao seu inventário.";
        header("Location: ../View/LojaPacote.php?success=1");
    } else {
        $_SESSION['error'] = "Erro ao processar a compra do pacote.";
        header("Location: ../View/LojaPacote.php?error=1");
    }
} else {
    // Alguma ação inválida ou não implementada
    $_SESSION['error'] = "Ação inválida.";
    header("Location: ../View/LojaPacote.php");
    die();
}