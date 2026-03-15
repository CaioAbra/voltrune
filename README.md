# Voltrune

Sistema da Voltrune construido em Laravel, com tres frentes principais no mesmo repositorio:

- site institucional e LPs de captacao
- Hub autenticado para operacao da conta e administracao
- Solar, produto operacional para fluxo comercial e tecnico de projetos fotovoltaicos

O projeto combina Blade, SCSS e Vite com uma linguagem visual premium e, nas ultimas rodadas, consolidou um padrao mais forte de responsividade, hierarquia de leitura e consistencia entre modulos.

## Stack

- PHP 8.2+
- Laravel 12
- Blade
- SCSS compilado com Vite
- SQLite por padrao no ambiente local

## Modulos Do Sistema

### Site institucional e LPs

Responsavel pelas paginas publicas da marca, captacao de leads e apresentacao comercial dos servicos e sistemas.

Rotas principais:

- `/`
- `/servicos`
- `/portfolio`
- `/sistemas`
- `/sistemas/solar`
- `/contato`
- `/vigilante`
- `/sistemas/vigilante`
- `/portal`

Fluxos com `POST`:

- `POST /contato`
- `POST /contato/prefill`
- `POST /vigilante`

### Hub

Ambiente autenticado para usuarios da plataforma, com area de conta, produtos, cobranca, ajuda e area administrativa.

Arquivos-base:

- [routes/hub.php](c:/Users/terc.caio.abra_g4edu/Documents/Projects/voltrune/routes/hub.php)
- [resources/views/hub](c:/Users/terc.caio.abra_g4edu/Documents/Projects/voltrune/resources/views/hub)
- [resources/scss/pages/_hub.scss](c:/Users/terc.caio.abra_g4edu/Documents/Projects/voltrune/resources/scss/pages/_hub.scss)

Entradas principais:

- `/login`
- `/register`
- `/dashboard`
- `/products`
- `/account`
- `/billing`
- `/help`
- `/admin`

### Solar

Produto autenticado para operacao comercial de energia solar, com fluxo de projeto, simulacao e orcamento.

Arquivos-base:

- [routes/solar.php](c:/Users/terc.caio.abra_g4edu/Documents/Projects/voltrune/routes/solar.php)
- [resources/views/solar](c:/Users/terc.caio.abra_g4edu/Documents/Projects/voltrune/resources/views/solar)
- [resources/scss/pages/_solar.scss](c:/Users/terc.caio.abra_g4edu/Documents/Projects/voltrune/resources/scss/pages/_solar.scss)

Fluxo principal:

1. cadastrar ou abrir projeto
2. revisar dados do cliente, local e consumo
3. criar ou abrir simulacao
4. analisar cenario tecnico-comercial
5. gerar orcamento
6. montar proposta e avancar status comercial

## Diretrizes Recentes De UX E Responsividade

As ultimas rodadas reforcaram um padrao unico para o sistema:

- layouts respondem a largura util real da viewport, nao apenas a resolucao nominal do monitor
- grids de cards priorizam `auto-fit` e `minmax(...)` em vez de colunas rigidas em faixas intermediarias
- estados com um unico card usam ocupacao controlada para preservar leitura
- titulos, metricas e valores financeiros quebram de forma mais equilibrada
- botoes mantem hierarquia clara entre acao principal, secundaria e terciaria
- Solar, Hub e paginas publicas compartilham uma base mais coerente de densidade visual e leitura

## Estrutura Principal

- [app/Http/Controllers](c:/Users/terc.caio.abra_g4edu/Documents/Projects/voltrune/app/Http/Controllers): controllers HTTP do site, Hub e Solar
- [routes/web.php](c:/Users/terc.caio.abra_g4edu/Documents/Projects/voltrune/routes/web.php): rotas publicas e LPs
- [routes/hub.php](c:/Users/terc.caio.abra_g4edu/Documents/Projects/voltrune/routes/hub.php): rotas do Hub
- [routes/solar.php](c:/Users/terc.caio.abra_g4edu/Documents/Projects/voltrune/routes/solar.php): rotas do Solar
- [resources/views](c:/Users/terc.caio.abra_g4edu/Documents/Projects/voltrune/resources/views): views Blade
- [resources/scss](c:/Users/terc.caio.abra_g4edu/Documents/Projects/voltrune/resources/scss): base visual, componentes e estilos por modulo
- [docs](c:/Users/terc.caio.abra_g4edu/Documents/Projects/voltrune/docs): documentacao funcional, estrutural e de UX

## Configuracao Local

1. Instale as dependencias de PHP:

```bash
composer install
```

2. Instale as dependencias de front-end:

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

4. Gere a chave da aplicacao:

```bash
php artisan key:generate
```

5. Rode as migrations da infraestrutura local e dos bancos do produto:

```bash
php artisan migrate --database=sqlite
php artisan voltrune:migrate-hub
php artisan voltrune:migrate-solar
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

## Build E Testes

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

## Variaveis De Ambiente Relevantes

Alem das variaveis padrao do Laravel, este projeto depende especialmente destas:

- `APP_URL`
- `WHATSAPP_URL`
- `PORTAL_REDIRECT_URL`
- `CONTACT_INBOX_ADDRESS`
- `MAIL_*`
- `GA_MEASUREMENT_ID`
- `META_PIXEL_ID`

No fluxo do Solar e de publicacao por dominio/subdominio, tambem vale revisar com cuidado as variaveis e configuracoes de dominio usadas pelo ambiente atual.

## Documentacao

Pontos de entrada recomendados:

- [docs/README.md](c:/Users/terc.caio.abra_g4edu/Documents/Projects/voltrune/docs/README.md)
- [docs/system-overview.md](c:/Users/terc.caio.abra_g4edu/Documents/Projects/voltrune/docs/system-overview.md)
- [docs/solar/ui-ux.md](c:/Users/terc.caio.abra_g4edu/Documents/Projects/voltrune/docs/solar/ui-ux.md)
- [docs/solar/organizacao-das-telas.md](c:/Users/terc.caio.abra_g4edu/Documents/Projects/voltrune/docs/solar/organizacao-das-telas.md)
- [docs/solar/current-state.md](c:/Users/terc.caio.abra_g4edu/Documents/Projects/voltrune/docs/solar/current-state.md)

## Observacoes

- O projeto esta em portugues e a comunicacao da marca usa a narrativa de produto premium e operacao comercial guiada.
- O repositorio ja nao corresponde ao escopo de um site Laravel simples; ele concentra site, Hub e produto Solar.
- Mudancas visuais devem preservar a hierarquia de leitura, a consistencia entre modulos e o comportamento responsivo em larguras intermediarias.
