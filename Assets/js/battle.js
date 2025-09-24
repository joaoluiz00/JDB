document.addEventListener('DOMContentLoaded', () => {
    const deckForm = document.getElementById('deck-form');
    const userCardsDiv = document.getElementById('user-cards');
    const deckSetupDiv = document.getElementById('deck-setup');
    const enemySelectDiv = document.getElementById('enemy-select');
    const battleContainer = document.getElementById('battle-container');
    const enemyListDiv = document.getElementById('enemy-list');
    const generateEnemiesBtn = document.getElementById('generate-enemies');
    const playerCardImg = document.getElementById('player-card-img');
    const opponentCardImg = document.getElementById('opponent-card-img');
    const playerLifeBar = document.getElementById('player-life-bar');
    const opponentLifeBar = document.getElementById('opponent-life-bar');
    const messageBox = document.getElementById('message-box');
    const attack1Btn = document.getElementById('attack1-btn');
    const attack2Btn = document.getElementById('attack2-btn');
    const switcherDiv = document.getElementById('switcher');
    const resetBtn = document.getElementById('reset-battle');

    let battleState = null;
    let enemies = [];

    function updateLifeBar(el, current, total) {
        const pct = Math.max(0, (current/total)*100);
        el.style.width = pct + '%';
        el.style.backgroundColor = pct < 20 ? 'red' : (pct < 50 ? 'orange' : 'green');
    }

    async function fetchUserCards() {
        // Usa endpoint simples: reutiliza cartas do banco (poderia criar controller dedicado)
        const res = await fetch('../Controller/GenericList.php?entity=cartas_usuario');
        // Fallback: se não existir esse controller, gerar lista simplificada pedindo estado de batalha (não implementado aqui)
        if (!res.ok) return;
        const data = await res.json();
        if (!data.success) return;
        renderUserCards(data.items || []);
    }

    function renderUserCards(cards) {
        userCardsDiv.innerHTML = '';
        cards.forEach(c => {
            const wrap = document.createElement('label');
            wrap.style.width = '140px';
            wrap.style.display = 'flex';
            wrap.style.flexDirection = 'column';
            wrap.style.border = '1px solid #ccc';
            wrap.style.padding = '4px';
            wrap.style.fontSize = '12px';
            wrap.innerHTML = `
                <input type="checkbox" name="cards[]" value="${c.id_carta}" />
                <img src="${c.path}" style="width:100%;height:80px;object-fit:cover" />
                <span>${c.nome}</span>
            `;
            userCardsDiv.appendChild(wrap);
        });
    }

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

    generateEnemiesBtn.addEventListener('click', () => {
        // Gera 3 inimigos locais a partir de random fetch de cartas
        enemies = [];
        const promises = [randCard(), randCard(), randCard()];
        Promise.all(promises).then(list => {
            enemies = list;
            renderEnemies();
        });
    });

    function renderEnemies() {
        enemyListDiv.innerHTML='';
        enemies.forEach((c,i)=>{
            const div=document.createElement('div');
            div.style.border='1px solid #666';
            div.style.padding='6px';
            div.style.width='150px';
            div.style.cursor='pointer';
            div.innerHTML=`<img src="${c.path}" style="width:100%;height:90px;object-fit:cover" /><strong>${c.nome}</strong><br>Vida: ${c.vida}`;
            div.addEventListener('click',()=>chooseEnemy(i));
            enemyListDiv.appendChild(div);
        });
    }

    async function randCard(){
        const res = await fetch('../Controller/RandomCard.php');
        const data = await res.json();
        return data.card;
    }

    async function chooseEnemy(index){
        // Apenas pega o card sorteado e injeta no estado via selectEnemy endpoint
        const res = await fetch('../Controller/BatalhaController.php', { method:'POST', body: new URLSearchParams({action:'selectEnemy'})});
        const data = await res.json();
        if (!data.success) { alert(data.message); return; }
        // Substitui o inimigo embaralhado do backend pelo escolhido local visualmente
        battleState = data.battleState;
        battleState.enemy.card = enemies[index];
        battleState.enemy.hp = parseInt(enemies[index].vida,10);
        launchBattleUI();
    }

    function launchBattleUI(){
        enemySelectDiv.style.display='none';
        battleContainer.style.display='block';
        refreshVisual();
        messageBox.textContent='Batalha iniciada! Seu turno.';
        attack1Btn.disabled=false; attack2Btn.disabled=false;
    }

    function refreshVisual(){
        const active = battleState.deck[battleState.activeIndex];
        playerCardImg.src = active.card.path;
        opponentCardImg.src = battleState.enemy.card.path;
        attack1Btn.textContent = active.card.ataque1 || 'Ataque 1';
        attack2Btn.textContent = active.card.ataque2 || 'Ataque 2';
        updateLifeBar(playerLifeBar, active.hp, active.card.vida);
        updateLifeBar(opponentLifeBar, battleState.enemy.hp, battleState.enemy.card.vida);
        renderSwitcher();
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

    async function switchCard(i){
        const fd = new FormData(); fd.append('action','switch'); fd.append('index', i);
        const res = await fetch('../Controller/BatalhaController.php', {method:'POST', body:fd});
        const data = await res.json();
        if (data.success){ battleState = data.battleState; refreshVisual(); messageBox.textContent=data.message; } else { alert(data.message); }
    }

    async function doAttack(slot){
        if (!battleState || battleState.finished) return;
        const fd = new FormData(); fd.append('action','attack'); fd.append('slot', slot);
        const res = await fetch('../Controller/BatalhaController.php',{method:'POST',body:fd});
        const data = await res.json();
        if (data.success){
            battleState = data.battleState; refreshVisual(); messageBox.textContent=data.message;
            if (battleState.finished){ attack1Btn.disabled=true; attack2Btn.disabled=true; }
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
});