# Documentacao Do Modulo Solar

## Visao Geral

O modulo Solar da Voltrune atende o fluxo comercial e operacional de instaladores solares.

Hoje ele cobre:

- clientes
- projetos
- simulacoes
- orcamentos
- itens de orcamento
- geocodificacao e fator solar
- pre-dimensionamento
- custos e indicadores financeiros

O objetivo desta documentacao e registrar o que ja existe no codigo, como as telas se organizam e quais decisoes estruturais orientam a evolucao do produto.

## Principios Do Modulo

- valor rapido para operacao comercial
- automacao com ajuste manual
- separacao clara entre contexto, cenario e proposta
- degradacao segura quando servicos externos falham

## Estrutura Recomendada Do Fluxo

1. Projeto: contexto do cliente e do local.
2. Simulacao: leitura do cenario tecnico/comercial.
3. Orcamento: montagem da proposta com materiais, servicos e margem.

## Guias Disponiveis

- [Arquitetura](./architecture.md)
- [Modelo de dados](./data-model.md)
- [Fluxos de automacao](./automation-flows.md)
- [UI e UX](./ui-ux.md)
- [Organizacao das telas](./organizacao-das-telas.md)
- [Operacao e comandos](./operations.md)
- [Estado atual](./current-state.md)

## Referencias Principais

- Rotas: [solar.php](/d:/projects/voltrune/routes/solar.php)
- Projeto: [ProjectController.php](/d:/projects/voltrune/app/Modules/Solar/Controllers/ProjectController.php)
- Simulacao: [SimulationController.php](/d:/projects/voltrune/app/Modules/Solar/Controllers/SimulationController.php)
- Orcamento: [QuoteController.php](/d:/projects/voltrune/app/Modules/Solar/Controllers/QuoteController.php)
- Servico de simulacao: [SolarSimulationService.php](/d:/projects/voltrune/app/Modules/Solar/Services/SolarSimulationService.php)
- UI do Solar: [_solar.scss](/d:/projects/voltrune/resources/scss/pages/_solar.scss)
