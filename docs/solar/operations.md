# Operacao E Comandos

## Dependencias Principais Em Runtime

Hoje o Solar depende de:

- runtime da aplicacao Laravel
- conexao `solar_mysql`
- ViaCEP para enriquecimento de CEP
- Nominatim/OpenStreetMap para geocodificacao
- PVGIS para fator solar
- dados publicos ANEEL + IBGE para sincronizacao de concessionarias

## Comandos Importantes

### Rodar migrations do Solar

Comando:

`php artisan voltrune:migrate-solar`

Por que importa:

- mantem o schema do Solar isolado do restante da plataforma

Referencia:

- [MigrateSolarCommand.php](/d:/projects/voltrune/app/Console/Commands/MigrateSolarCommand.php)

### Seed ou sync de concessionarias

Comando:

`php artisan voltrune:seed-solar-utilities`

Comportamento:

- tenta sincronizacao nacional primeiro
- se necessario, cai para seed local

Referencia:

- [SeedSolarUtilitiesCommand.php](/d:/projects/voltrune/app/Console/Commands/SeedSolarUtilitiesCommand.php)

### Sync nacional de concessionarias

Comando:

`php artisan voltrune:sync-solar-utilities-national --prune`

Por que importa:

- mantem o catalogo de concessionarias alinhado com dados publicos
- reduz divergencias entre cidade, estado e sugestao de concessionaria

Referencia:

- [SyncSolarUtilitiesNationalCommand.php](/d:/projects/voltrune/app/Console/Commands/SyncSolarUtilitiesNationalCommand.php)

### Backfill de simulacoes padrao

Comando:

`php artisan solar:backfill-project-simulations`

Por que importa:

- cria a simulacao padrao para projetos legados
- apoia a transicao do fluxo centrado em projeto para o fluxo centrado em simulacao

Referencia:

- [BackfillSolarProjectSimulationsCommand.php](/d:/projects/voltrune/app/Console/Commands/BackfillSolarProjectSimulationsCommand.php)

## Validacoes Automatizadas Relevantes

Testes que protegem a camada critica do Solar:

- [SolarSizingServiceTest.php](/d:/projects/voltrune/tests/Unit/SolarSizingServiceTest.php)
- [SolarGeocodingServiceTest.php](/d:/projects/voltrune/tests/Unit/SolarGeocodingServiceTest.php)
- [SolarRadiationServiceTest.php](/d:/projects/voltrune/tests/Unit/SolarRadiationServiceTest.php)
- [SolarSimulationServiceTest.php](/d:/projects/voltrune/tests/Unit/SolarSimulationServiceTest.php)
- [EnergyUtilityResolverServiceTest.php](/d:/projects/voltrune/tests/Unit/EnergyUtilityResolverServiceTest.php)
- [ProjectControllerLocationPreparationTest.php](/d:/projects/voltrune/tests/Unit/ProjectControllerLocationPreparationTest.php)

Por que esses testes importam:

- sizing e o nucleo comercial
- geocodificacao influencia a qualidade do fator solar
- fator solar influencia a credibilidade do dimensionamento
- resolucao de concessionaria influencia coerencia do contexto do projeto

## Operacao De Publicacao Em Producao

### Dominios atuais

Em producao, a Voltrune opera hoje com:

- `voltrune.com` para o site publico
- `hub.voltrune.com` para o Hub
- `solar.voltrune.com` para o Solar

Em ambiente local:

- site publico na raiz
- Hub em `/hub`
- Solar em `/solar`

### Build do frontend

O build e unico para toda a aplicacao.

Comando:

```bash
npm run build
```

Saida:

- `public/build`

Nao existe build separado dentro de `/solar`.

### Publicacao do subdominio Solar

Na Hostoo/cPanel, o subdominio Solar foi publicado em uma pasta publica propria:

- `~/public_html/solar`

Arquivos necessarios:

- `index.php`
- `.htaccess`

Links simbolicos necessarios para assets:

```bash
ln -s ../build build
ln -s ../favicon.ico favicon.ico
ln -s ../images images
ln -s ../robots.txt robots.txt
ln -s ../sitemap.xml sitemap.xml
```

Sem esses links:

- a pagina pode carregar em HTML puro
- mas CSS, JS e outros arquivos publicos nao serao entregues

### Limpeza de cache apos deploy

Depois de alterar dominio, subdominio, rotas ou configuracao, a sequencia segura e:

```bash
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

### Checklist rapido de validacao

1. Confirmar `.env` de producao com `ROOT_DOMAIN`, `HUB_DOMAIN`, `SOLAR_DOMAIN` e `SESSION_DOMAIN`.
2. Confirmar `php artisan route:list`.
3. Confirmar `https://solar.voltrune.com/build/manifest.json`.
4. Confirmar login compartilhado entre Hub e Solar.
5. Confirmar dashboard, projetos, simulacoes e orcamentos do Solar.

Guia detalhado relacionado:

- [Publicacao multi-subdominio](../shared/publicacao-multi-subdominio.md)

## Ordem Segura De Manutencao

Quando for mexer no Solar, a ordem mais segura de atencao costuma ser:

1. services
2. contrato do controller
3. rotas e publicacao
4. views
5. interacoes frontend
6. styling

Por que:

- regressao visual aparece rapido
- regressao em service altera resultado comercial
- regressao em rota/publicacao pode derrubar o produto inteiro em producao
