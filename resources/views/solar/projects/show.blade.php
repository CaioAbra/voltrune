@extends('solar.layout')

@section('title', 'Projeto | Voltrune Solar')

@php
    $statusLabel = match ($project->status) {
        'draft' => 'Base em montagem',
        'qualified' => 'Em revisao',
        'proposal' => 'Pronto para orcamento',
        'won' => 'Fechado',
        default => strtoupper((string) $project->status),
    };
    $locationSummary = collect([$project->city, $project->state])->filter()->implode(' / ');
    $displayAddress = $project->address ?: collect([
        $project->street ?: null,
        $project->number ?: null,
        $project->complement ?: null,
        $project->district ?: null,
        $project->city ?: null,
        $project->state ?: null,
    ])->filter()->implode(', ');
    $simulationCount = $simulations->count();
    $primarySimulation = $defaultSimulation;
    $quoteCount = $quotes->count();
    $nextStepLabel = $primarySimulation ? 'Abrir simulacao principal' : 'Criar a primeira simulacao';
    $nextStepMessage = $primarySimulation
        ? 'Abra a simulacao principal para revisar potencia, geracao, preco e indicadores financeiros antes de montar o orcamento.'
        : 'O projeto concentra cliente, local e consumo. A primeira simulacao abre a leitura tecnica e comercial do fluxo.';
    $comparisonRows = $simulations->map(function ($simulation) {
        $statusLabel = match ($simulation->status) {
            'draft' => 'Base automatica',
            'qualified' => 'Em revisao',
            'proposal' => 'Pronta para orcamento',
            'won' => 'Fechada',
            default => strtoupper((string) $simulation->status),
        };

        return [
            'id' => $simulation->id,
            'name' => $simulation->name,
            'status_label' => $statusLabel,
            'power' => $simulation->system_power_kwp !== null ? (float) $simulation->system_power_kwp : null,
            'generation' => $simulation->estimated_generation_kwh !== null ? (float) $simulation->estimated_generation_kwh : null,
            'price' => $simulation->suggested_price !== null ? (float) $simulation->suggested_price : null,
            'monthly_savings' => $simulation->estimated_monthly_savings !== null ? (float) $simulation->estimated_monthly_savings : null,
            'payback_months' => $simulation->estimated_payback_months !== null ? (int) $simulation->estimated_payback_months : null,
            'quotes_count' => $simulation->quotes->count(),
            'show_url' => route('solar.simulations.show', $simulation->id),
        ];
    })->values();
    $bestPayback = $comparisonRows
        ->filter(fn (array $row) => $row['payback_months'] !== null)
        ->sortBy('payback_months')
        ->first();
    $highestSavings = $comparisonRows
        ->filter(fn (array $row) => $row['monthly_savings'] !== null)
        ->sortByDesc('monthly_savings')
        ->first();
    $lowestPrice = $comparisonRows
        ->filter(fn (array $row) => $row['price'] !== null)
        ->sortBy('price')
        ->first();
    $comparisonHighlights = collect([
        $bestPayback ? [
            'label' => 'Melhor retorno',
            'title' => $bestPayback['name'],
            'value' => $bestPayback['payback_months'] . ' meses',
            'detail' => 'Menor payback entre as simulacoes ativas.',
        ] : null,
        $highestSavings ? [
            'label' => 'Maior economia mensal',
            'title' => $highestSavings['name'],
            'value' => 'R$ ' . number_format((float) $highestSavings['monthly_savings'], 2, ',', '.'),
            'detail' => 'Melhor leitura mensal para defender valor percebido.',
        ] : null,
        $lowestPrice ? [
            'label' => 'Menor preco sugerido',
            'title' => $lowestPrice['name'],
            'value' => 'R$ ' . number_format((float) $lowestPrice['price'], 2, ',', '.'),
            'detail' => 'Referencia de entrada para propostas mais enxutas.',
        ] : null,
    ])->filter()->values();
    $comparisonRows = $comparisonRows->map(function (array $row) use ($bestPayback, $highestSavings, $lowestPrice) {
        $leaderLabels = [];

        if (($bestPayback['id'] ?? null) === $row['id']) {
            $leaderLabels[] = 'Melhor payback';
        }

        if (($highestSavings['id'] ?? null) === $row['id']) {
            $leaderLabels[] = 'Maior economia';
        }

        if (($lowestPrice['id'] ?? null) === $row['id']) {
            $leaderLabels[] = 'Menor preco';
        }

        $row['leader_labels'] = $leaderLabels;

        return $row;
    });
@endphp

@section('solar-content')
    <section class="hub-card solar-project-show solar-project-shell">
        <div class="hub-actions solar-project-show__actions">
            <form method="POST" action="{{ route('solar.projects.simulations.store', $project->id) }}">
                @csrf
                <button type="submit" class="hub-btn">Nova simulacao</button>
            </form>

            @if ($primarySimulation)
                <form method="POST" action="{{ route('solar.simulations.quotes.store', $primarySimulation->id) }}">
                    @csrf
                    <button type="submit" class="hub-btn hub-btn--subtle">Novo orcamento</button>
                </form>
            @endif

            <a href="{{ route('solar.projects.edit', $project->id) }}" class="hub-btn hub-btn--subtle">Editar projeto</a>
            <a href="{{ route('solar.projects.index') }}" class="hub-btn hub-btn--subtle">Voltar para projetos</a>
        </div>

        <section class="hub-card hub-card--subtle solar-project-showcase">
            <div class="solar-project-showcase__header">
                <div>
                    <p class="solar-section-eyebrow">Projeto solar</p>
                    <h2>{{ $project->name }}</h2>
                    <p class="hub-note">
                        Esta tela concentra cliente, local e consumo. A leitura da simulacao e o fechamento do orcamento acontecem nas telas seguintes.
                    </p>

                    <div class="solar-project-showcase__chips">
                        <span class="solar-mini-badge solar-mini-badge--editable">{{ $statusLabel }}</span>
                        <span class="solar-mini-badge solar-mini-badge--automatic">{{ $simulationCount }} {{ $simulationCount === 1 ? 'simulacao' : 'simulacoes' }}</span>
                        <span class="solar-mini-badge">{{ $quoteCount }} {{ $quoteCount === 1 ? 'orcamento' : 'orcamentos' }}</span>
                    </div>
                </div>

                <div class="solar-project-showcase__status {{ $primarySimulation ? 'is-ready' : 'is-market' }}">
                    <span class="solar-project-showcase__status-label">Proximo passo</span>
                    <strong>{{ $nextStepLabel }}</strong>
                    <p>{{ $nextStepMessage }}</p>

                    @if ($primarySimulation)
                        <a href="{{ route('solar.simulations.show', $primarySimulation->id) }}" class="hub-btn solar-project-showcase__cta">Ver simulacao principal</a>
                    @else
                        <form method="POST" action="{{ route('solar.projects.simulations.store', $project->id) }}">
                            @csrf
                            <button type="submit" class="hub-btn solar-project-showcase__cta">Criar simulacao</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="solar-project-context-hero__grid">
                <article class="solar-project-context-tile">
                    <span class="solar-project-context-tile__label">Cliente</span>
                    <strong class="solar-project-context-tile__value">{{ $project->customer?->name ?: '-' }}</strong>
                </article>
                <article class="solar-project-context-tile">
                    <span class="solar-project-context-tile__label">Endereco</span>
                    <strong class="solar-project-context-tile__value">{{ $displayAddress !== '' ? $displayAddress : 'Endereco em preparacao' }}</strong>
                </article>
                <article class="solar-project-context-tile">
                    <span class="solar-project-context-tile__label">Cidade / Estado</span>
                    <strong class="solar-project-context-tile__value">{{ $locationSummary !== '' ? $locationSummary : '-' }}</strong>
                </article>
                <article class="solar-project-context-tile">
                    <span class="solar-project-context-tile__label">Concessionaria</span>
                    <strong class="solar-project-context-tile__value">{{ $project->utility_company ?: '-' }}</strong>
                </article>
                <article class="solar-project-context-tile">
                    <span class="solar-project-context-tile__label">Consumo mensal</span>
                    <strong class="solar-project-context-tile__value">
                        {{ $project->monthly_consumption_kwh ? number_format((float) $project->monthly_consumption_kwh, 2, ',', '.') . ' kWh/mes' : '-' }}
                    </strong>
                </article>
            </div>
        </section>

        <article class="hub-card hub-card--subtle solar-project-show__card solar-project-simulations-panel">
            <div class="solar-flow-section__header">
                <div>
                    <p class="solar-section-eyebrow">Simulacoes</p>
                    <h2>Simulacoes do projeto</h2>
                    <p class="hub-note">Use esta lista para revisar simulacoes, comparar alternativas e seguir para o orcamento certo.</p>
                </div>

                <div class="solar-project-showcase__status is-ready">
                    <span class="solar-project-showcase__status-label">Leitura operacional</span>
                    <strong>{{ $simulationCount }} {{ $simulationCount === 1 ? 'simulacao ativa' : 'simulacoes ativas' }}</strong>
                    <p>O projeto organiza a base do atendimento. A simulacao e a tela principal de revisao tecnica e comercial.</p>
                </div>
            </div>

            <div class="solar-project-simulations-panel__grid {{ $simulationCount === 1 ? 'is-single' : '' }}">
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
                    <article class="solar-project-simulation-card {{ $loop->first ? 'is-primary' : '' }}">
                        <div class="solar-project-simulation-card__header">
                            <div>
                                <span class="solar-project-simulation-card__eyebrow">{{ $loop->first ? 'Simulacao principal' : 'Simulacao' }}</span>
                                <h3>{{ $simulation->name }}</h3>
                            </div>
                            <span class="solar-mini-badge {{ $loop->first ? 'solar-mini-badge--editable' : 'solar-mini-badge--automatic' }}">{{ $simulationStatusLabel }}</span>
                        </div>

                        <div class="solar-project-simulation-card__body">
                            <p class="hub-note solar-project-simulation-card__summary">
                                Simulacao pronta para revisao tecnica e comercial antes da montagem do orcamento.
                            </p>

                            <div class="solar-project-simulation-card__metrics">
                                <span><strong>Potencia</strong>{{ $simulation->system_power_kwp ? number_format((float) $simulation->system_power_kwp, 2, ',', '.') . ' kWp' : '-' }}</span>
                                <span><strong>Geracao estimada</strong>{{ $simulation->estimated_generation_kwh ? number_format((float) $simulation->estimated_generation_kwh, 2, ',', '.') . ' kWh/mes' : '-' }}</span>
                                <span><strong>Preco sugerido</strong>{{ $simulation->suggested_price ? 'R$ ' . number_format((float) $simulation->suggested_price, 2, ',', '.') : '-' }}</span>
                                <span><strong>Economia mensal</strong>{{ $simulation->estimated_monthly_savings ? 'R$ ' . number_format((float) $simulation->estimated_monthly_savings, 2, ',', '.') : '-' }}</span>
                            </div>
                        </div>

                        <div class="solar-project-simulation-card__footer">
                            <a href="{{ route('solar.simulations.show', $simulation->id) }}" class="hub-btn">Ver simulacao</a>

                            <div class="solar-project-simulation-card__footer-secondary">
                                <form action="{{ route('solar.simulations.quotes.store', $simulation->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="hub-btn hub-btn--subtle">Gerar orcamento</button>
                                </form>

                                <form action="{{ route('solar.simulations.duplicate', $simulation->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="hub-link-secondary">Duplicar</button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <article class="solar-project-simulation-card solar-project-simulation-card--empty">
                        <div class="solar-project-simulation-card__header">
                            <div>
                                <span class="solar-project-simulation-card__eyebrow">Sem simulacoes</span>
                                <h3>Crie a primeira simulacao deste projeto</h3>
                            </div>
                        </div>
                        <p class="hub-note solar-project-simulation-card__summary">
                            A simulacao vira a tela principal de leitura do Solar. Comece por ela para sair do cadastro base e entrar na revisao.
                        </p>
                    </article>
                @endforelse
            </div>
        </article>

        @if ($simulationCount > 1)
            <article class="hub-card hub-card--subtle solar-project-show__card solar-simulation-compare">
                <div class="solar-flow-section__header">
                    <div>
                        <p class="solar-section-eyebrow">Comparacao guiada</p>
                        <h2>O que muda entre as simulacoes</h2>
                        <p class="hub-note">Esta leitura resume retorno, preco, economia e maturidade comercial para ajudar a escolher a melhor opcao antes do orcamento.</p>
                    </div>

                    <div class="solar-project-showcase__status is-market">
                        <span class="solar-project-showcase__status-label">Decisao comercial</span>
                        <strong>{{ $comparisonRows->count() }} alternativas lado a lado</strong>
                        <p>Use os destaques para decidir rapido e abra a simulacao vencedora quando quiser aprofundar os detalhes.</p>
                    </div>
                </div>

                @if ($comparisonHighlights->isNotEmpty())
                    <div class="solar-simulation-compare__spotlights">
                        @foreach ($comparisonHighlights as $highlight)
                            <article class="solar-simulation-compare__spotlight">
                                <span class="solar-simulation-compare__spotlight-label">{{ $highlight['label'] }}</span>
                                <strong>{{ $highlight['title'] }}</strong>
                                <p>{{ $highlight['value'] }}</p>
                                <small>{{ $highlight['detail'] }}</small>
                            </article>
                        @endforeach
                    </div>
                @endif

                <div class="solar-table-wrap">
                    <table class="hub-table solar-table solar-table--simulation-compare">
                        <thead>
                            <tr>
                                <th>Simulacao</th>
                                <th>Potencia</th>
                                <th>Geracao</th>
                                <th>Preco sugerido</th>
                                <th>Economia mensal</th>
                                <th>Payback</th>
                                <th>Orcamentos</th>
                                <th>Abertura</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($comparisonRows as $row)
                                <tr class="solar-table__row">
                                    <td data-label="Simulacao" class="solar-table__cell solar-table__cell--primary">
                                        <strong class="solar-table__entity">{{ $row['name'] }}</strong>
                                        <div class="hub-table__sub solar-table__meta">{{ $row['status_label'] }}</div>

                                        @if ($row['leader_labels'] !== [])
                                            <div class="solar-project-simulation-card__chips">
                                                @foreach ($row['leader_labels'] as $leaderLabel)
                                                    <span class="solar-mini-badge solar-mini-badge--automatic">{{ $leaderLabel }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td data-label="Potencia" class="solar-table__cell">{{ $row['power'] !== null ? number_format((float) $row['power'], 2, ',', '.') . ' kWp' : '-' }}</td>
                                    <td data-label="Geracao" class="solar-table__cell">{{ $row['generation'] !== null ? number_format((float) $row['generation'], 2, ',', '.') . ' kWh/mes' : '-' }}</td>
                                    <td data-label="Preco sugerido" class="solar-table__cell">{{ $row['price'] !== null ? 'R$ ' . number_format((float) $row['price'], 2, ',', '.') : '-' }}</td>
                                    <td data-label="Economia mensal" class="solar-table__cell">{{ $row['monthly_savings'] !== null ? 'R$ ' . number_format((float) $row['monthly_savings'], 2, ',', '.') : '-' }}</td>
                                    <td data-label="Payback" class="solar-table__cell">{{ $row['payback_months'] !== null ? $row['payback_months'] . ' meses' : '-' }}</td>
                                    <td data-label="Orcamentos" class="solar-table__cell">{{ $row['quotes_count'] }}</td>
                                    <td data-label="Abertura" class="solar-table__cell solar-table__cell--actions">
                                        <a href="{{ $row['show_url'] }}" class="hub-btn hub-btn--subtle">Abrir</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>
        @endif

        <article class="hub-card hub-card--subtle solar-project-show__card solar-project-simulations-panel">
            <div class="solar-flow-section__header">
                <div>
                    <p class="solar-section-eyebrow">Orcamentos</p>
                    <h2>Orcamentos relacionados</h2>
                    <p class="hub-note">Os orcamentos consolidam itens, preco final e status comercial para o proximo passo do atendimento.</p>
                </div>

                <div class="solar-project-showcase__status is-market">
                    <span class="solar-project-showcase__status-label">Pipeline comercial</span>
                    <strong>{{ $quoteCount }} {{ $quoteCount === 1 ? 'orcamento vinculado' : 'orcamentos vinculados' }}</strong>
                    <p>Valide uma simulacao e siga para o orcamento quando a leitura estiver pronta.</p>
                </div>
            </div>

            <div class="solar-project-simulations-panel__grid">
                @forelse ($quotes as $quote)
                    @php
                        $quoteStatusLabel = match ($quote->status) {
                            'draft' => 'Em montagem',
                            'review' => 'Em revisao interna',
                            'sent' => 'Enviado ao cliente',
                            'approved' => 'Aprovado',
                            'won' => 'Fechado',
                            'lost' => 'Perdido',
                            default => strtoupper((string) $quote->status),
                        };
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
                                <span class="solar-mini-badge solar-mini-badge--automatic">{{ $quoteStatusLabel }}</span>
                                @if ($quote->version_number)
                                    <span class="solar-mini-badge">V{{ str_pad((string) $quote->version_number, 2, '0', STR_PAD_LEFT) }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="solar-project-simulation-card__body">
                            <p class="hub-note solar-project-simulation-card__summary">
                                {{ $simulationSnapshot['name'] ?? $quote->simulation?->name ?: 'Sem simulacao vinculada' }}
                                @if ($quote->proposal_code)
                                    <br><span class="hub-table__sub solar-table__meta">{{ $quote->proposal_code }}</span>
                                @endif
                            </p>

                            <div class="solar-project-simulation-card__metrics">
                                <span><strong>Preco final</strong>{{ $resolvedFinalPrice ? 'R$ ' . number_format((float) $resolvedFinalPrice, 2, ',', '.') : '-' }}</span>
                                <span><strong>Itens</strong>{{ $quote->items->count() }}</span>
                                <span><strong>Economia</strong>{{ $quote->estimated_savings ? 'R$ ' . number_format((float) $quote->estimated_savings, 2, ',', '.') . '/mes' : '-' }}</span>
                                <span><strong>Status</strong>{{ $quoteStatusLabel }}</span>
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
                                <span class="solar-project-simulation-card__eyebrow">Sem orcamentos</span>
                                <h3>Nenhum orcamento criado ainda</h3>
                            </div>
                        </div>
                        <p class="hub-note solar-project-simulation-card__summary">
                            Valide uma simulacao e gere o primeiro orcamento quando a leitura estiver pronta.
                        </p>
                    </article>
                @endforelse
            </div>
        </article>

        @if ($project->pricing_notes || $project->notes)
            <article class="hub-card hub-card--subtle solar-project-show__card">
                <p class="solar-section-eyebrow">Observacoes</p>
                <h2>Anotacoes do projeto base</h2>

                <div class="solar-project-show__info-grid">
                    @if ($project->pricing_notes)
                        <p>
                            <strong>Notas comerciais</strong>
                            <span>{{ $project->pricing_notes }}</span>
                        </p>
                    @endif

                    @if ($project->notes)
                        <p>
                            <strong>Notas gerais</strong>
                            <span>{{ $project->notes }}</span>
                        </p>
                    @endif
                </div>
            </article>
        @endif
    </section>
@endsection
