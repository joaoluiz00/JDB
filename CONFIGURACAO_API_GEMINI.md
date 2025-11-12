# ⚡ Guia Rápido - Configuração da API Google Gemini

## 🎯 Por que configurar?

A API do Google Gemini permite:
- 🧠 Análise de sentimento MUITO mais precisa
- 📝 Resumos inteligentes e contextuais
- 🎨 Compreensão de nuances e sarcasmo
- 🆓 **É TOTALMENTE GRATUITA!**

---

## 🚀 Passo a Passo (5 minutos)

### 1️⃣ Obter a Chave API

1. Acesse: **https://makersuite.google.com/app/apikey**
2. Faça login com sua conta Google
3. Clique em **"Create API Key"** ou **"Get API Key"**
4. Selecione um projeto ou crie um novo
5. **Copie a chave** gerada (começa com `AIza...`)

---

### 2️⃣ Configurar no Sistema

1. Abra o arquivo:
   ```
   Service/SentimentAnalysisService.php
   ```

2. Localize a linha 17:
   ```php
   private const GEMINI_API_KEY = 'SUA_CHAVE_API_AQUI';
   ```

3. Substitua pela sua chave:
   ```php
   private const GEMINI_API_KEY = 'AIzaSyD...sua_chave_aqui';
   ```

4. Salve o arquivo

---

### 3️⃣ Testar

1. Entre no sistema como usuário
2. Compre um produto
3. Avalie o produto com um comentário
4. Visualize as avaliações
5. O sistema agora usa IA real! 🎉

---

## ✅ Como Saber se Está Funcionando?

**SEM a API configurada:**
- Análise de sentimento básica (palavras-chave)
- Resumo genérico

**COM a API configurada:**
- Análise contextual precisa
- Resumos personalizados e inteligentes
- Compreende ironia e contexto

---

## 🆓 Limites Gratuitos

A API Gemini oferece generosamente:
- **60 requisições por minuto**
- **1500 requisições por dia**

Para um e-commerce médio, isso é **MAIS que suficiente**! 🚀

---

## 🔒 Segurança

⚠️ **IMPORTANTE:**
- Nunca compartilhe sua chave API publicamente
- Não faça commit da chave no GitHub
- Mantenha o arquivo `SentimentAnalysisService.php` privado

---

## 🆘 Problemas?

### Erro 400 (Bad Request)
- Verifique se a chave foi copiada corretamente
- Certifique-se de não ter espaços antes/depois

### Erro 403 (Forbidden)
- A chave pode estar inválida
- Gere uma nova chave no Google AI Studio

### Erro 429 (Too Many Requests)
- Você excedeu o limite gratuito
- Aguarde alguns minutos ou aumente o limite

---

## 🎁 Alternativa SEM API

Se preferir não configurar a API:
- O sistema já funciona com análise básica
- Usa palavras-chave em português
- Adequado para a maioria dos casos
- Sem limites de uso

**Mas recomendamos MUITO usar a API!** 😊

---

## 📚 Documentação Oficial

Google AI for Developers:
https://ai.google.dev/

Gemini API Documentation:
https://ai.google.dev/docs

---

**Pronto! Em 5 minutos você tem IA real funcionando no seu e-commerce! 🚀**
