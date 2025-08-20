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
        // Captura e saneamento básico dos campos obrigatórios
        $nome          = trim($_POST['nome'] ?? '');
        $imagem        = trim($_POST['imagem'] ?? '');
        // Garante prefixo padronizado do caminho da imagem
        if ($imagem !== '' && str_starts_with($imagem, '/JDB/Assets/img/') === false) {
            $imagem = '/JDB/Assets/img/' . ltrim($imagem, '/');
        }
        $vida          = (int)($_POST['vida'] ?? 0);
        $ataque1       = trim($_POST['ataque1'] ?? '');
        $ataque1_dano  = (int)($_POST['ataque1_dano'] ?? 0);
        $ataque2       = trim($_POST['ataque2'] ?? '');
        $ataque2_dano  = (int)($_POST['ataque2_dano'] ?? 0);
        $esquiva       = (int)($_POST['esquiva'] ?? 0);
        $critico       = (int)($_POST['critico'] ?? 0);
        $preco         = (int)($_POST['preco'] ?? 0); // preço em moedas (int)
        $preco_dinheiro = (float)($_POST['preco_dinheiro'] ?? 0); // preço em dinheiro (decimal)
        $cor           = trim($_POST['cor'] ?? 'neutro');

        // Validação mínima
        if ($nome === '' || $imagem === '' || $vida <= 0 || $ataque1 === '') {
            header('Location: ../View/AdicionarCarta.php?error=invalid_fields');
            exit;
        }

        $conn = $db->getConnection();
        $sql = "INSERT INTO cartas (nome, path, vida, ataque1, ataque1_dano, ataque2, ataque2_dano, esquiva, critico, preco, preco_dinheiro, cor)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            header('Location: ../View/AdicionarCarta.php?error=stmt_prepare');
            exit;
        }
        // Tipos: s s i s i s i i i i d s
        $stmt->bind_param(
            'ssisisi' . 'iiids',
            $nome,
            $imagem,
            $vida,
            $ataque1,
            $ataque1_dano,
            $ataque2,
            $ataque2_dano,
            $esquiva,
            $critico,
            $preco,
            $preco_dinheiro,
            $cor
        );

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
    // Adicionar papel de parede
    elseif ($_POST['action'] === 'add_papelparede') {
        $nome = $_POST['nome'];
        $imagem = $_POST['imagem'];
        if ($imagem !== '' && str_starts_with($imagem, '/JDB/Assets/img/') === false) {
            $imagem = '/JDB/Assets/img/' . ltrim($imagem, '/');
        }
        $preco = $_POST['preco'];
        $preco_dinheiro = $_POST['preco_dinheiro'];
        $conn = $db->getConnection();
        $sql = "INSERT INTO papel_fundo (nome, path, preco, preco_dinheiro) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdd", $nome, $imagem, $preco, $preco_dinheiro);
        if ($stmt->execute()) {
            header('Location: ../View/HomeAdmin.php?success=papel_added');
        } else {
            header('Location: ../View/AdicionarPapelParede.php?error=add_failed');
        }
        $stmt->close();
    }
}