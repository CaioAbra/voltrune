# UI E UX Do Solar

## Direcao Geral

O Solar deve se comportar como um SaaS comercial para instaladores, com apoio tecnico suficiente para dar confianca na venda.

A hierarquia visual principal e:

1. resultado comercial
2. contexto do projeto
3. explicacao tecnica

## Papel Das Telas

### Projeto

A tela de projeto foi simplificada para concentrar:

- cliente
- local de instalacao
- consumo base
- simulacoes relacionadas
- orcamentos relacionados

Ela nao deve competir com a simulacao em leitura financeira.

### Simulacao

A tela de simulacao e a principal tela de analise do cenario.

Ela prioriza:

- potencia
- geracao
- preco sugerido
- economia mensal
- payback
- ROI

Os dados tecnicos continuam presentes, mas com peso visual menor.

### Orcamento

A tela de orcamento e a principal tela de proposta.

Ela prioriza:

- itens de materiais e servicos
- custo total
- preco final
- lucro bruto
- margem
- status comercial

## Acoes E Proximos Passos

A interface sempre deve indicar o proximo passo natural:

- no Projeto: criar ou abrir simulacao
- na Simulacao: gerar orcamento
- no Orcamento: adicionar itens e avancar o status da proposta

## Regras De Densidade Visual

- nao repetir a mesma informacao com o mesmo peso em telas diferentes
- manter cards principais para os indicadores mais importantes
- usar badges curtos para status e contexto rapido
- preferir mensagens enxutas a blocos longos de texto

## Microinteracoes

As microinteracoes atuais devem continuar discretas:

- hover suave em cards
- animacao leve em numeros
- foco visivel em campos e botoes
- tooltips simples via `title` em badges e acoes quando ajudam na leitura

## Referencias

- Projeto: [show.blade.php](/d:/projects/voltrune/resources/views/solar/projects/show.blade.php)
- Simulacao: [show.blade.php](/d:/projects/voltrune/resources/views/solar/simulations/show.blade.php)
- Orcamento: [edit.blade.php](/d:/projects/voltrune/resources/views/solar/quotes/edit.blade.php)
- Estilos: [_solar.scss](/d:/projects/voltrune/resources/scss/pages/_solar.scss)
