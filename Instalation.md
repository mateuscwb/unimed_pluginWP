Instalação
==========

### Verifique a pasta correta

No seu projeto há duas pastas com o mesmo nome. A pasta do plugin que deve ir para o WP é a que contém diretamente:
- `abgsoft-autopark-unimed.php`
- `js/`
- `libs/`
- `log/`
No seu workspace ela é: `abgsoft-autopark-unimed/abgsoft-autopark-unimed/`.

## Opção A – Instalar via .zip pelo WP

1. Entre em `abgsoft-autopark-unimed/abgsoft-autopark-unimed/`.
2. Compacte o conteúdo desta pasta (arquivos e subpastas) em um `.zip` cuja raiz contenha `abgsoft-autopark-unimed.php`.
   - Dica: o `.zip` deve descompactar como `abgsoft-autopark-unimed/abgsoft-autopark-unimed.php` (sem “pasta dentro de pasta”).
3. No painel WP: Plugins → Adicionar novo → Enviar plugin → selecione o `.zip` → Instalar agora → Ativar.

## Opção B – Instalar por FTP/gerenciador de arquivos

1. Crie a pasta `wp-content/plugins/abgsoft-autopark-unimed/`.
2. Envie para ela todos os arquivos e pastas de `abgsoft-autopark-unimed/abgsoft-autopark-unimed/`.
3. No painel WP: Plugins → Ativar “Unimed Promoção”.

## Pós-instalação (obrigatório)

### Permissões e pastas

- Crie a pasta de uploads: `wp-content/uploads/abgsoft-uploads/` (com permissão de escrita pelo PHP).
- Garanta que a pasta de logs do plugin `wp-content/plugins/abgsoft-autopark-unimed/log/` seja gravável.

### Dependências

- Contact Form 7 instalado e ativo.
- WordPress atualizado (usa o jQuery padrão do WP).

## Configuração no Admin

Abra: Configurações → Unimed Promoção. Preencha e salve:

- Token da API (fornecido pela AutoPark).
- ID do Formulário CF7 (ID do formulário que você criou).
- ID da Promoção (por exemplo, 1 ou o ID correto para seu uso).

## Configuração do formulário (CF7)

Crie um formulário com estes nomes exatos de campos:

- `nome` (texto)
- `cpf` (texto)
- `email` (email)
- `placa` (texto)
- `unidade` (select/radio/checkbox; pode retornar string ou array)

Opcional técnico: adicionar `[file-682]` em “Arquivos anexos” (para enviar o PNG do QR no e-mail).

## Teste rápido

Checklist:

1. Preencha Configurações → Unimed Promoção (token, ID CF7, ID promoção).
2. Envie o formulário CF7 com dados válidos.
3. Deve abrir um modal com o QR Code e link “clique aqui” para download.
4. Verifique:
   - PNG criado em `wp-content/uploads/abgsoft-uploads/`.
   - Log em `wp-content/plugins/abgsoft-autopark-unimed/log/YYYYMMDD.log`.
   - (Se configurado) e-mail do CF7 com anexo `[file-682]`.

## Resolução de problemas

- Modal não abre: verifique se o ID do formulário no painel (Configurações → Unimed Promoção) é o ID certo; confirme que o CF7 envia via Ajax (sem erros de JS).
- QR não aparece: confira token/ID da promoção nas Configurações; veja o log do plugin para mensagens da API.
- PNG não gerado: garanta que `wp-content/uploads/abgsoft-uploads/` existe e é gravável.
- Sem anexo no e-mail: inclua `[file-682]` em “Arquivos anexos” do CF7.

## Resumo

- Envie a pasta correta do plugin.
- Ative o plugin.
- Crie a pasta uploads/abgsoft-uploads.
- Configure Token, ID CF7 e ID Promoção em Configurações → Unimed Promoção.
- Crie o formulário CF7 com os campos exigidos.
- Teste o envio e valide o modal/QR, arquivo e logs.