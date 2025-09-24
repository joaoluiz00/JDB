<?php
session_start();
require_once __DIR__ . '/../Model/BancoDeDados.php';
require_once __DIR__ . '/../Model/PapelParede.php';

$backgroundUrl = '';
if (isset($_SESSION['id'])) {
    $idUsuario = $_SESSION['id'];
    $conn = BancoDeDados::getInstance()->getConnection();
    $sql = "SELECT pf.path FROM usuario u LEFT JOIN papel_fundo pf ON u.id_papel_fundo = pf.id WHERE u.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $idUsuario);
    $stmt->execute();
    $stmt->bind_result($bgPath);
    $stmt->fetch();
    $stmt->close();
    if ($bgPath) {
        $backgroundUrl = $bgPath;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Batalha de Cartas</title>
    <link rel="stylesheet" href="../Assets/style.css">
    <style>
        body.custom-bg {
            <?php if ($backgroundUrl): ?>
                background-image: url('<?= $backgroundUrl ?>');
                background-size: cover;
                background-repeat: no-repeat;
                background-attachment: fixed;
            <?php endif; ?>
        }
        
        #battle-container {
            position: relative;
            width: 800px;
            height: 600px;
            margin: 50px auto;
            border: 2px solid black;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        #game-canvas {
            display: block;
            background-color: transparent;
        }

        #ui-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 150px;
            background-color: rgba(255, 255, 255, 0.9);
            border-top: 2px solid black;
            padding: 10px;
            display: flex;
            flex-direction: column;
            justify-content: space-around;
            align-items: center;
        }

        #message-box {
            font-size: 1.2em;
            font-weight: bold;
            text-align: center;
        }

        #action-menu button {
            padding: 10px 20px;
            font-size: 1em;
            margin: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body class="<?php if ($backgroundUrl) echo 'custom-bg'; ?>">
    <div id="deck-setup" style="max-width:800px;margin:20px auto;background:#fff;padding:15px;border:1px solid #333;">
        <h2>1. Monte seu Deck (escolha até 3)</h2>
        <form id="deck-form">
            <div id="user-cards" style="display:flex;flex-wrap:wrap;gap:10px;"></div>
            <button type="submit">Confirmar Deck</button>
        </form>
    </div>

    <div id="enemy-select" style="display:none;max-width:800px;margin:20px auto;background:#fff;padding:15px;border:1px solid #333;">
        <h2>2. Escolha um Inimigo</h2>
        <p>Gere até 3 inimigos aleatórios (eles podem repetir cartas).</p>
        <button id="generate-enemies">Gerar Inimigos</button>
        <div id="enemy-list" style="display:flex;gap:15px;margin-top:10px;"></div>
    </div>

    <div id="battle-container" style="display:none;">
        <canvas id="game-canvas" width="800" height="600"></canvas>
        <div id="ui-overlay">
            <div id="info-bars" style="width: 100%; display: flex; justify-content: space-between; padding: 0 20px;">
                <div>
                    <img id="player-card-img" style="width: 100px; height: 100px;" src="" alt="Carta Ativa">
                    <div class="life-bar-container"><div id="player-life-bar" class="life-bar"></div></div>
                    <div id="switcher" style="margin-top:5px;display:flex;gap:5px;"></div>
                </div>
                <div>
                    <img id="opponent-card-img" style="width: 100px; height: 100px;" src="" alt="Carta do Inimigo">
                    <div class="life-bar-container"><div id="opponent-life-bar" class="life-bar"></div></div>
                </div>
            </div>
            <div id="message-box">Aguarde...</div>
            <div id="action-menu">
                <button id="attack1-btn" disabled>Ataque 1</button>
                <button id="attack2-btn" disabled>Ataque 2</button>
            </div>
            <button id="reset-battle" style="margin-top:8px;">Resetar</button>
        </div>
    </div>

    <script src="../Assets/js/battle.js"></script>
</body>
</html>