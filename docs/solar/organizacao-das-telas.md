# Organizacao Das Telas Do Solar

## Objetivo

Este documento descreve o papel de cada tela principal do modulo Solar apos a rodada de organizacao estrutural e refinamento de UX.

O foco desta etapa foi:

- reforcar responsabilidades por tela
- reduzir densidade visual
- deixar o proximo passo mais claro para o usuario
- manter a arquitetura existente
- nao alterar a logica de calculo

## Papel De Cada Tela

### Projeto

A tela de projeto deve responder:

- quem e o cliente
- onde sera a instalacao
- qual e o consumo base
- quais simulacoes e orcamentos existem para esse contexto

O projeto nao e a tela principal de analise comercial.

Ele funciona como base operacional do local.

Prioridades visuais:

- cliente
- endereco e localizacao
- consumo
- lista de simulacoes
- lista de orcamentos

Acoes principais:

- voltar para projetos
- nova simulacao
- novo orcamento
- editar projeto

### Simulacao

A tela de simulacao deve responder:

- qual e o cenario tecnico/comercial atual
- qual sistema foi sugerido
- qual geracao e fator solar foram usados
- qual preco e economia foram estimados
- se o cenario ja esta pronto para virar orcamento

Essa e a tela principal de leitura e decisao do cenario.

Prioridades visuais:

- potencia
- geracao
- preco sugerido
- economia mensal
- payback
- ROI

Acoes principais:

- voltar ao projeto
- duplicar simulacao
- gerar orcamento
- ajustar base do cenario

Observacao:

- nesta rodada nao foi criado um editor dedicado de simulacao
- quando o usuario precisa alterar a base do cenario, o fluxo continua passando pelo projeto

### Orcamento

A tela de orcamento deve responder:

- qual proposta esta sendo montada
- quais materiais e servicos foram adicionados
- qual e o custo total
- qual e o preco final
- qual e o lucro bruto
- qual e a margem

Essa e a tela principal para preparar proposta comercial.

Prioridades visuais:

- resumo financeiro do orcamento
- itens da proposta
- formulario para adicionar item
- acoes de status comercial

Acoes principais:

- voltar a simulacao
- adicionar item
- duplicar orcamento
- gerar proposta
- marcar como enviado

## Fluxo Recomendado

O fluxo recomendado do Solar fica:

1. cadastrar ou abrir o projeto
2. validar cliente, local e consumo
3. abrir ou criar simulacao
4. analisar o cenario tecnico/comercial
5. gerar orcamento
6. montar itens e revisar margem
7. marcar proposta como enviada

## Regras De UX Aplicadas

Nesta rodada, a UX passou a seguir estas regras:

- Projeto mostra contexto, nao leitura financeira completa.
- Simulacao mostra cenario, nao composicao detalhada de proposta.
- Orcamento mostra proposta montada, nao contexto completo do local.
- O hero de cada tela destaca apenas o que mais importa naquele momento.
- Badges curtos substituem textos longos quando a informacao e simples.
- A interface sempre tenta indicar o proximo passo natural.

## Referencias Em Codigo

- Projeto: [show.blade.php](/d:/projects/voltrune/resources/views/solar/projects/show.blade.php)
- Simulacao: [show.blade.php](/d:/projects/voltrune/resources/views/solar/simulations/show.blade.php)
- Orcamento: [edit.blade.php](/d:/projects/voltrune/resources/views/solar/quotes/edit.blade.php)
- Estilos: [_solar.scss](/d:/projects/voltrune/resources/scss/pages/_solar.scss)
- Rotas: [solar.php](/d:/projects/voltrune/routes/solar.php)
