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
    let currentStage = null; // estágio selecionado (0..2) para usar como fundo da arena

    function updateLifeBar(el, current, total) {
        const pct = Math.max(0, (current/total)*100);
        el.style.width = pct + '%';
        
        // Aplica classes para animação de baixa vida
        if (pct < 20) {
            el.classList.add('low');
            el.style.backgroundColor = '';
        } else {
            el.classList.remove('low');
            el.style.backgroundColor = '';
        }
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
            wrap.style.border = '3px solid #ddd';
            wrap.style.borderRadius = '8px';
            wrap.style.padding = '8px';
            wrap.style.fontSize = '12px';
            wrap.style.cursor = 'pointer';
            wrap.style.transition = 'all 0.3s ease';
            wrap.style.background = '#fff';
            
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.name = 'cards[]';
            checkbox.value = c.id_carta;
            
            // Add event listener para melhorar visual ao selecionar
            checkbox.addEventListener('change', (e) => {
                if (e.target.checked) {
                    wrap.style.borderColor = '#3498db';
                    wrap.style.boxShadow = '0 6px 16px rgba(52, 152, 219, 0.3)';
                    wrap.style.transform = 'translateY(-4px)';
                } else {
                    wrap.style.borderColor = '#ddd';
                    wrap.style.boxShadow = 'none';
                    wrap.style.transform = 'translateY(0)';
                }
            });
            
            wrap.appendChild(checkbox);
            
            const img = document.createElement('img');
            img.src = c.path;
            img.style.width = '100%';
            img.style.height = '140px';
            img.style.objectFit = 'contain';
            img.style.background = '#f9fafb';
            img.style.borderRadius = '4px';
            img.style.marginBottom = '4px';
            wrap.appendChild(img);
            
            const label = document.createElement('span');
            label.style.padding = '6px 0';
            label.style.fontWeight = '600';
            label.style.color = '#2c3e50';
            label.style.textAlign = 'center';
            label.textContent = c.nome;
            wrap.appendChild(label);
            
            userCardsDiv.appendChild(wrap);
        });
    }

    // Montagem do deck: envia ids e recebe o BattleState inicial já com progresso carregado do backend
    deckForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(deckForm);
        const chosen = fd.getAll('cards[]');
        if (chosen.length === 0 || chosen.length > 3) { 
            alert('Selecione entre 1 e 3 cartas.');
            return; 
        }
        const send = new FormData();
        send.append('action','setupDeck');
        chosen.forEach(id => send.append('cards[]', id));
        const res = await fetch('../Controller/BatalhaController.php', { method:'POST', body: send });
        const data = await res.json();
        if (data.success) {
            battleState = data.battleState;
            deckSetupDiv.style.display='none';
            enemySelectDiv.style.display='block';
            messageBox.textContent = 'Deck configurado! Escolha seu inimigo.';
            // Scroll suave para a próxima seção
            setTimeout(() => {
                enemySelectDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
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
            stage.className = 'battle-stage';
            const locked = i>progress;
            if (locked) stage.classList.add('locked');
            
            const title = document.createElement('h3');
            title.textContent = `Inimigo ${i+1}`;
            stage.appendChild(title);
            
            const status = document.createElement('div');
            status.className = 'stage-status';
            status.textContent = locked ? 'Bloqueado' : 'Disponível';
            stage.appendChild(status);

            // Imagem representativa do inimigo para este estágio (um por estágio)
            const enemyImgWrap = document.createElement('div');
            enemyImgWrap.className = 'stage-enemy-wrap';
            const enemyImg = document.createElement('img');
            enemyImg.src = `../Assets/img/inimigo${i+1}.png`;
            enemyImg.alt = `Inimigo ${i+1}`;
            enemyImg.className = 'stage-enemy-img';
            enemyImgWrap.appendChild(enemyImg);
            stage.appendChild(enemyImgWrap);

            const btn = document.createElement('button');
            btn.className = 'btn-primary btn-block';
            btn.textContent = 'Enfrentar';
            if (locked) { 
                btn.disabled = true;
                btn.textContent = 'Bloqueado';
            }
            else { 
                btn.addEventListener('click', ()=> {
                    btn.disabled = true;
                    btn.textContent = '⏳ Carregando...';
                    chooseStage(i);
                }); 
            }
            stage.appendChild(btn);

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
        // guarda o estágio atual para definir o fundo da arena
        currentStage = stage;
        messageBox.textContent = 'Preparando batalha...';
        setTimeout(launchBattleUI, 500);
    }

    // Mostra a arena de batalha e inicializa botões/vida/hud
    function launchBattleUI(){
        enemySelectDiv.style.display='none';
        battleContainer.style.display='block';
        
        refreshVisual();
        messageBox.textContent='Batalha iniciada! Seu turno.';
        attack1Btn.disabled=false; attack2Btn.disabled=false;
        refreshBalance();
        
        // Scroll suave para a arena
        setTimeout(() => {
            battleContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 100);
    }

    // Atualiza imagens, barras de vida, HUD do inimigo e switchers
    function refreshVisual(){
        const active = battleState.deck[battleState.activeIndex];
        const eActive = battleState.enemyDeck[battleState.enemyActiveIndex];
        
        // Aplica efeito visual na carta ativa
        playerCardImg.classList.add('active-card');
        playerCardImg.src = active.card.path;
        
        opponentCardImg.classList.add('active-card');
        opponentCardImg.src = eActive.card.path;
        
        if (enemyHudName) enemyHudName.textContent = eActive.card.nome || '';
        if (enemyHudCount) enemyHudCount.textContent = `Carta ${battleState.enemyActiveIndex+1} de ${battleState.enemyDeck.length}`;
        
        attack1Btn.textContent = (active.card.ataque1 || 'Ataque 1');
        attack2Btn.textContent = (active.card.ataque2 || 'Ataque 2');
        
        updateLifeBar(playerLifeBar, active.hp, active.card.vida);
        updateLifeBar(opponentLifeBar, eActive.hp, eActive.card.vida);
        renderSwitcher();
        renderEnemySwitcher();
    }

    function renderSwitcher(){
        switcherDiv.innerHTML='';
        battleState.deck.forEach((c,i)=>{
            const btn=document.createElement('button');
            btn.textContent = (i===battleState.activeIndex?'★ ':'') + c.card.nome + ' ('+c.hp+')';
            if (i===battleState.activeIndex) btn.classList.add('active');
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
            b.textContent = (i===battleState.enemyActiveIndex?'★ ':'') + c.card.nome + ' ('+c.hp+')';
            if (i===battleState.enemyActiveIndex) b.classList.add('active');
            b.disabled = true; // Apenas visual; não permite forçar troca do inimigo
            enemySwitcherDiv.appendChild(b);
        });
    }

    async function switchCard(i){
        const fd = new FormData(); fd.append('action','switch'); fd.append('index', i);
        const res = await fetch('../Controller/BatalhaController.php', {method:'POST', body:fd});
        const data = await res.json();
        if (data.success){ 
            battleState = data.battleState; 
            refreshVisual(); 
            messageBox.textContent='Troca realizada.';
            
            // Animação de troca
            playerCardImg.classList.remove('active-card');
            void playerCardImg.offsetWidth;
            playerCardImg.classList.add('active-card');
        } else { 
            alert('❌ ' + data.message); 
        }
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
            await sleep(2000);
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
        endTitle.textContent = won ? '🏆 Vitória!' : '💀 Derrota';
        const reward = data && typeof data.reward !== 'undefined' ? parseInt(data.reward,10) : 0;
        
        // Aplica classe para estilizar diferente se ganhou ou perdeu
        endModal.classList.remove('victory', 'defeat');
        if (won) {
            endModal.classList.add('victory');
            endSub.textContent = `🎉 Parabéns! Você ganhou ${reward} moedas!`;
        } else {
            endModal.classList.add('defeat');
            endSub.textContent = '😢 Tente novamente para ganhar moedas!';
        }
        
        endModal.classList.add('show');
    }

    playAgainBtn?.addEventListener('click', async ()=>{
        const res = await fetch('../Controller/BatalhaController.php', {method:'POST', body: new URLSearchParams({action:'newBattle'})});
        const data = await res.json();
        if (data.success){
            battleState = data.battleState;
            endModal.classList.remove('show');
            attack1Btn.disabled=false; 
            attack2Btn.disabled=false;
            refreshVisual();
            messageBox.textContent = '🔄 Nova batalha iniciada!';
            
            // Scroll suave para a arena
            setTimeout(() => {
                battleContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 100);
        }
    });

    backToSelectBtn?.addEventListener('click', async ()=>{
        // Pede ao backend para limpar inimigo atual e voltar para seleção
        const res = await fetch('../Controller/BatalhaController.php', {method:'POST', body: new URLSearchParams({action:'backToEnemySelect'})});
        const data = await res.json();
        if (data.success){
            battleState = data.battleState;
            endModal.classList.remove('show');
            battleContainer.style.display='none';
            // limpa background ao voltar à seleção
            currentStage = null;
            battleContainer.style.backgroundImage = '';
            document.body.classList.remove('battle-bg');
            enemySelectDiv.style.display='block';
            // Recarrega as opções de estágio com progresso mais recente, se houver
            renderStages();
            messageBox.textContent = data.message || '👀 Escolha um inimigo para enfrentar.';
            
            // Scroll suave
            setTimeout(() => {
                enemySelectDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        } else {
            alert('❌ ' + (data.message || 'Não foi possível voltar à seleção.'));
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