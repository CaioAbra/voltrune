# Arquitetura Do Solar

## Estrutura Geral

O Solar vive dentro da aplicacao Laravel como um modulo de produto isolado.

Principais caminhos de backend:

- [app/Modules/Solar](/d:/projects/voltrune/app/Modules/Solar)

Principais caminhos de frontend:

- [resources/views/solar](/d:/projects/voltrune/resources/views/solar)
- [resources/scss/pages/_solar.scss](/d:/projects/voltrune/resources/scss/pages/_solar.scss)
- [resources/js/app.js](/d:/projects/voltrune/resources/js/app.js)

Migrations do modulo:

- [database/migrations/solar](/d:/projects/voltrune/database/migrations/solar)

## Camadas Arquiteturais

### Rotas

As rotas do Solar ficam protegidas por:

- `auth`
- `company.active`
- `product:solar`

Referencia:

- [routes/solar.php](/d:/projects/voltrune/routes/solar.php)

Comportamento atual:

- local: rotas sob `/solar`
- producao: rotas sob `SOLAR_DOMAIN`

O registro central desse comportamento acontece em:

- [bootstrap/app.php](/d:/projects/voltrune/bootstrap/app.php)

### Controllers

Os controllers orquestram views e persistencia, enquanto a regra de negocio fica concentrada em services.

Controllers principais:

- `SolarDashboardController`
- `CustomerController`
- `ProjectController`
- `SolarCompanySettingController`
- `SimulationController`
- `QuoteController`

Referencia principal:

- [ProjectController.php](/d:/projects/voltrune/app/Modules/Solar/Controllers/ProjectController.php)

Por que isso importa:

- projeto concentra contexto do cliente e local
- simulacao concentra leitura tecnica e comercial
- orcamento concentra composicao real da proposta

### Models

Modelos centrais:

- `SolarCustomer`
- `SolarProject`
- `SolarSimulation`
- `SolarCompanySetting`
- `SolarMarketDefault`
- `EnergyUtility`
- `SolarQuote`
- `SolarQuoteItem`

Papel de cada grupo:

- `SolarCustomer` guarda contexto comercial do cliente
- `SolarProject` guarda o contexto da instalacao e os dados base
- `SolarSimulation` guarda cenarios tecnico-comerciais derivados do projeto
- `SolarCompanySetting` guarda defaults da empresa
- `SolarMarketDefault` sustenta fallback comercial
- `EnergyUtility` ajuda na resolucao da concessionaria
- `SolarQuote` e `SolarQuoteItem` sustentam a proposta comercial vinda da simulacao

### Services

A camada de services concentra as regras operacionais.

Principais services:

- [SolarSizingService.php](/d:/projects/voltrune/app/Modules/Solar/Services/SolarSizingService.php)
- [SolarGeocodingService.php](/d:/projects/voltrune/app/Modules/Solar/Services/SolarGeocodingService.php)
- [SolarRadiationService.php](/d:/projects/voltrune/app/Modules/Solar/Services/SolarRadiationService.php)
- [EnergyUtilityResolverService.php](/d:/projects/voltrune/app/Modules/Solar/Services/EnergyUtilityResolverService.php)
- [SolarNavigationService.php](/d:/projects/voltrune/app/Modules/Solar/Services/SolarNavigationService.php)

Essa separacao e intencional:

- controllers ficam legiveis
- calculos ficam testaveis
- evolucoes de UI nao exigem reescrita da regra central

## Posicionamento Funcional Atual

O Solar segue hoje este fluxo conceitual:

1. `SolarCustomer`
2. `SolarProject`
3. `SolarSimulation`
4. `SolarQuote`

Por que isso importa:

- projeto deixou de ser o objeto comercial final
- multiplos cenarios podem coexistir para o mesmo contexto
- orcamentos evoluem a partir de uma simulacao especifica

## Estrategia De Compatibilidade

Durante a evolucao do modulo:

- `SolarProject` ainda preserva snapshot de parte do fluxo legado
- `SolarSimulation` foi fortalecida como entidade propria
- o primeiro cenario continua funcionando como simulacao principal durante a transicao

Essa escolha foi deliberada para evitar ruptura no fluxo existente.

## Publicacao Em Producao

O Solar agora opera como subdominio proprio em producao:

- `solar.voltrune.com`

Mas continua simples no desenvolvimento local:

- `http://127.0.0.1:8000/solar`

Esse padrao foi adotado para:

- evitar complexidade de DNS em ambiente local
- manter separacao clara entre produtos em producao
- preservar sessao compartilhada com o Hub

Guia operacional relacionado:

- [Publicacao multi-subdominio](../shared/publicacao-multi-subdominio.md)
