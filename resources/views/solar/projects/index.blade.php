@extends('solar.layout')

@section('title', 'Projetos | Voltrune Solar')

@php
    $statusOptions = [
        '' => 'Todos os status',
        'draft' => 'Base em montagem',
        'qualified' => 'Em revisao',
        'proposal' => 'Pronto para orcamento',
        'won' => 'Fechado',
    ];
    $geocodingOptions = [
        '' => 'Toda a geolocalizacao',
        'ready' => 'Localizacao pronta',
        'address_loaded' => 'Endereco parcial',
        'not_requested' => 'Aguardando CEP',
        'not_found' => 'Nao encontrada',
        'pending' => 'Em andamento',
    ];
    $sortOptions = [
        'recent' => 'Mais recentes',
        'name_asc' => 'Nome do projeto',
        'consumption_desc' => 'Maior consumo',
        'price_desc' => 'Maior orcamento inicial',
    ];
@endphp

@section('solar-content')
    <section class="solar-page-shell">
        <section class="hub-card hub-card--subtle solar-page-intro">
            <div class="solar-page-intro__header">
                <div class="solar-page-intro__copy">
                    <p class="solar-section-eyebrow">Projetos</p>
                    <h2>Locais de instalacao e dados base do atendimento</h2>
                    <p class="hub-note">Cada projeto concentra cliente, endereco, consumo e a leitura inicial do orcamento para abrir a simulacao correta.</p>
                </div>

                <div class="solar-page-intro__meta">
                    <span class="solar-project-showcase__status-label">Comece por aqui</span>
                    <strong>Cliente, local e consumo primeiro</strong>
                    <p>Com o projeto bem montado, a simulacao nasce com menos digitacao e mais coerencia comercial.</p>
                </div>
            </div>
        </section>

        <section class="solar-page-grid solar-page-grid--cards">
            <article class="hub-card hub-card--subtle solar-quick-card">
                <span class="solar-quick-card__eyebrow">Projetos ativos</span>
                <h3>{{ $summary['total'] }}</h3>
                <p>Base total de atendimentos com local, consumo e contexto comercial registrados.</p>
            </article>
            <article class="hub-card hub-card--subtle solar-quick-card">
                <span class="solar-quick-card__eyebrow">Base em montagem</span>
                <h3>{{ $summary['draft'] }}</h3>
                <p>Projetos ainda concentrados no cadastro inicial, antes da revisao comercial forte.</p>
            </article>
            <article class="hub-card hub-card--subtle solar-quick-card">
                <span class="solar-quick-card__eyebrow">Em revisao</span>
                <h3>{{ $summary['qualified'] }}</h3>
                <p>Projetos com dados suficientes para validar leitura tecnica e comercial.</p>
            </article>
            <article class="hub-card hub-card--subtle solar-quick-card">
                <span class="solar-quick-card__eyebrow">Local pronto</span>
                <h3>{{ $summary['geocoding_ready'] }}</h3>
                <p>Projetos com geolocalizacao pronta para sustentar fator solar e concessionaria.</p>
            </article>
        </section>

        <section class="hub-card solar-page-panel">
            <section class="hub-card hub-card--subtle solar-filter-panel">
                <div class="solar-page-panel__header">
                    <h2>Busca e filtros</h2>
                    <p class="hub-note">Localize projetos por cliente, cidade, concessionaria ou etapa comercial e ordene a fila pelo que importa agora.</p>
                </div>

                <form method="get" action="{{ route('solar.projects.index') }}" class="solar-filter-grid solar-filter-grid--wide">
                    <div>
                        <label for="project-q" class="hub-auth-label">Buscar projeto</label>
                        <input id="project-q" name="q" type="text" class="hub-auth-input" value="{{ $filters['q'] }}" placeholder="Projeto, cliente, cidade ou concessionaria">
                    </div>

                    <div>
                        <label for="project-status" class="hub-auth-label">Status comercial</label>
                        <select id="project-status" name="status" class="hub-auth-input">
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="project-state" class="hub-auth-label">UF</label>
                        <select id="project-state" name="state" class="hub-auth-input">
                            <option value="">Todas</option>
                            @foreach ($stateOptions as $state)
                                <option value="{{ $state }}" @selected($filters['state'] === $state)>{{ $state }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="project-geocoding" class="hub-auth-label">Geolocalizacao</label>
                        <select id="project-geocoding" name="geocoding" class="hub-auth-input">
                            @foreach ($geocodingOptions as $value => $label)
                                <option value="{{ $value }}" @selected($filters['geocoding'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="project-sort" class="hub-auth-label">Ordenar por</label>
                        <select id="project-sort" name="sort" class="hub-auth-input">
                            @foreach ($sortOptions as $value => $label)
                                <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="solar-filter-actions">
                        <button type="submit" class="hub-btn">Aplicar filtros</button>
                        <a href="{{ route('solar.projects.index') }}" class="hub-btn hub-btn--subtle">Limpar</a>
                    </div>
                </form>

                <p class="solar-filter-summary">
                    {{ $summary['filtered'] }} {{ $summary['filtered'] === 1 ? 'projeto encontrado' : 'projetos encontrados' }}
                    @if ($hasActiveFilters)
                        com os filtros atuais.
                    @else
                        no funil operacional atual.
                    @endif
                </p>
            </section>

            <div class="hub-actions solar-page-toolbar">
                <a href="{{ route('solar.projects.create') }}" class="hub-btn">Novo projeto</a>
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

            @if ($projects->isEmpty())
                <div class="solar-page-panel__header">
                    <h2>{{ $hasActiveFilters ? 'Nenhum projeto encontrado' : 'Projetos de instalacao' }}</h2>
                    <p class="hub-note">
                        {{ $hasActiveFilters
                            ? 'Os filtros atuais nao retornaram projetos. Ajuste os criterios para recuperar a fila desejada.'
                            : 'Nenhum projeto cadastrado para esta empresa ainda. Comece pelo local da instalacao para destravar a primeira simulacao e o primeiro orcamento.' }}
                    </p>
                    @if ($hasActiveFilters)
                        <div class="hub-actions">
                            <a href="{{ route('solar.projects.index') }}" class="hub-btn hub-btn--subtle">Limpar filtros</a>
                        </div>
                    @endif
                </div>
            @else
                <div class="hub-card hub-card--subtle solar-table-panel">
                    <div class="solar-page-panel__header">
                        <h2>Fila operacional de projetos</h2>
                        <p class="hub-note">Use a coluna de fluxo para enxergar quantas simulacoes e orcamentos ja nasceram de cada projeto, sem perder contexto de localizacao e consumo.</p>
                    </div>
                </div>

                <div class="hub-table-wrap solar-table-wrap">
                    <table class="hub-table solar-table solar-table--projects">
                        <thead>
                            <tr>
                                <th>Projeto</th>
                                <th>Cliente</th>
                                <th>Cidade/UF</th>
                                <th>Consumo mensal</th>
                                <th>Fluxo</th>
                                <th>Status</th>
                                <th>Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($projects as $project)
                                @php
                                    $projectStatusLabel = match ($project->status) {
                                        'draft' => 'Base em montagem',
                                        'qualified' => 'Em revisao',
                                        'proposal' => 'Pronto para orcamento',
                                        'won' => 'Fechado',
                                        default => strtoupper((string) $project->status),
                                    };
                                    $geocodingStatusLabel = match ($project->geocoding_status ?? 'pending') {
                                        'ready' => 'Localizacao pronta',
                                        'not_found' => 'Nao encontrada',
                                        'address_loaded' => 'Endereco parcial',
                                        'not_requested' => 'Aguardando CEP',
                                        default => 'Em andamento',
                                    };
                                @endphp
                                <tr class="solar-table__row">
                                    <td data-label="Projeto" class="solar-table__cell solar-table__cell--primary">
                                        <strong class="solar-table__entity">{{ $project->name }}</strong>
                                        <div class="hub-table__sub solar-table__meta">
                                            {{ $project->street ?: 'Endereco em preparacao' }}{{ $project->number ? ', '.$project->number : '' }}
                                            @if ($project->zip_code)
                                                <span> | CEP {{ $project->zip_code }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td data-label="Cliente" class="solar-table__cell">{{ $project->customer?->name ?: '-' }}</td>
                                    <td data-label="Cidade / UF" class="solar-table__cell">
                                        @php
                                            $location = trim(collect([$project->city, $project->state])->filter()->implode('/'));
                                        @endphp
                                        {{ $location !== '' ? $location : 'Aguardando localizacao' }}
                                    </td>
                                    <td data-label="Consumo mensal" class="solar-table__cell">{{ $project->monthly_consumption_kwh ? number_format((float) $project->monthly_consumption_kwh, 2, ',', '.') . ' kWh' : '-' }}</td>
                                    <td data-label="Fluxo" class="solar-table__cell">
                                        <div><strong>{{ $project->simulations_count }}</strong> {{ $project->simulations_count === 1 ? 'simulacao' : 'simulacoes' }}</div>
                                        <div class="hub-table__sub solar-table__meta">
                                            {{ $project->quotes_count }} {{ $project->quotes_count === 1 ? 'orcamento' : 'orcamentos' }}
                                            @if ($project->utility_company)
                                                <span> | {{ $project->utility_company }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td data-label="Status" class="solar-table__cell">
                                        <div class="hub-inline-badges">
                                            <span class="hub-badge">{{ $projectStatusLabel }}</span>
                                            <span class="hub-badge hub-badge--muted">{{ $geocodingStatusLabel }}</span>
                                        </div>
                                    </td>
                                    <td data-label="Acoes" class="solar-table__cell solar-table__cell--actions">
                                        <div class="hub-table-actions solar-table__actions">
                                            <a href="{{ route('solar.projects.show', $project->id) }}" class="hub-btn">Ver</a>
                                            <a href="{{ route('solar.projects.edit', $project->id) }}" class="hub-btn hub-btn--subtle">Editar</a>
                                            <form action="{{ route('solar.projects.destroy', $project->id) }}" method="post">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="hub-btn" onclick="return confirm('Excluir este projeto?');">Excluir</button>
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
