# Voltrune

Site institucional da Voltrune, construído em Laravel, com foco em captação de leads para serviços de websites, apps, mídia, branding e hospedagem.

O projeto usa uma linguagem visual premium com páginas estáticas em Blade, formulário de contato com envio por e-mail e uma landing dedicada ao produto "Vigilante Jurídico".

## Stack

- PHP 8.2+
- Laravel 12
- Blade
- SCSS compilado com Vite
- SQLite por padrão no ambiente local

## Páginas e fluxos

- `/`: home institucional
- `/servicos`: apresentação dos serviços
- `/portfolio`: vitrine de cases e resultados
- `/sistemas`: página de sistemas
- `/contato`: formulário principal de orçamento
- `/portal`: redireciona para `PORTAL_REDIRECT_URL` se configurado; caso contrário, exibe página local
- `/vigilante` e `/sistemas/vigilante`: landing do Vigilante Jurídico

Fluxos com POST:

- `POST /contato`: valida dados, registra log e tenta enviar e-mail para a caixa configurada
- `POST /vigilante`: registra interesse no produto e salva log da submissão

Os formulários usam:

- proteção antispam com honeypot (`company_website`)
- rate limit com `throttle:6,1`
- feedback por flash message após envio

## Estrutura principal

- [app/Http/Controllers/ContactController.php](C:/Users/terc.caio.abra_g4edu/Documents/Projects/voltrune/app/Http/Controllers/ContactController.php): processamento do formulário de contato
- [app/Http/Controllers/VigilanteLeadController.php](C:/Users/terc.caio.abra_g4edu/Documents/Projects/voltrune/app/Http/Controllers/VigilanteLeadController.php): captura de interesse no Vigilante
- [routes/web.php](C:/Users/terc.caio.abra_g4edu/Documents/Projects/voltrune/routes/web.php): rotas públicas do site
- [resources/views](C:/Users/terc.caio.abra_g4edu/Documents/Projects/voltrune/resources/views): páginas e componentes Blade
- [resources/scss](C:/Users/terc.caio.abra_g4edu/Documents/Projects/voltrune/resources/scss): base visual, componentes e estilos por página

## Configuração local

1. Instale as dependências de PHP:

```bash
composer install
```

2. Instale as dependências de front-end:

```bash
npm install
```

3. Crie o arquivo de ambiente:

```bash
cp .env.example .env
```

No Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

4. Gere a chave da aplicação:

```bash
php artisan key:generate
```

5. Rode as migrations:

```bash
php artisan migrate
```

6. Suba o ambiente de desenvolvimento:

```bash
composer run dev
```

Esse comando inicia:

- servidor Laravel
- worker de fila
- monitor de logs com Pail
- Vite em modo watch

## Build e testes

Build de assets:

```bash
npm run build
```

Rodar testes:

```bash
composer test
```

Setup automatizado inicial:

```bash
composer run setup
```

## Variáveis de ambiente relevantes

Além das variáveis padrão do Laravel, este projeto depende especialmente destas:

- `APP_URL`: URL base da aplicação
- `WHATSAPP_URL`: link usado nos CTAs de WhatsApp
- `PORTAL_REDIRECT_URL`: se preenchida, `/portal` redireciona para essa URL
- `CONTACT_INBOX_ADDRESS`: caixa que recebe os envios do formulário de contato
- `MAIL_*`: credenciais e remetente para envio de e-mail
- `GA_MEASUREMENT_ID`: ativa o snippet do Google Analytics
- `META_PIXEL_ID`: ativa o snippet do Meta Pixel

## E-mail de contato

O formulário de contato:

- valida nome, e-mail, WhatsApp, assunto e mensagem
- registra a submissão via `Log::info`
- tenta enviar um e-mail em texto puro usando `Mail::raw`
- em caso de falha no envio, mantém o fluxo do usuário e registra erro em log

Para testar localmente sem envio real, use um mailer de desenvolvimento ou mantenha o driver configurado de forma segura para ambiente local.

## Observações

- O projeto está em português e a comunicação da marca usa a narrativa "ordem de artesãos digitais".
- O layout e a identidade visual foram customizados; o `README` original padrão do Laravel não representa mais o escopo deste repositório.
- O nome e a descrição em [composer.json](C:/Users/terc.caio.abra_g4edu/Documents/Projects/voltrune/composer.json) ainda estão com os valores padrão do skeleton Laravel. Se quiser, isso pode ser ajustado depois para refletir a Voltrune também.
