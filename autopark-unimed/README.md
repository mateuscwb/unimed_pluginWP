# Plugin WordPress - Unimed Promoção

## Descrição
Plugin WordPress personalizado desenvolvido pela Abrigo Software para gerenciar promoções de estacionamento AutoPark para Unimed. O plugin integra-se ao sistema AutoPark para geração de vouchers promocionais via QR Code.

## Funcionalidades Principais

1. **Integração com Contact Form 7**: Captura de dados do formulário para geração de vouchers.
2. **Geração de QR Code**: Criação de códigos QR para acesso ao estacionamento.
3. **API Integration**: Comunicação com a API do AutoPark para validação e registro dos vouchers.
4. **Interface Modal**: Exibição do QR Code gerado em um modal após envio bem-sucedido do formulário.
5. **Funcionalidade de Download**: Permite ao usuário baixar o QR Code gerado.
6. **Envio por Email**: Envio automático do voucher para o email cadastrado.
7. **Registro de Logs**: Sistema de logs para rastreamento de atividades e depuração.

## Dependências

### Dependências externas
1. **WordPress**: Plataforma base (versão compatível com Contact Form 7).
2. **Contact Form 7**: Plugin de formulário para WordPress (requerido para captura de dados).
3. **jQuery**: Utilizado para manipulação do DOM e funcionamento do modal.

### Dependências internas
1. **phpqrcode.inc**: Biblioteca PHP para geração de QR Codes.
2. **jquery.modal.min.js**: Biblioteca JavaScript para exibição de janelas modais.

## Estrutura de Arquivos

```
abgsoft-autopark-unimed/
│
├── abgsoft-autopark-unimed.php    # Arquivo principal do plugin
├── js/
│   ├── jquery.modal.min.js        # Biblioteca para modais
│   ├── jquery.modal.min.css       # Estilos para modais
│   └── scripts.js                 # Scripts personalizados do plugin
│
├── libs/
│   └── phpqrcode.inc              # Biblioteca para geração de QR Code
│
└── log/                           # Diretório para armazenamento de logs
```

## Fluxo de Funcionamento

1. O usuário preenche um formulário (Contact Form 7) com dados pessoais, veículo e unidade.
2. Ao enviar o formulário, o plugin intercepta os dados através do hook `wpcf7_before_send_mail`.
3. Os dados são processados e enviados para a API do AutoPark via função `consume_api()`.
4. A API retorna um código de voucher.
5. O plugin gera um QR Code baseado neste voucher utilizando a biblioteca phpqrcode.
6. O QR Code é armazenado no servidor e também codificado em base64.
7. O código base64 é enviado para o frontend através do filtro `wpcf7_ajax_json_echo`.
8. Após o envio bem-sucedido do formulário, o script JavaScript exibe um modal com o QR Code.
9. O usuário pode visualizar e fazer download do QR Code.

## Requisitos Técnicos

1. Servidor com suporte a PHP (versão compatível com WordPress atualizado).
2. WordPress atualizado.
3. Plugin Contact Form 7 instalado e configurado.
4. Formulário com ID 1703 configurado com os campos necessários:
   - `nome`: Nome do usuário
   - `cpf`: CPF do usuário
   - `email`: Email do usuário
   - `placa`: Placa do veículo
   - `unidade`: Unidade Unimed (pode ser array ou string)
   - `file-682`: Campo para upload do arquivo QR Code (usado internamente)
5. Diretório de upload com permissões de escrita: `wp-content/uploads/abgsoft-uploads/`.
6. Conexão com internet para comunicação com a API do AutoPark.

## Configuração da API

O plugin utiliza um token fixo para autenticação com a API do AutoPark:
- Token: Configurado internamente
- URL da API: "https://sys.autopark.com.br/api/v1/vouchers"
- ID de promoção: 1 (definido como valor fixo no código)

## Observações Importantes

1. O plugin depende de uma estrutura específica do formulário Contact Form 7.
2. O token de API está codificado diretamente no plugin (hardcoded).
3. O ID de promoção está definido como valor fixo (1).
4. Os vouchers gerados são válidos por 30 dias e para uso único.
