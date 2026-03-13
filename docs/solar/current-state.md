# Estado Atual E Limitacoes

## O Que Ja Esta Maduro Hoje

O Solar ja esta forte em:

- geracao de pre-orcamento
- apoio a primeira conversa comercial
- automacao orientada por localizacao
- uso de fator solar regional
- fallback comercial por mercado
- edicao guiada de projetos
- leitura comercial de cenarios por simulacao
- simulacoes ligadas a projetos
- orcamentos com composicao real de itens

## Limitacoes Atuais

### 1. Ainda nao existe catalogo estruturado de equipamentos

Hoje o modulo ainda nao gerencia:

- marcas de inversor como entidade estruturada
- marcas de modulo como entidade estruturada
- estoque de fornecedor
- custos por SKU

Workaround atual:

- defaults da empresa
- composicao descritiva
- detalhamento estimado do kit
- itens manuais no orcamento

### 2. Sensibilidade a dependencias externas

O Solar ainda depende de servicos publicos para:

- enriquecimento de CEP
- geocodificacao
- fator PVGIS

O que ja foi feito:

- cache
- fallback de fator solar
- reuso de fator persistido
- degradacao segura

Impacto pratico:

- o sistema continua utilizavel
- mas a automacao em tempo real ainda pode sofrer com latencia de terceiros

### 3. Simulacao financeira ainda e propositalmente simples

Hoje a simulacao nao modela:

- inflacao tarifaria
- financiamento
- complexidade tributaria
- cenarios avancados de payback

Isso foi uma escolha intencional para priorizar:

- velocidade comercial
- clareza de leitura
- simplicidade operacional

### 4. A transicao entre projeto e simulacao ainda e incremental

Hoje:

- o projeto ainda persiste parte do snapshot calculado
- a simulacao foi fortalecida como entidade real
- o orcamento ja nasce da simulacao, mas o fluxo de proposta ainda nao esta completo em profundidade

Essa transicao foi aceita para evitar ruptura do fluxo existente.

### 5. Publicacao do Solar em subdominio ainda depende de padrao operacional

O Solar ja esta funcionando em `solar.voltrune.com`, mas a publicacao correta depende de:

- variaveis de ambiente corretas
- registro correto das rotas por dominio
- sessao compartilhada com o Hub
- links simbolicos para assets publicos na pasta do subdominio

Sem isso, os erros mais provaveis sao:

- Solar cair no dominio do Hub
- loop de redirecionamento
- home institucional abrir no lugar do produto
- tela do Solar carregar sem CSS/JS

## Por Que Essas Trocas Foram Aceitas

A estrategia atual privilegia:

- utilidade rapida
- clareza comercial
- simplicidade de operacao

acima de:

- detalhe profundo de engenharia
- catalogo de compras completo
- modelagem financeira avancada

Para o momento atual do produto, essa troca continua coerente.

## Evolucao Recomendada

Quando o produto precisar de mais maturidade, a sequencia mais consistente e:

1. catalogo estruturado de equipamentos por empresa
2. integracao com fornecedor e origem de custo
3. fluxo de proposta mais completo a partir da simulacao
4. modelos financeiros mais ricos
5. profundidade de validacao de engenharia

Essa ordem preserva a identidade comercial do produto sem destruir a usabilidade atual.
