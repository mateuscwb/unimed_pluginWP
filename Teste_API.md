# Teste da API AutoPark (Postman)

Guia rápido para validar a API utilizada pelo plugin "Unimed Promoção" usando o Postman.


## Pré-requisitos
- Postman instalado (desktop ou web).
- Token de API válido da AutoPark.

Arquivos úteis no projeto:
- Coleção Postman: `Autopark-Unimed.postman_collection.json`


## Passo a passo

### 1) Importar a coleção
1. Abra o Postman → Import.
2. Selecione o arquivo `Autopark-Unimed.postman_collection.json` (raiz do projeto).

### 2) (Opcional) Criar um Environment
Crie um Environment e configure as variáveis (ou edite direto na requisição):
- `baseUrl` → `https://sys.autopark.com.br/api/v1`
- `api_token` → seu token (obrigatório)
- `promotion_id` → ex.: `1`
- `name` → ex.: `Fulano de Tal`
- `cpf` → ex.: `00000000000`
- `email` → ex.: `fulano@example.com`
- `license_plate` → ex.: `ABC1D23`
- `branch_id` → ID da unidade (ex.: `1`)

Selecione este Environment no topo do Postman antes de enviar.

### 3) Enviar a requisição
1. Abra a requisição da coleção: "Emitir voucher (POST /vouchers)".
2. Verifique Headers:
   - `Content-Type: application/json`
   - `X-Requested-With: XMLHttpRequest`
3. Body (JSON) deve estar assim (as variáveis serão resolvidas pelo Postman):
```json
{
  "api_token": "{{api_token}}",
  "promotion_id": {{promotion_id}},
  "name": "{{name}}",
  "cpf": "{{cpf}}",
  "email": "{{email}}",
  "license_plate": "{{license_plate}}",
  "branch_id": "{{branch_id}}"
}
```
4. Clique em Send.

### 4) Resultado esperado
- Status 200/201 com JSON contendo pelo menos:
```json
{
  "qrcode": "..."
  // outros campos que a API possa retornar
}
```
- O valor `qrcode` (string) é utilizado pelo plugin para gerar a imagem PNG do QR Code.


## Possíveis códigos de status e causas
- 200/201 OK/Created → Voucher emitido; resposta contém `qrcode`.
- 400 Bad Request → Erro de formato/JSON inválido ou campos faltantes.
- 401 Unauthorized → `api_token` inválido/ausente.
- 403 Forbidden → Credenciais sem permissão para esta operação.
- 404 Not Found → Rota incorreta (verifique `baseUrl` e caminho `/vouchers`).
- 422 Unprocessable Entity → Validação falhou (ex.: `promotion_id` ou `branch_id` inválidos, CPF inválido, etc.).
- 429 Too Many Requests → Limite de requisições atingido (aguarde e tente novamente).
- 500 Internal Server Error → Erro no servidor da API (tente novamente; se persistir, contatar suporte).
- Timeout → Problema de rede/firewall. Verifique conectividade e tente novamente.


## Dicas de troubleshooting
- Confirme `api_token` e `promotion_id` corretos.
- Teste outro `branch_id` (unidade) válido.
- Revise o JSON no Body (modo RAW + JSON válido, sem trailing commas).
- Veja a aba "Console" do Postman para erros adicionais.


## Teste via curl (alternativa)
Se preferir testar por terminal, substitua as variáveis e rode:
```bash
curl -X POST "https://sys.autopark.com.br/api/v1/vouchers" \
  -H "Content-Type: application/json" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d '{
    "api_token": "SEU_TOKEN_AQUI",
    "promotion_id": 1,
    "name": "Fulano de Tal",
    "cpf": "00000000000",
    "email": "fulano@example.com",
    "license_plate": "ABC1D23",
    "branch_id": "1"
  }'
```


## Observações de segurança
- Não compartilhe seu `api_token` publicamente.
- Prefira usar Environments no Postman para esconder o token.
- Rotacione o `api_token` periodicamente conforme a política interna.


## Relacionamento com o plugin
- O plugin envia exatamente estes campos à API.
- Se a resposta contiver `qrcode`, o plugin gerará o PNG e exibirá em modal após o envio do CF7.
