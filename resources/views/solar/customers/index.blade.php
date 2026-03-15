@extends('solar.layout')

@section('title', 'Clientes | Solar')

@php
    $sortOptions = [
        'name_asc' => 'Nome (A-Z)',
        'recent' => 'Mais recentes',
        'projects_desc' => 'Mais projetos',
    ];
    $pipelineOptions = [
        '' => 'Todo o funil',
        'with_projects' => 'Com projetos',
        'without_projects' => 'Sem projetos',
    ];
@endphp

@section('solar-content')
    <section class="solar-page-shell">
        <section class="hub-card hub-card--subtle solar-page-intro">
            <div class="solar-page-intro__header">
                <div class="solar-page-intro__copy">
                    <p class="solar-section-eyebrow">Clientes</p>
                    <h2>Base comercial dos clientes</h2>
                    <p class="hub-note">Aqui ficam as pessoas e empresas atendidas no Solar. A ideia e facilitar a abertura de novos projetos e reduzir repeticao de cadastro ao longo do atendimento.</p>
                </div>

                <div class="solar-page-intro__meta">
                    <span class="solar-project-showcase__status-label">Uso recomendado</span>
                    <strong>Cadastre uma vez, reutilize no funil inteiro</strong>
                    <p>Depois de criar o cliente, o proximo passo natural e abrir um projeto para registrar local, consumo e gerar a primeira simulacao.</p>
                </div>
            </div>
        </section>

        <section class="solar-page-grid solar-page-grid--cards">
            <article class="hub-card hub-card--subtle solar-quick-card">
                <span class="solar-quick-card__eyebrow">Base ativa</span>
                <h3>{{ $summary['total'] }} {{ $summary['total'] === 1 ? 'cliente' : 'clientes' }}</h3>
                <p>Carteira total cadastrada para abrir novos projetos sem repetir cadastro.</p>
            </article>
            <article class="hub-card hub-card--subtle solar-quick-card">
                <span class="solar-quick-card__eyebrow">Com projeto</span>
                <h3>{{ $summary['with_projects'] }}</h3>
                <p>Clientes que ja entraram no funil operacional com pelo menos um projeto aberto.</p>
            </article>
            <article class="hub-card hub-card--subtle solar-quick-card">
                <span class="solar-quick-card__eyebrow">Sem projeto</span>
                <h3>{{ $summary['without_projects'] }}</h3>
                <p>Base comercial pronta para receber o primeiro projeto assim que surgir oportunidade.</p>
            </article>
            <article class="hub-card hub-card--subtle solar-quick-card">
                <span class="solar-quick-card__eyebrow">Cobertura</span>
                <h3>{{ $summary['states'] }} {{ $summary['states'] === 1 ? 'UF' : 'UFs' }}</h3>
                <p>Quantidade de estados presentes na base atual de clientes atendidos.</p>
            </article>
        </section>

        <section class="hub-card solar-page-panel">
            <section class="hub-card hub-card--subtle solar-filter-panel">
                <div class="solar-page-panel__header">
                    <h2>Busca e filtros</h2>
                    <p class="hub-note">Encontre clientes por nome, contato, documento ou cidade e priorize quem ja entrou no funil de projetos.</p>
                </div>

                <form method="get" action="{{ route('solar.customers.index') }}" class="solar-filter-grid">
                    <div>
                        <label for="customer-q" class="hub-auth-label">Buscar cliente</label>
                        <input id="customer-q" name="q" type="text" class="hub-auth-input" value="{{ $filters['q'] }}" placeholder="Nome, telefone, documento ou cidade">
                    </div>

                    <div>
                        <label for="customer-state" class="hub-auth-label">UF</label>
                        <select id="customer-state" name="state" class="hub-auth-input">
                            <option value="">Todas</option>
                            @foreach ($stateOptions as $state)
                                <option value="{{ $state }}" @selected($filters['state'] === $state)>{{ $state }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="customer-pipeline" class="hub-auth-label">Pipeline</label>
                        <select id="customer-pipeline" name="pipeline" class="hub-auth-input">
                            @foreach ($pipelineOptions as $value => $label)
                                <option value="{{ $value }}" @selected($filters['pipeline'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="customer-sort" class="hub-auth-label">Ordenar por</label>
                        <select id="customer-sort" name="sort" class="hub-auth-input">
                            @foreach ($sortOptions as $value => $label)
                                <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="solar-filter-actions">
                        <button type="submit" class="hub-btn">Aplicar filtros</button>
                        <a href="{{ route('solar.customers.index') }}" class="hub-btn hub-btn--subtle">Limpar</a>
                    </div>
                </form>

                <p class="solar-filter-summary">
                    {{ $summary['filtered'] }} {{ $summary['filtered'] === 1 ? 'cliente encontrado' : 'clientes encontrados' }}
                    @if ($hasActiveFilters)
                        com os filtros atuais.
                    @else
                        na base comercial atual.
                    @endif
                </p>
            </section>

            <div class="hub-actions solar-page-toolbar">
                <a href="{{ route('solar.customers.create') }}" class="hub-btn">Novo cliente</a>
                <a href="{{ route('solar.projects.create') }}" class="hub-btn hub-btn--subtle">Novo projeto</a>
            </div>

            @if (session('solar_status'))
                <div
                    class="hub-alert hub-alert--success solar-flash-alert"
                    data-flash-alert
                    data-flash-timeout="5000"
                    role="status"
                    aria-live="polite"
                >
                    <div class="solar-flash-alert__content">
                        {{ session('solar_status') }}
                    </div>
                    <button type="button" class="solar-flash-alert__close" data-flash-close aria-label="Fechar aviso">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if ($customers->isEmpty())
                <div class="solar-page-panel__header">
                    <h2>{{ $hasActiveFilters ? 'Nenhum cliente encontrado' : 'Clientes cadastrados' }}</h2>
                    <p class="hub-note">
                        {{ $hasActiveFilters
                            ? 'Os filtros atuais nao retornaram clientes. Ajuste a busca ou limpe os filtros para recuperar a base completa.'
                            : 'Nenhum cliente cadastrado para esta empresa ainda. Comece pela base comercial e siga para os projetos quando surgir uma oportunidade.' }}
                    </p>
                    @if ($hasActiveFilters)
                        <div class="hub-actions">
                            <a href="{{ route('solar.customers.index') }}" class="hub-btn hub-btn--subtle">Limpar filtros</a>
                        </div>
                    @endif
                </div>
            @else
                <div class="hub-card hub-card--subtle solar-table-panel">
                    <div class="solar-page-panel__header">
                        <h2>Cadastro comercial</h2>
                        <p class="hub-note">Cada cliente pode receber multiplos projetos ao longo do mesmo relacionamento comercial. Use a coluna de projetos para priorizar a carteira.</p>
                    </div>
                </div>

                <div class="hub-table-wrap solar-table-wrap">
                    <table class="hub-table solar-table solar-table--customers">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Contato</th>
                                <th>Cidade/UF</th>
                                <th>Projetos</th>
                                <th>Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($customers as $customer)
                                <tr class="solar-table__row">
                                    <td data-label="Nome" class="solar-table__cell solar-table__cell--primary">
                                        <strong class="solar-table__entity">{{ $customer->name }}</strong>
                                        <div class="hub-table__sub solar-table__meta">
                                            {{ $customer->document ?: 'Documento nao informado' }}
                                            <span> | criado em {{ $customer->created_at?->format('d/m/Y H:i') ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td data-label="Contato" class="solar-table__cell">
                                        <div>{{ $customer->email ?: '-' }}</div>
                                        <div class="hub-table__sub solar-table__meta">{{ $customer->phone ?: 'Sem telefone' }}</div>
                                    </td>
                                    <td data-label="Cidade / UF" class="solar-table__cell">
                                        @php
                                            $location = trim(collect([$customer->city, $customer->state])->filter()->implode('/'));
                                        @endphp
                                        {{ $location !== '' ? $location : '-' }}
                                    </td>
                                    <td data-label="Projetos" class="solar-table__cell">
                                        <strong class="solar-table__entity">{{ $customer->projects_count }}</strong>
                                        <div class="hub-table__sub solar-table__meta">
                                            {{ $customer->projects_count === 0 ? 'Ainda sem projeto no funil' : ($customer->projects_count === 1 ? 'Projeto em andamento' : 'Projetos vinculados') }}
                                        </div>
                                    </td>
                                    <td data-label="Acoes" class="solar-table__cell solar-table__cell--actions">
                                        <div class="hub-table-actions solar-table__actions">
                                            <a href="{{ route('solar.projects.create', ['customer' => $customer->id]) }}" class="hub-btn">
                                                Abrir projeto
                                            </a>
                                            <a href="{{ route('solar.customers.edit', $customer->id) }}" class="hub-btn hub-btn--subtle">
                                                Editar
                                            </a>
                                            <form action="{{ route('solar.customers.destroy', $customer->id) }}" method="post">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="hub-btn" onclick="return confirm('Excluir este cliente?');">
                                                    Excluir
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </section>
@endsection
