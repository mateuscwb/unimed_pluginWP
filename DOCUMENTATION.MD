# Unimed Promoção — Documentação do Plugin WordPress

Este documento descreve como instalar, configurar, usar e manter o plugin “Unimed Promoção”, que integra o Contact Form 7 (CF7) com a API do AutoPark para geração de vouchers via QR Code.


## Sumário
- Visão geral
- Requisitos
- Estrutura de pastas
- Instalação
- Configuração do formulário (Contact Form 7)
- Funcionamento (fluxo técnico)
- Testes e validação
- Resolução de problemas (FAQ)
- Personalizações comuns
- Notas de segurança e conformidade


## Visão geral
O plugin captura dados de um formulário (CF7), chama a API do AutoPark para emitir um voucher promocional (ID de promoção configurável no admin) e gera um QR Code (PNG) para exibição em modal e download. Também registra logs de operação e anexa o arquivo PNG ao envio do CF7 (campo técnico `file-682`).

Principais arquivos:
- `abgsoft-autopark-unimed/abgsoft-autopark-unimed/abgsoft-autopark-unimed.php` (núcleo do plugin)
- `abgsoft-autopark-unimed/abgsoft-autopark-unimed/js/scripts.js` (lógica front-end/CF7 + modal)
- `abgsoft-autopark-unimed/abgsoft-autopark-unimed/libs/phpqrcode.inc` (geração de QR)
- `abgsoft-autopark-unimed/abgsoft-autopark-unimed/log/` (arquivos de log)


## Requisitos
- WordPress atualizado (com jQuery padrão do WP).
- Plugin Contact Form 7 ativo (recomendado 5.4+).
- PHP compatível com a versão do WP.
- Acesso à Internet a partir do servidor (chamada à API do AutoPark).
- Permissões de escrita em:
  - `wp-content/uploads/abgsoft-uploads/`
  - `abgsoft-autopark-unimed/abgsoft-autopark-unimed/log/`


## Estrutura de pastas

```
abgsoft-autopark-unimed/
└── abgsoft-autopark-unimed/
    ├── abgsoft-autopark-unimed.php    # Arquivo principal do plugin
    ├── js/
    │   ├── jquery.modal.min.css       # CSS do modal
    │   ├── jquery.modal.min.js        # JS do modal
    │   └── scripts.js                 # Interação com CF7 e exibição do QR Code
    ├── libs/
    │   └── phpqrcode.inc              # Biblioteca PHP para geração do QR
    └── log/                           # Diretório de logs (YYYYMMDD.log)
```

Observação importante: para o WordPress detectar corretamente, a pasta do plugin dentro de `wp-content/plugins/` deve conter diretamente o arquivo `abgsoft-autopark-unimed.php` e as subpastas `js/`, `libs/`, `log/` (evite “pasta dentro de pasta” ao compactar/enviar).


## Instalação
1. Copie a pasta interna do plugin para `wp-content/plugins/abgsoft-autopark-unimed/`, garantindo que o arquivo `abgsoft-autopark-unimed.php` esteja diretamente dentro desta pasta.
2. Ative o plugin no painel do WordPress (Plugins → Ativar).
3. Crie a pasta de uploads se não existir: `wp-content/uploads/abgsoft-uploads/` e conceda permissão de escrita ao servidor web.
4. Garanta permissão de escrita em `abgsoft-autopark-unimed/log/` (ou ajuste o local de logs se necessário).


## Configuração do formulário (Contact Form 7)
O plugin espera um formulário CF7 com os seguintes campos (nomes exatos):
- `nome` (texto)
- `cpf` (texto)
- `email` (email)
- `placa` (texto)
- `unidade` (select, radio ou checkbox; pode retornar string ou array)
- (técnico) `file-682` (arquivo) — usado para anexar o PNG gerado; pode ficar oculto no formulário.

ID do formulário no front-end:
- O ID do formulário CF7 agora é lido das configurações do admin (Configurações → Unimed Promoção) e passado ao `scripts.js` automaticamente. Não é necessário editar o arquivo JS.

Anexos no e-mail do CF7 (opcional):
- No formulário CF7, adicione `[file-682]` em “Arquivos anexos” para enviar o PNG no e-mail (se desejado).


## Funcionamento (fluxo técnico)
- Hook `wpcf7_before_send_mail` chama `tratar_solicitacao()`:
  - Lê os campos do formulário.
  - Monta o payload com `promotion_id` vindo de Configurações → Unimed Promoção, `branch_id` (da `unidade`), etc.
  - Envia POST à API `https://sys.autopark.com.br/api/v1/vouchers` com `api_token` definido nas Configurações.
  - Recebendo `qrcode` da API, gera PNG via `phpqrcode.inc` em `wp-content/uploads/abgsoft-uploads/` e guarda o conteúdo base64 para retorno ao front.
  - Anexa o arquivo ao envio (`add_uploaded_file($submission, 'file-682', $pathFile)`).
- Hook `wpcf7_ajax_json_echo` injeta `qrcode` (base64) na resposta Ajax.
- Front-end (`wpcf7mailsent` em `scripts.js`): ao sucesso do CF7, exibe um modal com o QR Code e link de download. O ID do formulário é obtido das Configurações (via `wp_localize_script`).

Dependências front-end enfileiradas:
- `js/jquery.modal.min.css`, `js/jquery.modal.min.js`, `js/scripts.js` (sob demanda via `wp_enqueue_scripts`).

Logs:
- Gerados em `abgsoft-autopark-unimed/log/YYYYMMDD.log` (função `logFile`).


## Testes e validação
1. Garanta que o CF7 esteja ativo e que o formulário tenha os campos com os nomes exatos.
2. Acesse Configurações → Unimed Promoção e preencha: Token da API, ID do Formulário CF7 e ID da Promoção. Salve.
3. Envie o formulário com dados válidos e verifique:
   - Modal abre com o QR Code após o envio.
   - Link “clique aqui” baixa o PNG.
   - Arquivo PNG foi salvo em `wp-content/uploads/abgsoft-uploads/`.
   - Logs foram gerados no diretório `log/`.
   - (Opcional) E-mail do CF7 recebeu o anexo `[file-682]`.


## Resolução de problemas (FAQ)
- Modal não abre após envio:
  - O ID do formulário no `scripts.js` não corresponde ao do seu CF7.
  - O envio do CF7 não está em Ajax (conflito de tema/plugin). Verifique console e dependências.
- QR Code ausente ou em branco:
  - A API não retornou `qrcode` (veja logs). Verifique token, payload (campos) e conectividade.
- PNG não é gerado/salvo:
  - Pasta `wp-content/uploads/abgsoft-uploads/` não existe ou não é gravável.
- Anexo não vem no e-mail do CF7:
  - Adicione `[file-682]` em “Arquivos anexos” do formulário, ou ajuste o nome do campo no código.
- Logs não aparecem:
  - Verifique permissões de escrita na pasta `log/` do plugin.


## Personalizações comuns
- Alterar ID do formulário (front): agora é feito via Configurações → Unimed Promoção (campo “ID do Formulário CF7”).
- Alterar `promotion_id` (back): via Configurações → Unimed Promoção (campo “ID da Promoção”).
- Alterar token/URL da API (back): token via Configurações → Unimed Promoção; URL permanece fixa no código (pode ser alterada por desenvolvimento se necessário).
- Ajustar layout do modal (front):
  - HTML/CSS em `add_modal_qrcode()` (no PHP) e `js/jquery.modal.min.css`.


## Notas de segurança e conformidade
- O token de API e o `promotion_id` agora são definidos via Options API (Configurações → Unimed Promoção). Recomendações:
  - Restringir acesso de leitura ao diretório do plugin e ao admin.
  - Rotacionar/monitorar credenciais conforme política interna.
  - Se desejado, mover o token para `wp-config.php` e sincronizar via filtro/option harden (custom dev).
- Dados pessoais (nome, CPF, e-mail) são processados. Avalie requisitos de LGPD:
  - Informar o usuário e obter consentimento.
  - Definir base legal e política de retenção.
  - Proteger logs e dados gravados.

---

Dúvidas ou necessidade de adaptação (ex.: página de configurações no WP para token/ID da promoção, criação automática da pasta de upload, suporte a múltiplos formulários)? Abra um ticket e descreva seu cenário para orientarmos o ajuste ideal.
