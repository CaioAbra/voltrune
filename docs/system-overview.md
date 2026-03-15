# Visao Geral Do Sistema

## O Que E A Voltrune

A Voltrune e uma plataforma Laravel com multiplas camadas de produto e acesso.

Hoje ela combina:

- site publico para aquisicao e posicionamento
- Hub para autenticacao e navegacao do cliente
- Hub Admin para operacao interna
- modulos de produto protegidos por acesso

O modulo de produto mais maduro hoje e o Solar.

## Dominios Funcionais Principais

### 1. Site Publico

Papel:

- apresentar a empresa
- captar interesse
- gerar leads
- posicionar servicos e produtos

Rotas principais:

- [routes/web.php](/d:/projects/voltrune/routes/web.php)

Em producao, o site publico fica associado ao `ROOT_DOMAIN`.

### 2. Hub

Papel:

- autenticar usuarios
- registrar e contextualizar empresas
- expor dashboard e area da conta
- funcionar como porta de entrada para os produtos SaaS

Rotas principais:

- [routes/hub.php](/d:/projects/voltrune/routes/hub.php)

Em producao, o Hub fica associado ao `HUB_DOMAIN`.

### 3. Hub Admin

Papel:

- operar empresas
- controlar acesso a produtos
- controlar contratos
- controlar cobranca e estado comercial

Esta e a console interna da Voltrune.

### 4. Solar

Papel:

- permitir que instaladores solares montem leitura comercial e pre-orcamento com agilidade
- organizar o fluxo em cliente, projeto, simulacao e orcamento

Rotas principais:

- [routes/solar.php](/d:/projects/voltrune/routes/solar.php)

Cadeia funcional atual:

1. cliente
2. projeto
3. simulacao
4. orcamento

Em producao, o Solar fica associado ao `SOLAR_DOMAIN`.

## Modelo De Execucao

Em alto nivel, a plataforma funciona assim:

1. o usuario entra pelo site publico
2. o usuario se registra ou faz login pelo Hub
3. o usuario e associado a uma empresa
4. o acesso e validado por status da empresa e liberacao do produto
5. os modulos habilitados ficam disponiveis

Essa cadeia de acesso e uma das decisoes arquiteturais mais importantes da plataforma.

Ela permite que a Voltrune opere como um SaaS multiproduto sem exigir um projeto Laravel separado por produto.

## Padrao Atual De Dominio

No estado atual, o padrao recomendado e:

- `voltrune.com` para o site publico
- `hub.voltrune.com` para o Hub
- `solar.voltrune.com` para o modulo Solar

Em ambiente local, o comportamento continua simplificado:

- site publico na raiz local
- Hub em `/hub`
- Solar em `/solar`

Isso evita complexidade de DNS no desenvolvimento, sem perder a separacao correta em producao.

## Ideia Central Da Plataforma

A plataforma atual esta organizada em torno de:

- uma camada unica de identidade
- uma camada unica de empresa
- multiplas camadas de produto

Isso significa:

- o usuario nao entra em um dashboard generico
- ele entra em um contexto de empresa
- os produtos sao habilitados conforme o estado comercial e contratual

Esse padrao aparece de forma muito clara no Hub e no Solar.
