$filePath = "c:\xampp\htdocs\JDB\Assets\js\battle.js"
$content = [System.IO.File]::ReadAllText($filePath, [System.Text.Encoding]::UTF8)

# Padrão antigo (minificado em uma linha)
$oldPattern = '    async function doAttack\(slot\)\{[\s\S]*?        \} else \{ alert\(data\.message\); \}\s*?\}'

# Nova função
$newFunction = @'
    async function doAttack(slot){
        if (!battleState || battleState.finished) return;
        
        // Desabilita botões e mostra mensagem de processamento
        attack1Btn.disabled = true;
        attack2Btn.disabled = true;
        messageBox.textContent = '⏳ Processando ataque...';
        
        // Anima o ataque do jogador
        playerCardImg.classList.remove('punch');
        void playerCardImg.offsetWidth;
        playerCardImg.classList.add('punch');
        
        const fd = new FormData();
        fd.append('action', 'attack');
        fd.append('slot', slot);
        const res = await fetch('../Controller/BatalhaController.php', {method: 'POST', body: fd});
        const data = await res.json();
        
        if (data.success){
            battleState = data.battleState;
            refreshVisual();
            
            // Adiciona emojis e detecta crítico no ataque do jogador
            let playerMessage = data.message;
            if (playerMessage.toLowerCase().includes('critico')) {
                playerMessage = '💥 ATAQUE CRÍTICO! ' + playerMessage;
            } else {
                playerMessage = '⚔️ ' + playerMessage;
            }
            messageBox.textContent = playerMessage;
            
            // Se a batalha terminou após o ataque do jogador
            if (battleState.finished){
                attack1Btn.disabled = true;
                attack2Btn.disabled = true;
                refreshBalance();
                await sleep(1000);
                showEndModal(data);
                return;
            }
            
            // Se ainda não terminou, aguarda e executa turno inimigo
            attack1Btn.disabled = true;
            attack2Btn.disabled = true;
            await sleep(2000);
            
            opponentCardImg.classList.remove('punch');
            void opponentCardImg.offsetWidth;
            opponentCardImg.classList.add('punch');
            
            const fd2 = new URLSearchParams({action: 'enemyTurn'});
            const res2 = await fetch('../Controller/BatalhaController.php', {method: 'POST', body: fd2});
            const data2 = await res2.json();
            
            if (data2.success){
                // Anima "hit" no jogador caso tenha tomado dano
                playerCardImg.classList.remove('shake');
                void playerCardImg.offsetWidth;
                playerCardImg.classList.add('shake');
                
                battleState = data2.battleState;
                refreshVisual();
                
                // Adiciona emojis e detecta crítico no ataque do inimigo
                let enemyMessage = data2.message || messageBox.textContent;
                if (enemyMessage.toLowerCase().includes('critico')) {
                    enemyMessage = '💥 CRÍTICO DO INIMIGO! ' + enemyMessage;
                } else {
                    enemyMessage = '🗡️ ' + enemyMessage;
                }
                messageBox.textContent = enemyMessage;
                
                // Se a batalha terminou após o turno do inimigo
                if (battleState.finished){
                    attack1Btn.disabled = true;
                    attack2Btn.disabled = true;
                    refreshBalance();
                    await sleep(1000);
                    showEndModal(data2);
                    return;
                }
            }
            
            attack1Btn.disabled = false;
            attack2Btn.disabled = false;
        } else {
            alert(data.message);
            attack1Btn.disabled = false;
            attack2Btn.disabled = false;
        }
    }
'@

# Tentar com regex
if ([System.Text.RegularExpressions.Regex]::IsMatch($content, $oldPattern)) {
    Write-Host "Padrão encontrado com regex!"
    $newContent = [System.Text.RegularExpressions.Regex]::Replace($content, $oldPattern, $newFunction)
    [System.IO.File]::WriteAllText($filePath, $newContent, [System.Text.Encoding]::UTF8)
    Write-Host "✅ Arquivo atualizado com sucesso!"
} else {
    Write-Host "❌ Padrão não encontrado"
}
