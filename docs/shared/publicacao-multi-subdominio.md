# Publicacao Multi-Subdominio

## Objetivo

Este guia registra o padrao usado para publicar site, Hub e produtos SaaS no mesmo projeto Laravel, com subdominios separados em producao e rotas simplificadas em ambiente local.

Ele foi consolidado durante a publicacao do modulo Solar em:

- `solar.voltrune.com`

O mesmo padrao pode ser reaproveitado no futuro para outros produtos SaaS.

## Estrategia Atual

A plataforma usa um unico projeto Laravel com tres camadas publicas principais:

- `voltrune.com` para o site publico
- `hub.voltrune.com` para o Hub
- `solar.voltrune.com` para o produto Solar

Em ambiente local:

- site publico na raiz
- Hub em `/hub`
- Solar em `/solar`

## Variaveis De Ambiente

As variaveis centrais para esse padrao sao:

```env
APP_URL=https://voltrune.com
ROOT_DOMAIN=voltrune.com

HUB_DOMAIN=hub.voltrune.com
HUB_URL=https://hub.voltrune.com

SOLAR_DOMAIN=solar.voltrune.com
SOLAR_URL=https://solar.voltrune.com

SESSION_DOMAIN=.voltrune.com
```

Observacoes:

- `ROOT_DOMAIN` controla as rotas do site publico em producao
- `HUB_DOMAIN` controla as rotas do Hub em producao
- `SOLAR_DOMAIN` controla as rotas do Solar em producao
- `SESSION_DOMAIN=.voltrune.com` permite compartilhar sessao entre Hub e produtos

## Arquivos De Codigo Envolvidos

### Bootstrap de rotas

Arquivo:

- [bootstrap/app.php](/d:/projects/voltrune/bootstrap/app.php)

Responsabilidade:

- registrar `routes/web.php`
- registrar `routes/hub.php`
- registrar `routes/solar.php`
- alternar entre prefixo local e dominio dedicado em producao

Padrao atual:

- local:
  - Hub em `/hub`
  - Solar em `/solar`
- producao:
  - Hub em `HUB_DOMAIN`
  - Solar em `SOLAR_DOMAIN`

### Rotas do site publico

Arquivo:

- [routes/web.php](/d:/projects/voltrune/routes/web.php)

Responsabilidade:

- restringir o site publico ao `ROOT_DOMAIN` em producao
- evitar que `hub.voltrune.com` e `solar.voltrune.com` caiam na home institucional

### Rotas do produto

Arquivo:

- [routes/solar.php](/d:/projects/voltrune/routes/solar.php)

Responsabilidade:

- manter `/solar` em local
- usar `SOLAR_DOMAIN` em producao

## Publicacao De Assets

O build do frontend continua unico.

Comando:

```bash
npm run build
```

Saida:

- `public/build`

Isso significa:

- nao existe build separado por subdominio
- Hub e Solar reutilizam o mesmo build compilado

## Estrutura Publica Na Hospedagem

Na Hostoo/cPanel, cada subdominio aponta para uma pasta publica propria.

Exemplo adotado:

- `~/public_html/hub`
- `~/public_html/solar`

Essas pastas nao duplicam o projeto inteiro. Elas funcionam como uma ponte para a pasta `public` real do Laravel.

Arquivos minimos necessarios:

- `index.php`
- `.htaccess`

Links simbolicos recomendados para assets:

```bash
ln -s ../build build
ln -s ../favicon.ico favicon.ico
ln -s ../images images
ln -s ../robots.txt robots.txt
ln -s ../sitemap.xml sitemap.xml
```

Sem esses links:

- o HTML pode carregar
- mas CSS, JS e arquivos publicos podem nao ser entregues

## Sequencia De Validacao Em Producao

1. Confirmar `.env` com `ROOT_DOMAIN`, `HUB_DOMAIN`, `SOLAR_DOMAIN` e `SESSION_DOMAIN`.
2. Subir o codigo novo.
3. Limpar cache do Laravel:

```bash
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

4. Confirmar rotas em producao:

```bash
php artisan route:list
```

Esperado:

- `voltrune.com/` para o site
- `hub.voltrune.com/...` para o Hub
- `solar.voltrune.com/...` para o Solar

5. Confirmar assets:

- `https://solar.voltrune.com/build/manifest.json`
- `https://solar.voltrune.com/build/assets/...`

6. Confirmar sessao compartilhada:

- login no Hub
- acesso ao Solar sem nova autenticacao

## Erros Reais Encontrados Durante A Publicacao Do Solar

### 1. Solar registrado no dominio do Hub

Causa:

- `bootstrap/app.php` ainda registrava as rotas do Solar com `HUB_DOMAIN`

Efeito:

- links do Solar apontavam para `hub.voltrune.com/solar`

Correcao:

- separar claramente `HUB_DOMAIN` e `SOLAR_DOMAIN`

### 2. Loop de redirecionamento no Solar

Causa:

- a raiz global redirecionava `solar.voltrune.com` para `solar.dashboard`
- mas `solar.dashboard` ja era a propria raiz do subdominio

Efeito:

- `ERR_TOO_MANY_REDIRECTS`

Correcao:

- remover esse redirecionamento da rota publica global

### 3. Home institucional carregando no Solar

Causa:

- `routes/web.php` estava global, sem restricao ao `ROOT_DOMAIN`

Efeito:

- `solar.voltrune.com/` caia na home do marketing

Correcao:

- restringir `routes/web.php` ao dominio raiz em producao

### 4. Solar sem CSS e JS

Causa:

- a pasta publica do subdominio Solar nao tinha acesso aos assets em `public/build`

Efeito:

- pagina carregava sem estilo

Correcao:

- criar links simbolicos para `build` e demais arquivos publicos, como ja existia no Hub

## Como Replicar Para Um Novo SaaS

Para um novo produto, por exemplo `crm.voltrune.com`, a sequencia recomendada e:

1. criar `CRM_DOMAIN` e `CRM_URL` no `.env`
2. criar arquivo de rotas do produto
3. registrar as rotas no `bootstrap/app.php`
4. usar prefixo simplificado em local
5. usar dominio dedicado em producao
6. garantir `SESSION_DOMAIN=.voltrune.com`
7. criar pasta publica do subdominio com:
   - `index.php`
   - `.htaccess`
   - links simbolicos para `build` e arquivos publicos compartilhados
8. validar assets, login e navegacao cruzada com o Hub

Esse e o padrao oficial atual para expansao multiproduto da Voltrune.
