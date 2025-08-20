document.addEventListener('DOMContentLoaded', () => {
    const battleContainer = document.getElementById('battle-container');
    const playerCardImg = document.getElementById('player-card-img');
    const opponentCardImg = document.getElementById('opponent-card-img');
    const playerLifeBar = document.getElementById('player-life-bar');
    const opponentLifeBar = document.getElementById('opponent-life-bar');
    const messageBox = document.getElementById('message-box');
    const attack1Btn = document.getElementById('attack1-btn');
    const attack2Btn = document.getElementById('attack2-btn');

    let battleState = null;

    // Função para atualizar a barra de vida
    function updateLifeBar(element, currentLife, totalLife) {
        const percentage = (currentLife / totalLife) * 100;
        element.style.width = percentage + '%';
        if (percentage < 20) {
            element.style.backgroundColor = 'red';
        } else if (percentage < 50) {
            element.style.backgroundColor = 'orange';
        } else {
            element.style.backgroundColor = 'green';
        }
    }

    // Função para iniciar a batalha
    async function iniciarBatalha() {
        try {
            messageBox.textContent = 'Iniciando batalha...';
            const response = await fetch('../Controller/BatalhaController.php?action=iniciarBatalha');
            const data = await response.json();

            if (data.success) {
                battleState = data.battleState;
                playerCardImg.src = battleState.player.carta.path;
                opponentCardImg.src = battleState.opponent.carta.path;
                
                updateLifeBar(playerLifeBar, battleState.player.vidaAtual, battleState.player.carta.vida);
                updateLifeBar(opponentLifeBar, battleState.opponent.vidaAtual, battleState.opponent.carta.vida);
                
                messageBox.textContent = `Uma batalha contra um(a) ${battleState.opponent.carta.nome} começou!`;
                attack1Btn.textContent = battleState.player.carta.ataque1;
                attack2Btn.textContent = battleState.player.carta.ataque2;
            } else {
                messageBox.textContent = 'Erro ao iniciar a batalha: ' + data.message;
            }
        } catch (error) {
            console.error('Erro de comunicação:', error);
            messageBox.textContent = 'Erro de comunicação com o servidor.';
        }
    }

    // Função para processar o turno
    async function processarTurno(attackId) {
        if (!battleState) return;

        messageBox.textContent = 'Processando sua ação...';
        
        const formData = new FormData();
        formData.append('action', 'processarTurno');
        formData.append('batalhaId', battleState.id_batalha);
        formData.append('attackId', attackId);

        try {
            const response = await fetch('../Controller/BatalhaController.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                // Atualiza o estado da batalha
                battleState.player.vidaAtual = data.battleState.player.vidaAtual;
                battleState.opponent.vidaAtual = data.battleState.opponent.vidaAtual;
                
                // Atualiza as barras de vida
                updateLifeBar(playerLifeBar, battleState.player.vidaAtual, battleState.player.carta.vida);
                updateLifeBar(opponentLifeBar, battleState.opponent.vidaAtual, battleState.opponent.carta.vida);
                
                // Exibe a mensagem do turno
                messageBox.textContent = data.message;
                
                if (data.battleState.winner) {
                    // Lidar com o fim da batalha
                    if (data.battleState.winner === 'player') {
                        messageBox.textContent = 'Parabéns, você venceu a batalha!';
                    } else {
                        messageBox.textContent = 'Você foi derrotado. Tente novamente!';
                    }
                    // Desabilitar botões, etc.
                    attack1Btn.disabled = true;
                    attack2Btn.disabled = true;
                }
                
            } else {
                messageBox.textContent = 'Erro no turno: ' + data.message;
            }
        } catch (error) {
            console.error('Erro de comunicação:', error);
            messageBox.textContent = 'Erro de comunicação com o servidor.';
        }
    }

    // Event listeners para os botões de ataque
    attack1Btn.addEventListener('click', () => processarTurno('attack1'));
    attack2Btn.addEventListener('click', () => processarTurno('attack2'));

    // Inicia a batalha assim que a página é carregada
    iniciarBatalha();
});