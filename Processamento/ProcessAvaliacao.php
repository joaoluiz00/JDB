<?php
session_start();
require_once __DIR__ . '/../Controller/ControllerAvaliacao.php';
require_once __DIR__ . '/../Model/Avaliacao.php';
require_once __DIR__ . '/../Service/NotificationService.php';
require_once __DIR__ . '/../Controller/ControllerCartas.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

$controller = new ControllerAvaliacao();
$idUsuario = $_SESSION['id'];

// Valida os dados recebidos
$tipoItem = $_POST['tipo_item'] ?? null;
$idItem = $_POST['id_item'] ?? null;
$nota = $_POST['nota'] ?? null;
$comentario = $_POST['comentario'] ?? null;

// Validações
if (!$tipoItem || !$idItem || !$nota || !$comentario) {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

if (!in_array($tipoItem, ['carta', 'pacote', 'icone', 'papel_fundo'])) {
    echo json_encode(['success' => false, 'message' => 'Tipo de item inválido']);
    exit;
}

if ($nota < 1 || $nota > 5) {
    echo json_encode(['success' => false, 'message' => 'Nota deve ser entre 1 e 5']);
    exit;
}

if (strlen($comentario) < 10) {
    echo json_encode(['success' => false, 'message' => 'Comentário deve ter pelo menos 10 caracteres']);
    exit;
}

// Verifica se o usuário comprou o produto
if (!$controller->usuarioComprouProduto($idUsuario, $tipoItem, $idItem)) {
    echo json_encode(['success' => false, 'message' => 'Você precisa comprar o produto antes de avaliá-lo']);
    exit;
}

// Verifica se o usuário já avaliou
if ($controller->usuarioJaAvaliou($idUsuario, $tipoItem, $idItem)) {
    echo json_encode(['success' => false, 'message' => 'Você já avaliou este produto']);
    exit;
}

// Cria a avaliação
$avaliacao = new Avaliacao(
    null,
    $idUsuario,
    $tipoItem,
    $idItem,
    $nota,
    $comentario
);

$idAvaliacao = $controller->criarAvaliacao($avaliacao);

if (!$idAvaliacao) {
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar avaliação']);
    exit;
}

// Processa upload de imagens (opcional)
$imagensEnviadas = [];
if (isset($_FILES['imagens'])) {
    $uploadDir = __DIR__ . '/../Assets/img/avaliacoes/';
    
    // Cria diretório se não existir
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $totalImagens = count($_FILES['imagens']['name']);
    $maxImagens = 5; // Limite de 5 imagens por avaliação

    for ($i = 0; $i < min($totalImagens, $maxImagens); $i++) {
        if ($_FILES['imagens']['error'][$i] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['imagens']['tmp_name'][$i];
            $originalName = $_FILES['imagens']['name'][$i];
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            
            // Valida extensão
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($extension, $allowedExtensions)) {
                continue;
            }

            // Valida tamanho (max 5MB)
            if ($_FILES['imagens']['size'][$i] > 5242880) {
                continue;
            }

            // Gera nome único
            $newName = 'avaliacao_' . $idAvaliacao . '_' . uniqid() . '.' . $extension;
            $uploadPath = $uploadDir . $newName;
            $dbPath = '/JDB/Assets/img/avaliacoes/' . $newName;

            if (move_uploaded_file($tmpName, $uploadPath)) {
                // Salva no banco de dados
                if ($controller->adicionarImagem($idAvaliacao, $dbPath)) {
                    $imagensEnviadas[] = $dbPath;
                }
            }
        }
    }
}

// NOTIFICAÇÃO: Dispara evento de avaliação aprovada
$notificationService = NotificationService::getInstance();

// Busca nome do item para notificação
$nomeItem = 'Produto';
if ($tipoItem === 'carta') {
    $controllerCarta = new ControllerCartas();
    $carta = $controllerCarta->buscarCartaPorId($idItem);
    if ($carta) {
        $nomeItem = $carta->getNome();
    }
}

$notificationService->notificarAvaliacaoAprovada(
    $idUsuario,
    $_SESSION['nome'] ?? 'Usuário',
    $tipoItem,
    $idItem,
    $nomeItem
);

echo json_encode([
    'success' => true, 
    'message' => 'Avaliação enviada com sucesso!',
    'id_avaliacao' => $idAvaliacao,
    'total_imagens' => count($imagensEnviadas)
]);
