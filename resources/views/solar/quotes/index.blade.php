@extends('solar.layout')

@section('title', 'Orcamentos | Solar')

@php
    $statusLabels = [
        '' => 'Todos os status',
        'draft' => 'Em montagem',
        'review' => 'Em revisao interna',
        'sent' => 'Enviado ao cliente',
        'approved' => 'Aprovado',
        'won' => 'Fechado',
        'lost' => 'Perdido',
    ];
    $compositionLabels = [
        '' => 'Com e sem itens',
        'with_items' => 'Com itens',
        'without_items' => 'Sem itens',
    ];
    $temperatureLabels = [
        '' => 'Todas',
        'cold' => 'Frias',
        'warm' => 'Mornas',
        'hot' => 'Quentes',
    ];
    $temperatureStatusLabels = [
        'cold' => 'Fria',
        'warm' => 'Morna',
        'hot' => 'Quente',
    ];
    $sortOptions = [
        'recent' => 'Mais recentes',
        'title_asc' => 'Titulo do orcamento',
        'price_desc' => 'Maior preco final',
        'payback_asc' => 'Menor payback',
        'items_desc' => 'Mais itens',
        'follow_up_asc' => 'Proximo contato',
    ];
@endphp

@section('solar-content')
    <section class="hub-card solar-project-show solar-project-shell">
        <section class="hub-card hub-card--subtle solar-project-context-hero">
            <div class="solar-project-context-hero__header">
                <div>
                    <p class="solar-section-eyebrow">Orcamentos</p>
                    <h2>Pipeline comercial do Solar</h2>
                    <p class="hub-note">Os orcamentos nascem da simulacao e consolidam itens, preco final e proximo passo comercial com o cliente.</p>
                </div>

                <div class="solar-project-context-hero__focus">
                    <span class="solar-project-showcase__status-label">Fluxo recomendado</span>
                    <strong>Simulacao -> orcamento -> fechamento</strong>
                    <p>Revise a simulacao, gere o orcamento e acompanhe o atendimento ate o fechamento.</p>
                </div>
            </div>
        </section>

        <section class="solar-page-grid solar-page-grid--cards">
            <article class="hub-card hub-card--subtle solar-quick-card">
                <span class="solar-quick-card__eyebrow">Total ativo</span>
                <h3>{{ $summary['total'] }}</h3>
                <p>Orcamentos em qualquer etapa do fluxo comercial, da montagem ao fechamento.</p>
            </article>
            <article class="hub-card hub-card--subtle solar-quick-card">
                <span class="solar-quick-card__eyebrow">Em montagem</span>
                <h3>{{ $summary['draft'] }}</h3>
                <p>Orcamentos ainda sendo compostos com itens, custo real e preco final.</p>
            </article>
            <article class="hub-card hub-card--subtle solar-quick-card">
                <span class="solar-quick-card__eyebrow">Enviados</span>
                <h3>{{ $summary['sent'] }}</h3>
                <p>Orcamentos ja apresentados ao cliente e que pedem acompanhamento comercial ativo.</p>
            </article>
            <article class="hub-card hub-card--subtle solar-quick-card">
                <span class="solar-quick-card__eyebrow">Fechados</span>
                <h3>{{ $summary['won'] }}</h3>
                <p>Atendimentos convertidos em venda, prontos para leitura de ganho e historico.</p>
            </article>
            <article class="hub-card hub-card--subtle solar-quick-card">
                <span class="solar-quick-card__eyebrow">Follow-up vencido</span>
                <h3>{{ $summary['follow_up_due'] }}</h3>
                <p>Oportunidades que ja passaram do proximo contato programado e pedem acao comercial.</p>
            </article>
        </section>

        <section class="hub-card hub-card--subtle solar-filter-panel">
            <div class="solar-page-panel__header">
                <h2>Busca e filtros</h2>
                <p class="hub-note">Pesquise por orcamento, cliente, projeto ou simulacao e ordene a carteira pelo fechamento mais urgente.</p>
            </div>

            <form method="get" action="{{ route('solar.quotes.index') }}" class="solar-filter-grid solar-filter-grid--wide">
                <div>
                    <label for="quote-q" class="hub-auth-label">Buscar orcamento</label>
                    <input id="quote-q" name="q" type="text" class="hub-auth-input" value="{{ $filters['q'] }}" placeholder="Titulo, cliente, projeto ou simulacao">
                </div>

                <div>
                    <label for="quote-status" class="hub-auth-label">Status comercial</label>
                    <select id="quote-status" name="status" class="hub-auth-input">
                        @foreach ($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="quote-composition" class="hub-auth-label">Composicao</label>
                    <select id="quote-composition" name="composition" class="hub-auth-input">
                        @foreach ($compositionLabels as $value => $label)
                            <option value="{{ $value }}" @selected($filters['composition'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="quote-temperature" class="hub-auth-label">Temperatura</label>
                    <select id="quote-temperature" name="temperature" class="hub-auth-input">
                        @foreach ($temperatureLabels as $value => $label)
                            <option value="{{ $value }}" @selected($filters['temperature'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="quote-sort" class="hub-auth-label">Ordenar por</label>
                    <select id="quote-sort" name="sort" class="hub-auth-input">
                        @foreach ($sortOptions as $value => $label)
                            <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="solar-filter-actions">
                    <button type="submit" class="hub-btn">Aplicar filtros</button>
                    <a href="{{ route('solar.quotes.index') }}" class="hub-btn hub-btn--subtle">Limpar</a>
                </div>
            </form>

            <p class="solar-filter-summary">
                {{ $summary['filtered'] }} {{ $summary['filtered'] === 1 ? 'orcamento encontrado' : 'orcamentos encontrados' }}
                @if ($hasActiveFilters)
                    com os filtros atuais.
                @else
                    no pipeline comercial atual.
                @endif
            </p>
        </section>

        <article class="hub-card hub-card--subtle solar-project-show__card solar-project-simulations-panel">
            <div class="solar-flow-section__header">
                <div>
                    <p class="solar-section-eyebrow">Lista de orcamentos</p>
                    <h2>Orcamentos gerados</h2>
                </div>
                <div class="solar-project-showcase__status is-ready">
                    <span class="solar-project-showcase__status-label">Leitura operacional</span>
                    <strong>{{ $summary['total'] }} {{ $summary['total'] === 1 ? 'orcamento ativo' : 'orcamentos ativos' }}</strong>
                    <p>Novos orcamentos sao criados a partir da simulacao e acompanham o fluxo ate o fechamento.</p>
                </div>
            </div>

            <div class="solar-project-simulations-panel__grid">
                @forelse ($quotes as $quote)
                    @php
                        $resolvedFinalPrice = $quote->items->isNotEmpty() ? $quote->itemsTotalPrice() : $quote->final_price;
                        $simulationSnapshot = is_array($quote->simulation_snapshot_json) ? $quote->simulation_snapshot_json : [];
                    @endphp
                    <article class="solar-project-simulation-card">
                        <div class="solar-project-simulation-card__header">
                            <div>
                                <span class="solar-project-simulation-card__eyebrow">Orcamento</span>
                                <h3>{{ $quote->title }}</h3>
                            </div>
                            <div class="solar-project-simulation-card__chips">
                                <span class="solar-mini-badge solar-mini-badge--automatic">{{ $statusLabels[$quote->status] ?? strtoupper((string) $quote->status) }}</span>
                                @if ($quote->version_number)
                                    <span class="solar-mini-badge">V{{ str_pad((string) max((int) $quote->version_number, 1), 2, '0', STR_PAD_LEFT) }}</span>
                                @endif
                                <span class="solar-mini-badge">{{ $quote->items_count }} {{ $quote->items_count === 1 ? 'item' : 'itens' }}</span>
                            </div>
                        </div>

                        <div class="solar-project-simulation-card__body">
                            <p class="hub-note solar-project-simulation-card__summary">
                                {{ $quote->project?->customer?->name ?: 'Cliente nao vinculado' }}
                                @if ($simulationSnapshot['name'] ?? $quote->simulation)
                                    | origem: {{ $simulationSnapshot['name'] ?? $quote->simulation?->name }}
                                @endif
                                @if ($quote->proposal_code)
                                    <br><span class="hub-table__sub solar-table__meta">{{ $quote->proposal_code }}</span>
                                @endif
                            </p>

                            <div class="solar-project-simulation-card__metrics">
                                <span><strong>Itens</strong>{{ $quote->items_count }}</span>
                                <span><strong>Preco final</strong>{{ $resolvedFinalPrice ? 'R$ ' . number_format((float) $resolvedFinalPrice, 2, ',', '.') : '-' }}</span>
                                <span><strong>Economia</strong>{{ $quote->estimated_savings ? 'R$ ' . number_format((float) $quote->estimated_savings, 2, ',', '.') . '/mes' : '-' }}</span>
                                <span><strong>Payback</strong>{{ $quote->payback_months ? $quote->payback_months . ' meses' : '-' }}</span>
                                <span><strong>Responsavel</strong>{{ $quote->owner_name ?: '-' }}</span>
                                <span><strong>Temperatura</strong>{{ $temperatureStatusLabels[$quote->deal_temperature ?? 'warm'] ?? 'Morna' }}</span>
                                <span><strong>Proximo contato</strong>{{ $quote->next_contact_at?->format('d/m H:i') ?: '-' }}</span>
                            </div>
                        </div>

                        <div class="solar-project-simulation-card__footer">
                            <a href="{{ route('solar.quotes.edit', $quote->id) }}" class="hub-btn">Abrir orcamento</a>
                        </div>
                    </article>
                @empty
                    <article class="solar-project-simulation-card solar-project-simulation-card--empty">
                        <div class="solar-project-simulation-card__header">
                            <div>
                                <span class="solar-project-simulation-card__eyebrow">{{ $hasActiveFilters ? 'Sem resultados' : 'Sem orcamentos' }}</span>
                                <h3>{{ $hasActiveFilters ? 'Nenhum orcamento encontrado' : 'Nenhum orcamento criado' }}</h3>
                            </div>
                        </div>
                        <p class="hub-note">
                            {{ $hasActiveFilters
                                ? 'Os filtros atuais nao retornaram orcamentos. Ajuste os criterios para recuperar o pipeline desejado.'
                                : 'Abra uma simulacao e gere o primeiro orcamento para iniciar o fluxo comercial.' }}
                        </p>
                        @if ($hasActiveFilters)
                            <div class="solar-project-simulation-card__footer">
                                <a href="{{ route('solar.quotes.index') }}" class="hub-btn hub-btn--subtle">Limpar filtros</a>
                            </div>
                        @endif
                    </article>
                @endforelse
            </div>
        </article>
    </section>
@endsection
