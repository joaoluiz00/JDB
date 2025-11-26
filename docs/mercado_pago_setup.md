# Guia de Configuração — Pagamentos Mercado Pago

Este documento reúne tudo o que precisa ser feito para que os fluxos de cartão de crédito e PIX funcionem no projeto JDB. As rotas envolvidas já estão prontas (serviços, processamentos e views), então basta cumprir os passos abaixo para liberar a operação em produção ou sandbox.

## 1. Visão geral dos componentes

- `config/mercado_pago.php`: centraliza as credenciais (token, public key, integrator_id, notification_url).
- `Service/MercadoPagoService.php`: client PHP que cria cobranças PIX/cartão e consulta pagamentos.
- `Processamento/ProcessCartao.php` e `Processamento/ProcessPagamentoPix.php`: validam pagamentos, criam pedidos, liberam itens/moedas e enviam notificações.
- Views atualizadas:
  - `View/AdicionarCartão.php`: checkout cartão para carrinho/itens.
  - `View/ConfirmarPagamentoPix.php`: gera QR code PIX e faz polling do status.
  - `View/ConfirmarPagamentoMoedas.php`: compra de moedas via cartão ou redirecionamento para PIX.
- `Processamento/ProcessMercadoPagoStatus.php`: endpoint interno que consulta o status de um pagamento (usado no polling PIX).

## 2. Pré-requisitos

1. Conta Mercado Pago com acesso ao painel de desenvolvedor.
2. Ambiente PHP >= 7.4 com extensões `curl`, `json` e `openssl` habilitadas (Recomendado PHP 8+). O XAMPP já possui esses módulos — confirme no `php.ini`.
3. URL pública (ou túnel tipo Ngrok/Cloudflared) se quiser receber notificações webhooks (`notification_url`).
4. HTTPS recomendado para produção.
5. Banco MySQL atualizado com as colunas usadas pela nova lógica (ver seção 4).

## 3. Configurar credenciais

As chaves são lidas primeiro de variáveis de ambiente e, se não existirem, dos valores "TEST-*" de fallback. Para ambientes reais substitua **sempre** os valores de teste.

### 3.1. Obter credenciais

No painel Mercado Pago > Seu Negócio > Configurações > Credenciais:
- **Access Token** (API privada) — usado pelo backend.
- **Public Key** — usada no JavaScript (`AdicionarCartão.php` e `ConfirmarPagamentoMoedas.php`).
- **Integrator ID** (opcional, apenas se o MP solicitar para identificar sua integração).

Se desejar usar um Webhook específico, configure o endpoint público (ex: `https://seusite.com/Processamento/ProcessPagamentoPix.php`) e copie a URL para a variável `MERCADO_PAGO_NOTIFICATION_URL`.

### 3.2. Definir variáveis no Windows (XAMPP)

Como o projeto roda em Apache/PHP tradicional, existem 3 jeitos práticos:

1. **`httpd.conf`/`httpd-vhosts.conf`**:
   ```conf
   SetEnv MERCADO_PAGO_ACCESS_TOKEN "TEST-123"
   SetEnv MERCADO_PAGO_PUBLIC_KEY "TEST-abc"
   SetEnv MERCADO_PAGO_NOTIFICATION_URL "https://exemplo.ngrok.io/webhook"
   ```
   Reinicie o Apache após editar.

2. **Arquivo `.env` + `phpdotenv`**: caso já exista mecanismo de carregar `.env`, basta definir as chaves. (Atualmente o projeto não usa Dotenv nativamente.)

3. **Variáveis de usuário do Windows**:
   - Painel de Controle > Sistema > Configurações avançadas > Variáveis de Ambiente.
   - Adicione `MERCADO_PAGO_ACCESS_TOKEN`, `MERCADO_PAGO_PUBLIC_KEY`, etc.
   - Reinicie o serviço Apache ou o próprio Windows para garantir que o PHP leia os valores.

Se nenhuma variável estiver presente, o código cai nos placeholders de teste (`TEST-ACCESS-TOKEN`), portanto **sem substituição real os pagamentos falharão**.

## 4. Atualizar o banco de dados

Os scripts de processamento gravam campos adicionais nas tabelas `pedidos` e `enderecos_entrega`. Execute os ALTER/CREATE abaixo uma única vez no schema `banco`:

```sql
ALTER TABLE pedidos
    ADD COLUMN hash_transacao VARCHAR(64) NULL AFTER status,
    ADD COLUMN payment_id VARCHAR(64) NULL AFTER hash_transacao,
    ADD COLUMN payment_status VARCHAR(32) NULL AFTER payment_id,
    ADD COLUMN payment_detail VARCHAR(64) NULL AFTER payment_status,
    ADD COLUMN external_reference VARCHAR(64) NULL AFTER payment_detail;

CREATE TABLE IF NOT EXISTS enderecos_entrega (
    id_endereco INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    cep VARCHAR(15) NOT NULL,
    rua VARCHAR(120) NOT NULL,
    numero VARCHAR(20) NOT NULL,
    complemento VARCHAR(60) NULL,
    bairro VARCHAR(80) NOT NULL,
    cidade VARCHAR(80) NOT NULL,
    estado VARCHAR(2) NOT NULL,
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido)
); 
```

> Ajuste os nomes das colunas caso sua tabela já tenha versões equivalentes. O importante é garantir que os campos existam e aceitem `VARCHAR` para IDs grandes do Mercado Pago.

## 5. Fluxos de checkout e o que validar

1. **Endereço & CPF**
   - `View/ConfirmarEndereco.php` exige que o usuário informe CPF e endereço antes de seguir para PIX ou Cartão.
   - Certifique-se de que o formulário está enviando `documento` (CPF) e dados de endereço — sem isso PIX recusa o pagamento.

2. **Cartão de crédito**
   - `View/AdicionarCartão.php` (carrinho/itens) e `View/ConfirmarPagamentoMoedas.php` (moedas) carregam o SDK `https://sdk.mercadopago.com/js/v2` usando `public_key`.
   - O `cardForm` gera `token`, `payment_method_id`, `issuer_id`, `installments` e envia tudo para `Processamento/ProcessCartao.php`.
   - No backend, `MercadoPagoService::createCardPayment` dispara a cobrança. Pagamentos aprovados liberam itens/moedas e notificam via `NotificationService`.

3. **PIX**
   - `View/ConfirmarPagamentoPix.php` chama `MercadoPagoService::createPixPayment`, mostra o QR code (`qr_code_base64`) e faz polling em `Processamento/ProcessMercadoPagoStatus.php`.
   - Após o status retornar `approved`, o formulário envia para `Processamento/ProcessPagamentoPix.php`, que confere o status via API antes de conceder os itens.
   - Em compras de moedas, `ConfirmarPagamentoMoedas.php` possui um botão "Gerar PIX" que reaproveita os mesmos parâmetros e redireciona para essa tela.

4. **Notificações (opcional)**
   - Se preencher `notification_url`, configure a rota pública para receber POSTs do Mercado Pago. Você pode criar um dispatcher simples reaproveitando `ProcessPagamentoPix.php` ou um webhook dedicado que chame `MercadoPagoService::getPayment($id)` e atualize o pedido.

## 6. Testes recomendados

1. **Sandbox habilitado** no painel do Mercado Pago (ou utilize credenciais de teste padrão `TEST-`):
   - Gere usuários de teste (comprador e vendedor) e cartões de teste disponibilizados pelo MP.
2. **Fluxo cartão**:
   - Adicione itens ao carrinho, vá para "Pagar com Cartão" e utilize um cartão sandbox.
   - Verifique se o pedido aparece em `HistoricoCompras.php` e os itens são liberados.
3. **Fluxo PIX**:
   - Gere o QR, pague usando o app do comprador sandbox (ou aprove o pagamento via painel) e confirme que o polling reconhece o status.
   - Confira se o registro da tabela `pedidos` recebeu `payment_id` e `payment_status=approved`.
4. **Compra de moedas**:
   - Teste tanto cartão quanto PIX a partir de `View/LojaMoedas.php` e veja se `BancoDeDados::addCoins` incrementa corretamente as moedas do usuário.
5. **Erros comuns**: deixe as credenciais inválidas e confirme se o usuário recebe feedback (`$_SESSION['error']`).

## 7. Checklist antes de subir para produção

- [ ] Credenciais reais definidas (`ACCESS_TOKEN`, `PUBLIC_KEY`, `INTEGRATOR_ID`, `NOTIFICATION_URL`).
- [ ] Banco atualizado com as colunas novas.
- [ ] HTTPS configurado.
- [ ] Logs/erros do PHP limpos e modo debug desativado.
- [ ] URL pública para notificações (ou estratégia alternativa de reconciliação) validada.
- [ ] Fluxos de carrinho, item individual e moedas testados e aprovados.

Seguindo estes passos, a integração Mercado Pago já existente no código funcionará corretamente tanto para PIX quanto para cartão, cobrindo carrinho completo, compras individuais e pacotes de moedas.
