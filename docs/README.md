# Documentacao Da Voltrune

## Objetivo

Esta pasta `docs/` registra o estado atual da plataforma Voltrune com base no codigo que esta no repositorio.

Ela existe para apoiar:

- onboarding tecnico
- manutencao
- revisao arquitetural
- alinhamento interno
- replicacao de padroes entre produtos SaaS

## Mapa Da Documentacao

- [Visao geral do sistema](./system-overview.md)
- [Site publico](./public-site/README.md)
- [Hub](./hub/README.md)
- [Hub Admin](./hub-admin/README.md)
- [Camada compartilhada](./shared/README.md)
- [Modulo Solar](./solar/README.md)

## Como Esta Organizada

A Voltrune nao e um unico site nem um unico modulo.

Hoje a plataforma se divide em:

- site publico para aquisicao
- Hub para autenticacao e area do cliente
- Hub Admin para operacao interna
- modulos de produto protegidos por acesso

Por isso, a documentacao foi separada por dominio funcional em vez de concentrar tudo em um arquivo unico.

## Convencao Atual

Sempre que uma mudanca impactar:

- dominio ou subdominio
- fluxo de autenticacao
- publicacao de assets
- integracao entre Hub e produtos

ela deve ser refletida na documentacao de:

- visao geral do sistema
- camada compartilhada
- modulo/produto afetado
