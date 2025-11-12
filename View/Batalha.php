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
    <link rel="stylesheet" href="../Assets/batalha.css">
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
            width: 100%;
            max-width: 980px;
            margin: 30px auto;
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
            position: static;
            width: 100%;
            background-color: rgba(255, 255, 255, 0.95);
            border-top: 2px solid black;
            padding: 12px 10px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            align-items: center;
        }

        #message-box {
            font-size: 1.2em;
            font-weight: bold;
            text-align: center;
        }

    .life-bar-container { width: 160px; height: 12px; border: 1px solid #000; background:#ddd; margin-top:4px; border-radius:6px; overflow:hidden }
    .life-bar { height: 100%; width: 100%; background: linear-gradient(90deg, #2ecc71, #27ae60); transition: width 0.3s ease; }

            /* Animações simples */
            @keyframes hitShake { 0%{transform:translateX(0)} 25%{transform:translateX(-6px)} 50%{transform:translateX(6px)} 75%{transform:translateX(-3px)} 100%{transform:translateX(0)} }
            @keyframes punch { 0%{transform:translateX(0)} 50%{transform:translateX(12px) scale(1.05)} 100%{transform:translateX(0)} }
            .shake { animation: hitShake .100s ease; }
            .punch { animation: punch .100s ease; }

        #action-menu button {
            padding: 10px 20px;
            font-size: 1em;
            margin: 5px;
            cursor: pointer;
        }

        /* Modal de fim de batalha */
        #end-modal {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.6);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        #end-modal .panel {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            width: 90%;
            max-width: 520px;
            box-shadow: 0 10px 30px rgba(0,0,0,.3);
            text-align: center;
        }
        #end-modal h3 { margin: 0 0 10px 0; }
        #end-modal .cta { display:flex; gap:8px; justify-content:center; flex-wrap:wrap; margin-top: 10px; }
        #end-modal .cta button, #end-modal .cta a {
            padding: 10px 14px; border-radius: 6px; border:1px solid #333; background:#111; color:#fff; cursor:pointer; text-decoration:none
        }
    </style>
</head>
<body class="<?php if ($backgroundUrl) echo 'custom-bg'; ?>">
    <!-- Passo 1: Montagem do deck do jogador -->
    <div id="deck-setup" style="max-width:980px;margin:20px auto;background:#fff;padding:15px;border:1px solid #333;border-radius:8px;box-shadow:0 4px 10px rgba(0,0,0,.15)">
        <h2>1. Monte seu Deck (escolha até 3)</h2>
        <form id="deck-form">
            <div id="user-cards" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(160px,1fr));gap:12px"></div>
            <div style="display:flex;justify-content:space-between;margin-top:10px;gap:10px">
                <a href="Home.php" class="btn-voltar" id="voltar-step1">Voltar</a>
                <div style="text-align:right"><button type="submit" class="btn-primary">Confirmar Deck</button></div>
            </div>
        </form>
    </div>

    <!-- Passo 2: Seleção de inimigos por estágio; gating baseado em progresso salvo -->
    <div id="enemy-select" style="display:none;max-width:980px;margin:20px auto;background:#fff;padding:15px;border:1px solid #333;border-radius:8px;box-shadow:0 4px 10px rgba(0,0,0,.15)">
        <h2>2. Progresso de Inimigos</h2>
        <p>Derrote o Inimigo 1 para desbloquear o 2, e assim por diante. As cartas deles são surpresa.</p>
        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px">
            <button type="button" class="btn-voltar" id="voltar-to-step1">Voltar</button>
            <button id="generate-enemies" class="btn-primary">Ver Inimigos</button>
        </div>
        <div id="enemy-list" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(220px,1fr));gap:15px;margin-top:10px;"></div>
    </div>

    <!-- Passo 3: Arena de batalha com HUD do inimigo no topo e overlay de UI na base -->
    <div id="battle-container" style="display:none;max-width:980px;margin:20px auto;border:1px solid #333;border-radius:8px;overflow:hidden;box-shadow:0 4px 10px rgba(0,0,0,.15);background:rgba(0,0,0,.5)">
        <!-- HUD do inimigo no topo da arena -->
        <div id="enemy-hud" style="position:absolute; top:10px; right:10px; z-index:10; background:rgba(0,0,0,.55); color:#fff; padding:10px; border-radius:10px; display:flex; gap:10px; align-items:center;">
            <img id="enemy-hud-img" src="" alt="Carta Inimiga" style="width:140px;height:140px;border-radius:6px;border:1px solid #222;object-fit:cover;background:#111">
            <div>
                <div id="enemy-hud-name" style="font-weight:600;line-height:1.2">&nbsp;</div>
                <div id="enemy-hud-count" style="opacity:.85;font-size:.9em;margin-bottom:6px">&nbsp;</div>
                <div class="life-bar-container" style="width:220px;background:#333;border-color:#555"><div id="enemy-hud-life" class="life-bar" style="background:linear-gradient(90deg,#e74c3c,#c0392b)"></div></div>
            </div>
        </div>
        <canvas id="game-canvas" width="980" height="540" style="display:block; background-image:url('<?= $backgroundUrl ?: "/JDB/Assets/img/gerencia.jpg" ?>'); background-size:cover; background-position:center; background-repeat:no-repeat"></canvas>
        <div id="ui-overlay">
            <div id="info-bars" style="width:100%;display:grid;grid-template-columns:1fr 160px;gap:10px;align-items:center;padding:0 20px;">
                <div>
                    <div style="display:flex;gap:10px;align-items:center">
                        <img id="player-card-img" style="width: 150px; height: 150px; object-fit:cover;border-radius:8px;border:1px solid #222;background:#fff" src="" alt="Carta Ativa">
                        <div>
                            <div class="life-bar-container"><div id="player-life-bar" class="life-bar"></div></div>
                            <div id="switcher" style="margin-top:8px;display:flex;gap:6px;flex-wrap:wrap;"></div>
                        </div>
                    </div>
                </div>
                <div style="text-align:right;min-width:120px">
                    <div><strong>Moedas:</strong> <span id="coin-balance">...</span></div>
                </div>
            </div>
            <div id="message-box" style="margin-top:8px">Aguarde...</div>
            <div id="enemy-switcher" style="margin-top:6px;display:flex;gap:6px;flex-wrap:wrap;justify-content:flex-end;width:100%;padding:0 20px"></div>
            <div id="action-menu" style="margin-top:8px">
                <button id="attack1-btn" disabled class="btn-primary">Ataque 1</button>
                <button id="attack2-btn" disabled class="btn-primary">Ataque 2</button>
                <button id="reset-battle" class="btn-voltar" style="margin-left:8px">Fugir</button>
            </div>
        </div>
    </div>

    <!-- Modal de fim de batalha: ações de continuar jogando, voltar à seleção ou sair -->
    <div id="end-modal">
        <div class="panel">
            <h3 id="end-title">Fim da Batalha</h3>
            <p id="end-sub">Resultado</p>
            <div class="cta">
                <button id="play-again" class="btn-primary">Jogar Novamente</button>
                <button id="back-to-select" class="btn-voltar">Voltar à seleção de inimigos</button>
                <a href="Loja.php" class="btn-voltar">Ir para a Loja</a>
                <a href="Home.php" class="btn-voltar">Voltar ao Início</a>
            </div>
        </div>
    </div>

    <script src="../Assets/js/battle.js"></script>
    <script>
    // Tornar o botão "Voltar" do passo 2 idempotente: volta para o passo 1 sem navegar
    document.addEventListener('DOMContentLoaded', function(){
        var backBtn = document.getElementById('voltar-to-step1');
        if(backBtn){
            backBtn.addEventListener('click', function(e){
                e.preventDefault();
                var deck = document.getElementById('deck-setup');
                var enemy = document.getElementById('enemy-select');
                if(deck && enemy){
                    deck.style.display = 'block';
                    enemy.style.display = 'none';
                    // rolar suavemente para o passo 1
                    setTimeout(function(){
                        var y = deck.getBoundingClientRect().top + window.scrollY - 20;
                        window.scrollTo({top: y, behavior: 'smooth'});
                    }, 50);
                }
            });
        }
    });
    </script>
</body>
</html>