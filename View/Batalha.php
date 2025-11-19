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
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            height: 100vh;
            margin: 0;
            border: none;
            background: transparent;
            display: flex;
            flex-direction: column;
            z-index: 100;
            overflow: hidden;
            box-sizing: border-box;
        }

        #game-canvas {
            display: block;
            background-color: #1a1a2e;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 0;
            width: 100%;
            height: 100%;
            flex-shrink: 0;
        }

        #ui-overlay {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            max-width: 100%;
            background-color: rgba(0, 0, 0, 0.85);
            border-top: 2px solid #444;
            padding: 10px 8px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            align-items: center;
            z-index: 2;
            box-sizing: border-box;
        }

        #message-box {
            font-size: 1.2em;
            font-weight: bold;
            text-align: center;
        }

        #enemy-hud {
            background: rgba(0,0,0,.55);
            color: #fff;
            padding: 6px;
            border-radius: 6px;
            display: flex;
            gap: 6px;
            align-items: center;
            position: absolute;
            top: 6px;
            right: 6px;
            z-index: 10;
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
    <div id="deck-setup" style="max-width:900px;margin:15px auto;background:linear-gradient(135deg, #fff 0%, #f8f8f8 100%);padding:14px;border:2px solid #ddd;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.15);display:block">
        <h2 style="color:#2c3e50;margin-bottom:12px;font-size:1.2em;text-transform:uppercase;letter-spacing:0.3px">Monte seu Deck (até 3)</h2>
        <form id="deck-form">
            <div id="user-cards" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(140px,1fr));gap:10px;margin-bottom:14px"></div>
            <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;padding-top:10px;border-top:1px solid #ddd">
                <a href="Home.php" class="btn-voltar" id="voltar-step1">Voltar</a>
                <button type="submit" class="btn-primary">Confirmar</button>
            </div>
        </form>
    </div>

    <!-- Passo 2: Seleção de inimigos por estágio; gating baseado em progresso salvo -->
    <div id="enemy-select" style="display:none;max-width:900px;margin:15px auto;background:linear-gradient(135deg, #fff 0%, #f8f8f8 100%);padding:14px;border:2px solid #ddd;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.15)">
        <h2 style="color:#2c3e50;margin-bottom:8px;font-size:1.2em;text-transform:uppercase;letter-spacing:0.3px">Escolha seu Inimigo</h2>
        <p style="color:#555;margin-bottom:12px;font-size:13px;line-height:1.4">Derrote o Inimigo 1 para desbloquear o 2. As cartas deles são surpresa!</p>
        <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:12px;padding-bottom:10px;border-bottom:1px solid #ddd">
            <button type="button" class="btn-voltar" id="voltar-to-step1">Voltar</button>
            <button id="generate-enemies" class="btn-primary">Ver Inimigos</button>
        </div>
        <div id="enemy-list" style="display:grid;grid-template-columns:repeat(auto-fill, minmax:200px,1fr));gap:12px;margin-top:0"></div>
    </div>

    <!-- Passo 3: Arena de batalha -->
    <div id="battle-container" style="display:none;">
        <!-- HUD do inimigo no topo da arena - estilo Pokémon -->
        <div id="enemy-hud" style="position:absolute; top:6px; right:6px; z-index:10; background:rgba(0,0,0,.55); color:#fff; padding:6px; border-radius:6px; display:flex; gap:6px; align-items:flex-start;">
            <div style="flex-shrink:0">
                <img id="enemy-hud-img" src="" alt="Carta Inimiga" style="width:110px;height:120px;border-radius:4px;border:2px solid rgba(255,215,0,0.6);object-fit:cover;background:#111">
            </div>
            <div style="flex-grow:1">
                <div id="enemy-hud-name" style="font-weight:700;line-height:1.1;font-size:11px;margin-bottom:1px">&nbsp;</div>
                <div id="enemy-hud-count" style="opacity:.85;font-size:.7em;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.1px">&nbsp;</div>
                <div class="life-bar-container" style="width:110px;background:#333;border-color:#555"><div id="enemy-hud-life" class="life-bar"></div></div>
                <div id="enemy-switcher" style="margin-top:4px;display:flex;gap:2px;flex-wrap:wrap;justify-content:flex-start;width:100%;max-width:150px"></div>
            </div>
        </div>
        <canvas id="game-canvas" width="2000" height="400" style="display:block; background-color:rgba(0,0,0,0.3);"></canvas>
        <div id="ui-overlay">
            <div id="info-bars" style="width:100%;display:grid;grid-template-columns:1fr auto;gap:8px;align-items:flex-start;padding:0 10px 6px 10px;">
                <!-- Jogador e sua carta -->
                <div>
                    <div style="display:flex;gap:8px;align-items:flex-start">
                        <div style="flex-shrink:0">
                            <img id="player-card-img" style="width: 140px; height: 140px; object-fit:cover;border-radius:5px;border:2px solid rgba(255,255,255,0.3);background:#fff" src="" alt="Carta Ativa">
                        </div>
                        <div style="flex-grow:1;padding-top:2px">
                            <div style="color:#ffd700;font-weight:700;margin-bottom:3px;font-size:10px;text-transform:uppercase;letter-spacing:0.2px">Sua Carta</div>
                            <div class="life-bar-container" style="margin-bottom:4px;width:100px"><div id="player-life-bar" class="life-bar"></div></div>
                            <div id="switcher" style="margin-top:2px;display:flex;gap:3px;flex-wrap:wrap;"></div>
                        </div>
                    </div>
                </div>
                <!-- Info de moedas -->
                <div style="text-align:right;min-width:80px;padding-top:2px">
                    <div style="color:#ffd700;font-weight:700;font-size:10px;text-shadow:0.5px 0.5px 1px rgba(0,0,0,0.8)">Moedas</div>
                    <div style="color:#fff;font-size:16px;font-weight:700;text-shadow:0.5px 0.5px 1px rgba(0,0,0,0.8)"><span id="coin-balance">...</span></div>
                </div>
            </div>
            <div id="message-box" style="margin:0 10px 6px 10px;font-size:0.85em">Aguarde...</div>
            <div id="action-menu" style="margin:6px 10px 0 10px">
                <button id="attack1-btn" disabled class="btn-primary">Ataque 1</button>
                <button id="attack2-btn" disabled class="btn-primary">Ataque 2</button>
                <button id="reset-battle" class="btn-voltar">Fugir</button>
            </div>
        </div>
    </div>

    <!-- Modal de fim de batalha: ações de continuar jogando, voltar à seleção ou sair -->
    <div id="end-modal">
        <div class="panel">
            <h3 id="end-title" style="font-size:20px;color:#2c3e50;margin:0 0 8px 0">Fim da Batalha</h3>
            <p id="end-sub" style="font-size:13px;color:#555;margin-bottom:12px;line-height:1.4">Resultado</p>
            <div class="cta">
                <button id="play-again" class="btn-primary">Jogar Novamente</button>
                <button id="back-to-select" class="btn-voltar">Voltar à Seleção</button>
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