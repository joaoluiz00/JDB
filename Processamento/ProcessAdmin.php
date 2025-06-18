<?php

require_once '../Controller/ControllerAdmin.php';
require_once '../Model/BancoDeDados.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $controller = new ControllerAdmin();
    $db = BancoDeDados::getInstance('localhost', 'root', '', 'banco'); // Corrigido para usar getInstance()

    if ($_POST['action'] === 'register') {
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $senha = $_POST['senha'];

        if ($controller->registerAdmin($nome, $email, $senha)) {
            header('Location: ../View/Index.php?success=admin_registered');
        } else {
            header('Location: ../View/CadastroAdmin.php?error=registration_failed');
        }
    } elseif ($_POST['action'] === 'login') {
        $email = $_POST['email'];
        $senha = $_POST['senha'];

        if ($controller->loginAdmin($email, $senha)) {
            header('Location: ../View/HomeAdmin.php');
        } else {
            header('Location: ../View/LoginAdmin.php?error=invalid_credentials');
        }
    } elseif ($_POST['action'] === 'add_card') {
        $nome = $_POST['nome'];
        $imagem = $_POST['imagem'];
        $vida = $_POST['vida'];
        $ataque1 = $_POST['ataque1'];
        $ataque1_dano = $_POST['ataque1_dano'];
        $ataque2 = $_POST['ataque2'];
        $ataque2_dano = $_POST['ataque2_dano'];
        $esquiva_critico = $_POST['esquiva_critico'];
        $preco = $_POST['preco'];

        $conn = $db->getConnection(); // Corrigido para usar getConnection()
        $sql = "INSERT INTO cartas (nome, path, vida, ataque1, ataque1_dano, ataque2, ataque2_dano, esquiva_critico, preco) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssisssiii", $nome, $imagem, $vida, $ataque1, $ataque1_dano, $ataque2, $ataque2_dano, $esquiva_critico, $preco);

        if ($stmt->execute()) {
            header('Location: ../View/HomeAdmin.php?success=card_added');
        } else {
            header('Location: ../View/AdicionarCarta.php?error=add_failed');
        }

        $stmt->close();
    }
    // Adicionar cupom
    elseif ($_POST['action'] === 'add_cupom') {
        $codigo = $_POST['codigo'];
        $descricao = $_POST['descricao'];
        $tipo_desconto = $_POST['tipo_desconto'];
        $valor_desconto = $_POST['valor_desconto'];
        $valor_minimo = $_POST['valor_minimo'] ?? 0;
        $data_inicio = $_POST['data_inicio'];
        $data_fim = $_POST['data_fim'];
        $uso_maximo = $_POST['uso_maximo'] ?? null;
        $conn = $db->getConnection();
        $sql = "INSERT INTO cupons (codigo, descricao, tipo_desconto, valor_desconto, valor_minimo, data_inicio, data_fim, ativo, uso_maximo) VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssddssi", $codigo, $descricao, $tipo_desconto, $valor_desconto, $valor_minimo, $data_inicio, $data_fim, $uso_maximo);
        if ($stmt->execute()) {
            header('Location: ../View/CuponsAdmin.php?success=added');
        } else {
            header('Location: ../View/AdicionarCupom.php?error=add_failed');
        }
        $stmt->close();
    }
    // Editar cupom
    elseif ($_POST['action'] === 'edit_cupom') {
        $id = $_POST['id'];
        $codigo = $_POST['codigo'];
        $descricao = $_POST['descricao'];
        $tipo_desconto = $_POST['tipo_desconto'];
        $valor_desconto = $_POST['valor_desconto'];
        $valor_minimo = $_POST['valor_minimo'] ?? 0;
        $data_inicio = $_POST['data_inicio'];
        $data_fim = $_POST['data_fim'];
        $uso_maximo = $_POST['uso_maximo'] ?? null;
        $conn = $db->getConnection();
        $sql = "UPDATE cupons SET codigo=?, descricao=?, tipo_desconto=?, valor_desconto=?, valor_minimo=?, data_inicio=?, data_fim=?, uso_maximo=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssddssii", $codigo, $descricao, $tipo_desconto, $valor_desconto, $valor_minimo, $data_inicio, $data_fim, $uso_maximo, $id);
        if ($stmt->execute()) {
            header('Location: ../View/CuponsAdmin.php?success=edited');
        } else {
            header('Location: ../View/EditarCupom.php?id=' . $id . '&error=edit_failed');
        }
        $stmt->close();
    }
    // Remover cupom
    elseif ($_POST['action'] === 'delete_cupom') {
        $id = $_POST['id'];
        $conn = $db->getConnection();
        $sql = "UPDATE cupons SET ativo=0 WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            header('Location: ../View/CuponsAdmin.php?success=deleted');
        } else {
            header('Location: ../View/CuponsAdmin.php?error=delete_failed');
        }
        $stmt->close();
    }
}