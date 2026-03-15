# Camada Compartilhada

## Objetivo

Esta secao documenta as partes compartilhadas que sustentam toda a plataforma.

Nao sao elementos de um unico modulo.

Elas existem para suportar:

- roteamento de autenticacao
- acesso orientado por empresa
- acesso administrativo
- comandos operacionais
- configuracao de dominios e subdominios

## Modelos Compartilhados Principais

Referencias:

- [Company.php](/d:/projects/voltrune/app/Models/Company.php)
- [User.php](/d:/projects/voltrune/app/Models/User.php)
- [CompanyContract.php](/d:/projects/voltrune/app/Models/CompanyContract.php)
- [CompanyProductAccess.php](/d:/projects/voltrune/app/Models/CompanyProductAccess.php)
- [CompanyBillingRecord.php](/d:/projects/voltrune/app/Models/CompanyBillingRecord.php)

Por que eles importam:

- sustentam o Hub e a liberacao de produtos
- definem quem e o cliente dentro da plataforma
- definem o que a empresa pode acessar

## Middlewares Compartilhados

Principais middlewares:

- [EnsureCompanyIsActive.php](/d:/projects/voltrune/app/Http/Middleware/EnsureCompanyIsActive.php)
- [EnsureProductAccessIsActive.php](/d:/projects/voltrune/app/Http/Middleware/EnsureProductAccessIsActive.php)
- [EnsureHubAdmin.php](/d:/projects/voltrune/app/Http/Middleware/EnsureHubAdmin.php)

Por que existem:

- autenticacao isolada nao basta
- o acesso depende do estado da empresa e da liberacao do produto
- a area admin precisa ficar explicitamente separada

## Padrao De Publicacao Multi-Subdominio

O padrao atual da Voltrune usa:

- um projeto Laravel unico
- build unico do frontend em `public/build`
- multiplos subdominios apontando para produtos diferentes

Guia detalhado:

- [Publicacao multi-subdominio](./publicacao-multi-subdominio.md)

## Comandos Compartilhados

Os comandos atuais incluem:

- migrations do Hub
- migrations do Solar
- seed/admin do Hub
- sincronizacao de utilidades do Solar

Por que isso importa:

- a plataforma tem mais de uma area logica
- setup e manutencao sao orientados por produto
- algumas capacidades dependem de sincronizacao agendada ou manual

## Principio Arquitetural Compartilhado

A Voltrune esta estruturada em torno de:

- uma camada unica de identidade
- uma camada unica de empresa
- multiplas camadas de produto

E essa camada compartilhada que mantem essas partes coerentes entre si.
