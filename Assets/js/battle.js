// Script principal da tela de batalha
// Fluxo: montar deck -> selecionar estágio -> executar turnos (ataque/enemyTurn) -> fim de batalha com modal
document.addEventListener('DOMContentLoaded', () => {
    const deckForm = document.getElementById('deck-form');
    const userCardsDiv = document.getElementById('user-cards');
    const deckSetupDiv = document.getElementById('deck-setup');
    const enemySelectDiv = document.getElementById('enemy-select');
    const battleContainer = document.getElementById('battle-container');
    const enemyListDiv = document.getElementById('enemy-list');
    const generateEnemiesBtn = document.getElementById('generate-enemies');
    const playerCardImg = document.getElementById('player-card-img');
    const opponentCardImg = document.getElementById('enemy-hud-img');
    const playerLifeBar = document.getElementById('player-life-bar');
    const opponentLifeBar = document.getElementById('enemy-hud-life');
    const enemyHudName = document.getElementById('enemy-hud-name');
    const enemyHudCount = document.getElementById('enemy-hud-count');
    const messageBox = document.getElementById('message-box');
    const attack1Btn = document.getElementById('attack1-btn');
    const attack2Btn = document.getElementById('attack2-btn');
    const switcherDiv = document.getElementById('switcher');
    const enemySwitcherDiv = document.getElementById('enemy-switcher');
    const endModal = document.getElementById('end-modal');
    const endTitle = document.getElementById('end-title');
    const endSub = document.getElementById('end-sub');
    const playAgainBtn = document.getElementById('play-again');
    const backToSelectBtn = document.getElementById('back-to-select');
    const resetBtn = document.getElementById('reset-battle');
    const coinBalanceSpan = document.getElementById('coin-balance');

    let battleState = null;
    let enemies = [];

    function updateLifeBar(el, current, total) {
        const pct = Math.max(0, (current/total)*100);
        el.style.width = pct + '%';
        el.style.backgroundColor = pct < 20 ? 'red' : (pct < 50 ? 'orange' : 'green');
    }

    async function fetchUserCards() {
        const res = await fetch('../Controller/BatalhaController.php', { method:'POST', body: new URLSearchParams({action:'listUserCards'}) });
        if (!res.ok) return;
        const data = await res.json();
        if (!data.success) return;
        renderUserCards(data.items || []);
    }

    // Gera a grade de cartas do usuário para montar o deck (até 3)
    function renderUserCards(cards) {
        userCardsDiv.innerHTML = '';
        cards.forEach(c => {
            const wrap = document.createElement('label');
            wrap.style.width = '100%';
            wrap.style.display = 'flex';
            wrap.style.flexDirection = 'column';
            wrap.style.border = '1px solid #ccc';
            wrap.style.padding = '4px';
            wrap.style.fontSize = '12px';
            wrap.innerHTML = `
                <input type="checkbox" name="cards[]" value="${c.id_carta}" />
                <img src="${c.path}" style="width:100%;height:140px;object-fit:contain;background:#f9fafb" />
                <span style="padding:4px 0">${c.nome}</span>
            `;
            userCardsDiv.appendChild(wrap);
        });
    }

    // Montagem do deck: envia ids e recebe o BattleState inicial já com progresso carregado do backend
    deckForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(deckForm);
        const chosen = fd.getAll('cards[]');
        if (chosen.length === 0 || chosen.length > 3) { alert('Selecione entre 1 e 3 cartas.'); return; }
        const send = new FormData();
        send.append('action','setupDeck');
        chosen.forEach(id => send.append('cards[]', id));
        const res = await fetch('../Controller/BatalhaController.php', { method:'POST', body: send });
        const data = await res.json();
        if (data.success) {
            battleState = data.battleState;
            deckSetupDiv.style.display='none';
            enemySelectDiv.style.display='block';
            messageBox.textContent = 'Deck configurado. Gere inimigos.';
        } else {
            alert(data.message);
        }
    });

    // Exibe os estágios disponíveis conforme progresso desbloqueado
    generateEnemiesBtn.addEventListener('click', async () => {
        // Exibe estágios (1..3) com bloqueio por progressão; não revela cartas
        renderStages();
    });

    // Renderiza 3 estágios (0..2) e bloqueia os acima do enemyProgress
    function renderStages(){
        enemyListDiv.innerHTML='';
        const progress = (battleState && typeof battleState.enemyProgress !== 'undefined') ? battleState.enemyProgress : 0;
        for (let i=0;i<3;i++){
            const stage = document.createElement('div');
            stage.style.border='1px solid #666';
            stage.style.padding='10px';
            stage.style.borderRadius='6px';
            stage.style.textAlign='center';
            const locked = i>progress;
            stage.innerHTML = `
                <div style='font-weight:600;margin-bottom:6px'>Inimigo ${i+1}</div>
                <div style='opacity:.85;margin-bottom:6px'>${locked? 'Bloqueado' : 'Disponível'}</div>
                <button ${locked?'disabled':''} style='width:100%'>Enfrentar</button>
            `;
            if (!locked){ stage.querySelector('button').addEventListener('click',()=>chooseStage(i)); }
            enemyListDiv.appendChild(stage);
        }
    }

    async function randCard(){
        const res = await fetch('../Controller/BatalhaController.php', { method:'POST', body: new URLSearchParams({action:'randomCard'})});
        const data = await res.json();
        return data.card;
    }

    async function genEnemyDeck(){
        const cards = await Promise.all([randCard(), randCard(), randCard()]);
        return cards;
    }

    // Seleciona um estágio; backend sorteia o deck inimigo sem revelar as cartas
    async function chooseStage(stage){
        const params = new URLSearchParams({action:'selectEnemy', stage});
        const res = await fetch('../Controller/BatalhaController.php', { method:'POST', body: params});
        const data = await res.json();
        if (!data.success) { alert(data.message); return; }
        battleState = data.battleState;
        battleState.enemyActiveIndex = 0;
        launchBattleUI();
    }

    // Mostra a arena de batalha e inicializa botões/vida/hud
    function launchBattleUI(){
        enemySelectDiv.style.display='none';
        battleContainer.style.display='block';
        refreshVisual();
        messageBox.textContent='Batalha iniciada! Seu turno.';
        attack1Btn.disabled=false; attack2Btn.disabled=false;
        refreshBalance();
    }

    // Atualiza imagens, barras de vida, HUD do inimigo e switchers
    function refreshVisual(){
        const active = battleState.deck[battleState.activeIndex];
        const eActive = battleState.enemyDeck[battleState.enemyActiveIndex];
    playerCardImg.src = active.card.path;
    opponentCardImg.src = eActive.card.path;
    if (enemyHudName) enemyHudName.textContent = eActive.card.nome || '';
    if (enemyHudCount) enemyHudCount.textContent = `Carta ${battleState.enemyActiveIndex+1} de ${battleState.enemyDeck.length}`;
        attack1Btn.textContent = active.card.ataque1 || 'Ataque 1';
        attack2Btn.textContent = active.card.ataque2 || 'Ataque 2';
        updateLifeBar(playerLifeBar, active.hp, active.card.vida);
        updateLifeBar(opponentLifeBar, eActive.hp, eActive.card.vida);
        renderSwitcher();
        renderEnemySwitcher();
    }

    function renderSwitcher(){
        switcherDiv.innerHTML='';
        battleState.deck.forEach((c,i)=>{
            const btn=document.createElement('button');
            btn.textContent = (i===battleState.activeIndex?'* ':'') + c.card.nome + ' ('+c.hp+')';
            btn.disabled = c.hp<=0 || i===battleState.activeIndex;
            btn.addEventListener('click',()=>switchCard(i));
            switcherDiv.appendChild(btn);
        });
    }

    function renderEnemySwitcher(){
        if (!enemySwitcherDiv) return;
        enemySwitcherDiv.innerHTML='';
        battleState.enemyDeck.forEach((c,i)=>{
            const b=document.createElement('button');
            b.textContent = (i===battleState.enemyActiveIndex?'* ':'') + c.card.nome + ' ('+c.hp+')';
            b.disabled = true; // Apenas visual; não permite forçar troca do inimigo
            enemySwitcherDiv.appendChild(b);
        });
    }

    async function switchCard(i){
        const fd = new FormData(); fd.append('action','switch'); fd.append('index', i);
        const res = await fetch('../Controller/BatalhaController.php', {method:'POST', body:fd});
        const data = await res.json();
        if (data.success){ battleState = data.battleState; refreshVisual(); messageBox.textContent=data.message; } else { alert(data.message); }
    }

    // Ciclo de turno: jogador ataca, anima; se não finalizar, inimigo ataca após pequeno atraso
    async function doAttack(slot){
        if (!battleState || battleState.finished) return;
        // Anima o ataque do jogador
        playerCardImg.classList.remove('punch'); void playerCardImg.offsetWidth; playerCardImg.classList.add('punch');
        const fd = new FormData(); fd.append('action','attack'); fd.append('slot', slot);
        const res = await fetch('../Controller/BatalhaController.php',{method:'POST',body:fd});
        const data = await res.json();
        if (data.success){
            battleState = data.battleState; refreshVisual(); messageBox.textContent=data.message;
            if (battleState.finished){
                attack1Btn.disabled=true; attack2Btn.disabled=true; refreshBalance(); showEndModal(data); return;
            }
            // Se ainda não terminou, aguarda pequeno delay e executa turno inimigo
            attack1Btn.disabled = true; attack2Btn.disabled = true;
            await sleep(500);
            opponentCardImg.classList.remove('punch'); void opponentCardImg.offsetWidth; opponentCardImg.classList.add('punch');
            const fd2 = new URLSearchParams({action:'enemyTurn'});
            const res2 = await fetch('../Controller/BatalhaController.php',{method:'POST',body:fd2});
            const data2 = await res2.json();
            if (data2.success){
                // Anima “hit” no jogador caso tenha tomado dano
                playerCardImg.classList.remove('shake'); void playerCardImg.offsetWidth; playerCardImg.classList.add('shake');
                battleState = data2.battleState; refreshVisual(); messageBox.textContent=data2.message || messageBox.textContent;
                if (battleState.finished){ attack1Btn.disabled=true; attack2Btn.disabled=true; refreshBalance(); showEndModal(data2); return; }
            }
            attack1Btn.disabled = false; attack2Btn.disabled = false;
        } else { alert(data.message); }
    }

    attack1Btn.addEventListener('click',()=>doAttack('1'));
    attack2Btn.addEventListener('click',()=>doAttack('2'));
    resetBtn.addEventListener('click', async()=>{
        await fetch('../Controller/BatalhaController.php', {method:'POST', body:new URLSearchParams({action:'reset'})});
        location.reload();
    });

    // Inicializa listagem de cartas do usuário
    fetchUserCards();
    refreshBalance();
    // Mostra modal de fim de batalha com texto e recompensa (se houver)
    function showEndModal(data){
        const won = battleState.winner === 'player';
        endTitle.textContent = won ? 'Vitória!' : 'Derrota';
        const reward = data && typeof data.reward !== 'undefined' ? parseInt(data.reward,10) : 0;
        endSub.textContent = won ? `Você ganhou ${reward} moedas.` : 'Tente novamente para ganhar moedas!';
        endModal.style.display = 'flex';
    }

    playAgainBtn?.addEventListener('click', async ()=>{
        const res = await fetch('../Controller/BatalhaController.php', {method:'POST', body: new URLSearchParams({action:'newBattle'})});
        const data = await res.json();
        if (data.success){
            battleState = data.battleState;
            endModal.style.display = 'none';
            attack1Btn.disabled=false; attack2Btn.disabled=false;
            refreshVisual();
            messageBox.textContent = 'Nova batalha iniciada!';
        }
    });

    backToSelectBtn?.addEventListener('click', async ()=>{
        // Pede ao backend para limpar inimigo atual e voltar para seleção
        const res = await fetch('../Controller/BatalhaController.php', {method:'POST', body: new URLSearchParams({action:'backToEnemySelect'})});
        const data = await res.json();
        if (data.success){
            battleState = data.battleState;
            endModal.style.display = 'none';
            battleContainer.style.display='none';
            enemySelectDiv.style.display='block';
            // Recarrega as opções de estágio com progresso mais recente, se houver
            renderStages();
            messageBox.textContent = data.message || 'Escolha um inimigo para enfrentar.';
        } else {
            alert(data.message || 'Não foi possível voltar à seleção.');
        }
    });

    async function refreshBalance(){
        try{
            const res = await fetch('../Controller/BatalhaController.php', {method:'POST', body:new URLSearchParams({action:'balance'})});
            const data = await res.json();
            if (data.success && typeof data.coin !== 'undefined') {
                coinBalanceSpan.textContent = data.coin;
            }
        }catch(e){ /* silencioso */ }
    }

    function sleep(ms){ return new Promise(r=>setTimeout(r,ms)); }
});