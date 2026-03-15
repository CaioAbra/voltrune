@extends('solar.layout')

@section('title', 'Solar | Voltrune')

@php
    $nextAction = match (true) {
        $kpis['customers'] === 0 => [
            'title' => 'Cadastre o primeiro cliente',
            'body' => 'O fluxo do Solar sempre comeca pelo cliente. Depois disso, voce abre o projeto com menos retrabalho.',
            'label' => 'Abrir clientes',
            'route' => route('solar.customers.index'),
        ],
        $kpis['projects'] === 0 => [
            'title' => 'Abra o primeiro projeto',
            'body' => 'Com um cliente cadastrado, o proximo passo e registrar local, consumo e dados base da instalacao.',
            'label' => 'Abrir projetos',
            'route' => route('solar.projects.index'),
        ],
        $pipeline['simulations_without_quotes'] > 0 => [
            'title' => 'Revise as simulacoes pendentes',
            'body' => 'Voce ja tem simulacoes prontas para virar orcamento. Revise primeiro as que ainda nao foram convertidas.',
            'label' => 'Abrir simulacoes',
            'route' => route('solar.simulations.index'),
        ],
        $pipeline['quotes_follow_up_due'] > 0 => [
            'title' => 'Atualize os follow-ups vencidos',
            'body' => 'Existem oportunidades com proximo contato atrasado. Vale priorizar o pipeline comercial antes de abrir novas propostas.',
            'label' => 'Abrir orcamentos',
            'route' => route('solar.quotes.index', ['sort' => 'follow_up_asc']),
        ],
        default => [
            'title' => 'Acompanhe os orcamentos ativos',
            'body' => 'Com a base montada, o foco agora e revisar itens, preco final e proximo passo comercial de cada orcamento.',
            'label' => 'Abrir orcamentos',
            'route' => route('solar.quotes.index'),
        ],
    };
@endphp

@section('solar-content')
    <section class="solar-page-shell">
        <section class="hub-card hub-card--subtle solar-page-intro">
            <div class="solar-page-intro__header">
                <div class="solar-page-intro__copy">
                    <p class="solar-section-eyebrow">Operacao do Solar</p>
                    <h2>Organize o atendimento do primeiro contato ao orcamento</h2>
                    <p class="hub-note">Use o Solar para cadastrar clientes, abrir projetos, revisar simulacoes e fechar orcamentos com mais clareza e menos retrabalho.</p>
                </div>

                <div class="solar-page-intro__meta">
                    <span class="solar-project-showcase__status-label">Fluxo oficial</span>
                    <strong>Cliente -> Projeto -> Simulacao -> Orcamento</strong>
                    <p>Se estiver comecando, siga essa ordem. Ela foi pensada para reduzir retrabalho e manter contexto em cada etapa.</p>
                </div>
            </div>
        </section>

        <section class="solar-page-grid solar-page-grid--cards">
            <article class="hub-card hub-card--subtle solar-quick-card">
                <span class="solar-quick-card__eyebrow">Base ativa</span>
                <div>
                    <h3>{{ $kpis['customers'] }}</h3>
                    <p>{{ $kpis['customers'] === 1 ? 'cliente cadastrado' : 'clientes cadastrados' }}</p>
                </div>
                <a href="{{ route('solar.customers.index') }}" class="hub-btn hub-btn--subtle">Abrir clientes</a>
            </article>

            <article class="hub-card hub-card--subtle solar-quick-card">
                <span class="solar-quick-card__eyebrow">Entrada do funil</span>
                <div>
                    <h3>{{ $kpis['projects'] }}</h3>
                    <p>{{ $pipeline['projects_draft'] }} {{ $pipeline['projects_draft'] === 1 ? 'projeto em rascunho' : 'projetos em rascunho' }}</p>
                </div>
                <a href="{{ route('solar.projects.index') }}" class="hub-btn hub-btn--subtle">Abrir projetos</a>
            </article>

            <article class="hub-card hub-card--subtle solar-quick-card">
                <span class="solar-quick-card__eyebrow">Analise tecnica</span>
                <div>
                    <h3>{{ $kpis['simulations'] }}</h3>
                    <p>{{ $pipeline['simulations_without_quotes'] }} {{ $pipeline['simulations_without_quotes'] === 1 ? 'simulacao sem orcamento' : 'simulacoes sem orcamento' }}</p>
                </div>
                <a href="{{ route('solar.simulations.index') }}" class="hub-btn hub-btn--subtle">Abrir simulacoes</a>
            </article>

            <article class="hub-card hub-card--subtle solar-quick-card">
                <span class="solar-quick-card__eyebrow">Catalogo</span>
                <div>
                    <h3>{{ $kpis['catalog'] }}</h3>
                    <p>{{ $kpis['catalog'] === 1 ? 'item ativo no catalogo' : 'itens ativos no catalogo' }}</p>
                </div>
                <a href="{{ route('solar.catalog.index') }}" class="hub-btn hub-btn--subtle">Abrir catalogo</a>
            </article>

            <article class="hub-card hub-card--subtle solar-quick-card">
                <span class="solar-quick-card__eyebrow">Fechamento</span>
                <div>
                    <h3>{{ $kpis['quotes'] }}</h3>
                    <p>{{ $pipeline['quotes_review'] }} em revisao, {{ $pipeline['quotes_sent'] }} enviadas, {{ $pipeline['quotes_follow_up_due'] }} com follow-up vencido e {{ $pipeline['quotes_won'] }} fechadas</p>
                </div>
                <a href="{{ route('solar.quotes.index') }}" class="hub-btn hub-btn--subtle">Abrir orcamentos</a>
            </article>
        </section>

        <section class="hub-card solar-page-panel">
            <div class="solar-page-panel__header">
                <p class="solar-section-eyebrow">Comece por aqui</p>
                <h2>{{ $nextAction['title'] }}</h2>
                <p class="hub-note">{{ $nextAction['body'] }}</p>
            </div>

            <div class="hub-actions">
                <a href="{{ $nextAction['route'] }}" class="hub-btn">{{ $nextAction['label'] }}</a>
            </div>

            <div class="solar-page-grid solar-page-grid--cards">
                <article class="hub-card hub-card--subtle solar-quick-card">
                    <span class="solar-quick-card__eyebrow">Passo 1</span>
                    <div>
                        <h3>Cliente</h3>
                        <p>Cadastre a pessoa ou empresa atendida para abrir o fluxo sem repetir informacoes.</p>
                    </div>
                    <a href="{{ route('solar.customers.index') }}" class="hub-btn hub-btn--subtle">Abrir clientes</a>
                </article>

                <article class="hub-card hub-card--subtle solar-quick-card">
                    <span class="solar-quick-card__eyebrow">Passo 2</span>
                    <div>
                        <h3>Projeto</h3>
                        <p>Preencha local, consumo e dados base da instalacao para montar a leitura inicial.</p>
                    </div>
                    <a href="{{ route('solar.projects.index') }}" class="hub-btn hub-btn--subtle">Abrir projetos</a>
                </article>

                <article class="hub-card hub-card--subtle solar-quick-card">
                    <span class="solar-quick-card__eyebrow">Passo 3</span>
                    <div>
                        <h3>Simulacao</h3>
                        <p>Revise a sugestao do sistema, compare alternativas e ajuste o que for necessario.</p>
                    </div>
                    <a href="{{ route('solar.simulations.index') }}" class="hub-btn hub-btn--subtle">Abrir simulacoes</a>
                </article>

                <article class="hub-card hub-card--subtle solar-quick-card">
                    <span class="solar-quick-card__eyebrow">Passo 4</span>
                    <div>
                        <h3>Orcamento</h3>
                        <p>Feche itens, preco final e status comercial para avancar o atendimento com seguranca.</p>
                    </div>
                    <a href="{{ route('solar.quotes.index') }}" class="hub-btn hub-btn--subtle">Abrir orcamentos</a>
                </article>

                <article class="hub-card hub-card--subtle solar-quick-card">
                    <span class="solar-quick-card__eyebrow">Base operacional</span>
                    <div>
                        <h3>Catalogo</h3>
                        <p>Mantenha equipamentos e servicos da empresa prontos para entrar no orcamento com custo e venda coerentes.</p>
                    </div>
                    <a href="{{ route('solar.catalog.index') }}" class="hub-btn hub-btn--subtle">Abrir catalogo</a>
                </article>
            </div>
        </section>
    </section>
@endsection
