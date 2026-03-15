@extends('solar.layout')

@section('title', 'Simulacoes | Solar')

@php
    $statusLabels = [
        '' => 'Todas as etapas',
        'draft' => 'Base automatica',
        'qualified' => 'Em revisao',
        'proposal' => 'Pronta para orcamento',
        'won' => 'Fechada',
    ];
    $quoteStateLabels = [
        '' => 'Com e sem orcamento',
        'with_quotes' => 'Com orcamento',
        'without_quotes' => 'Sem orcamento',
    ];
    $sortOptions = [
        'recent' => 'Mais recentes',
        'name_asc' => 'Nome da simulacao',
        'power_desc' => 'Maior potencia',
        'price_desc' => 'Maior preco sugerido',
        'savings_desc' => 'Maior economia',
        'quotes_desc' => 'Mais orcamentos',
    ];
@endphp

@section('solar-content')
    <section class="hub-card solar-project-show solar-project-shell">
        <section class="hub-card hub-card--subtle solar-project-context-hero">
            <div class="solar-project-context-hero__header">
                <div>
                    <p class="solar-section-eyebrow">Simulacoes</p>
                    <h2>Biblioteca de simulacoes</h2>
                    <p class="hub-note">Cada simulacao representa uma revisao tecnica e comercial associada a um projeto, pronta para leitura e conversao em orcamento.</p>
                </div>

                <div class="solar-project-context-hero__focus">
                    <span class="solar-project-showcase__status-label">Leitura operacional</span>
                    <strong>{{ $simulations->count() }} {{ $simulations->count() === 1 ? 'simulacao ativa' : 'simulacoes ativas' }}</strong>
                    <p>Use esta biblioteca para revisar potencia, preco e contexto comercial antes de abrir um orcamento.</p>
                </div>
            </div>
        </section>

        <section class="solar-page-grid solar-page-grid--cards">
            <article class="hub-card hub-card--subtle solar-quick-card">
                <span class="solar-quick-card__eyebrow">Total ativo</span>
                <h3>{{ $summary['total'] }}</h3>
                <p>Revisoes tecnicas e comerciais disponiveis para comparar antes de abrir orcamentos.</p>
            </article>
            <article class="hub-card hub-card--subtle solar-quick-card">
                <span class="solar-quick-card__eyebrow">Base automatica</span>
                <h3>{{ $summary['draft'] }}</h3>
                <p>Simulacoes ainda muito proximas do projeto base, prontas para primeira revisao.</p>
            </article>
            <article class="hub-card hub-card--subtle solar-quick-card">
                <span class="solar-quick-card__eyebrow">Em revisao</span>
                <h3>{{ $summary['qualified'] }}</h3>
                <p>Alternativas que ja pedem comparacao comercial antes de gerar o orcamento final.</p>
            </article>
            <article class="hub-card hub-card--subtle solar-quick-card">
                <span class="solar-quick-card__eyebrow">Com orcamento</span>
                <h3>{{ $summary['with_quotes'] }}</h3>
                <p>Simulacoes que ja viraram proposta operacional de itens, margem e fechamento.</p>
            </article>
        </section>

        <section class="hub-card hub-card--subtle solar-filter-panel">
            <div class="solar-page-panel__header">
                <h2>Busca e filtros</h2>
                <p class="hub-note">Pesquise por simulacao, projeto ou cliente e ordene a biblioteca pelo que precisa ser revisto primeiro.</p>
            </div>

            <form method="get" action="{{ route('solar.simulations.index') }}" class="solar-filter-grid solar-filter-grid--wide">
                <div>
                    <label for="simulation-q" class="hub-auth-label">Buscar simulacao</label>
                    <input id="simulation-q" name="q" type="text" class="hub-auth-input" value="{{ $filters['q'] }}" placeholder="Simulacao, projeto ou cliente">
                </div>

                <div>
                    <label for="simulation-status" class="hub-auth-label">Etapa</label>
                    <select id="simulation-status" name="status" class="hub-auth-input">
                        @foreach ($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="simulation-quote-state" class="hub-auth-label">Orcamento</label>
                    <select id="simulation-quote-state" name="quote_state" class="hub-auth-input">
                        @foreach ($quoteStateLabels as $value => $label)
                            <option value="{{ $value }}" @selected($filters['quote_state'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="simulation-sort" class="hub-auth-label">Ordenar por</label>
                    <select id="simulation-sort" name="sort" class="hub-auth-input">
                        @foreach ($sortOptions as $value => $label)
                            <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="solar-filter-actions">
                    <button type="submit" class="hub-btn">Aplicar filtros</button>
                    <a href="{{ route('solar.simulations.index') }}" class="hub-btn hub-btn--subtle">Limpar</a>
                </div>
            </form>

            <p class="solar-filter-summary">
                {{ $summary['filtered'] }} {{ $summary['filtered'] === 1 ? 'simulacao encontrada' : 'simulacoes encontradas' }}
                @if ($hasActiveFilters)
                    com os filtros atuais.
                @else
                    na biblioteca operacional atual.
                @endif
            </p>
        </section>

        <article class="hub-card hub-card--subtle solar-project-show__card solar-project-simulations-panel">
            <div class="solar-flow-section__header">
                <div>
                    <p class="solar-section-eyebrow">Lista de simulacoes</p>
                    <h2>Revisoes tecnico-comerciais</h2>
                </div>
            </div>

            <div class="solar-project-simulations-panel__grid {{ $simulations->count() === 1 ? 'is-single' : '' }}">
                @forelse ($simulations as $simulation)
                    @php
                        $simulationStatusLabel = match ($simulation->status) {
                            'draft' => 'Base automatica',
                            'qualified' => 'Em revisao',
                            'proposal' => 'Pronta para orcamento',
                            'won' => 'Fechada',
                            default => strtoupper((string) $simulation->status),
                        };
                    @endphp
                    <article class="solar-project-simulation-card">
                        <div class="solar-project-simulation-card__header">
                            <div>
                                <span class="solar-project-simulation-card__eyebrow">Simulacao</span>
                                <h3>{{ $simulation->name }}</h3>
                            </div>
                            <div class="solar-project-simulation-card__chips">
                                <span class="solar-mini-badge solar-mini-badge--automatic">{{ $simulationStatusLabel }}</span>
                                <span class="solar-mini-badge">{{ $simulation->quotes_count }} {{ $simulation->quotes_count === 1 ? 'orcamento' : 'orcamentos' }}</span>
                            </div>
                        </div>

                        <div class="solar-project-simulation-card__body">
                            <p class="hub-note solar-project-simulation-card__summary">
                                Projeto: {{ $simulation->project?->name ?: '-' }}
                                @if ($simulation->project?->customer?->name)
                                    | Cliente: {{ $simulation->project->customer->name }}
                                @endif
                            </p>

                            <div class="solar-project-simulation-card__metrics">
                                <span><strong>Potencia</strong>{{ $simulation->system_power_kwp ? number_format((float) $simulation->system_power_kwp, 2, ',', '.') . ' kWp' : 'Potencia pendente' }}</span>
                                <span><strong>Geracao estimada</strong>{{ $simulation->estimated_generation_kwh ? number_format((float) $simulation->estimated_generation_kwh, 2, ',', '.') . ' kWh/mes' : '-' }}</span>
                                <span><strong>Preco sugerido</strong>{{ $simulation->suggested_price ? 'R$ ' . number_format((float) $simulation->suggested_price, 2, ',', '.') : '-' }}</span>
                                <span><strong>Economia mensal</strong>{{ $simulation->estimated_monthly_savings ? 'R$ ' . number_format((float) $simulation->estimated_monthly_savings, 2, ',', '.') : '-' }}</span>
                            </div>
                        </div>

                        <div class="solar-project-simulation-card__footer">
                            <a href="{{ route('solar.simulations.show', $simulation->id) }}" class="hub-btn">Abrir simulacao</a>
                            @if ($simulation->project)
                                <a href="{{ route('solar.projects.show', $simulation->project->id) }}" class="hub-btn hub-btn--subtle">Ver projeto</a>
                            @endif
                        </div>
                    </article>
                @empty
                    <article class="solar-project-simulation-card solar-project-simulation-card--empty">
                        <div class="solar-project-simulation-card__header">
                            <div>
                                <span class="solar-project-simulation-card__eyebrow">{{ $hasActiveFilters ? 'Sem resultados' : 'Sem simulacoes' }}</span>
                                <h3>{{ $hasActiveFilters ? 'Nenhuma simulacao encontrada' : 'Nenhuma simulacao criada ainda' }}</h3>
                            </div>
                        </div>
                        <p class="hub-note solar-project-simulation-card__summary">
                            {{ $hasActiveFilters
                                ? 'Os filtros atuais nao retornaram simulacoes. Ajuste a busca para recuperar a lista desejada.'
                                : 'As simulacoes serao geradas a partir dos projetos para preparar revisoes e orcamentos multiplos.' }}
                        </p>
                        @if ($hasActiveFilters)
                            <div class="solar-project-simulation-card__footer">
                                <a href="{{ route('solar.simulations.index') }}" class="hub-btn hub-btn--subtle">Limpar filtros</a>
                            </div>
                        @endif
                    </article>
                @endforelse
            </div>
        </article>
    </section>
@endsection
