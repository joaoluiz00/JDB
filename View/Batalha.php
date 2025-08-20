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
    <div id="battle-container">
        <canvas id="game-canvas" width="800" height="600"></canvas>
        <div id="ui-overlay">
            <div id="info-bars" style="width: 100%; display: flex; justify-content: space-between; padding: 0 20px;">
                <div>
                    <img id="player-card-img" style="width: 100px; height: 100px;" src="" alt="Sua Carta">
                    <div class="life-bar-container">
                        <div id="player-life-bar" class="life-bar"></div>
                    </div>
                </div>
                <div>
                    <img id="opponent-card-img" style="width: 100px; height: 100px;" src="" alt="Carta do Oponente">
                    <div class="life-bar-container">
                        <div id="opponent-life-bar" class="life-bar"></div>
                    </div>
                </div>
            </div>
            <div id="message-box">Aguarde...</div>
            <div id="action-menu">
                <button id="attack1-btn">Ataque 1</button>
                <button id="attack2-btn">Ataque 2</button>
            </div>
        </div>
    </div>
    
    <script src="../Assets/js/battle.js"></script>
</body>
</html>