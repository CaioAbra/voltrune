@extends('solar.layout')

@section('title', 'Projeto | Voltrune Solar')

@php
    $statusLabel = match ($project->status) {
        'draft' => 'Rascunho',
        'qualified' => 'Qualificado',
        'proposal' => 'Proposta',
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
        ? 'Abra a simulacao principal para revisar potencia, geracao, preco e indicadores financeiros antes de montar a proposta.'
        : 'O projeto concentra cliente, local e consumo. A primeira simulacao abre a leitura tecnica e comercial do cenario.';
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
                        Esta tela concentra cliente, local e consumo. As leituras de cenarios ficam nas simulacoes e a composicao comercial fica nos orcamentos.
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
                    <h2>Cenarios tecnico-comerciais</h2>
                    <p class="hub-note">Use esta lista para comparar alternativas e seguir para o orcamento certo.</p>
                </div>

                <div class="solar-project-showcase__status is-ready">
                    <span class="solar-project-showcase__status-label">Leitura operacional</span>
                    <strong>{{ $simulationCount }} {{ $simulationCount === 1 ? 'cenario ativo' : 'cenarios ativos' }}</strong>
                    <p>O projeto organiza o contexto. A simulacao e a tela principal de analise do Solar.</p>
                </div>
            </div>

            <div class="solar-project-simulations-panel__grid {{ $simulationCount === 1 ? 'is-single' : '' }}">
                @forelse ($simulations as $simulation)
                    @php
                        $simulationStatusLabel = match ($simulation->status) {
                            'draft' => 'Rascunho',
                            'qualified' => 'Em analise',
                            'proposal' => 'Pronta para proposta',
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
                                Cenario de leitura tecnica e comercial pronto para revisao e conversao em orcamento.
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
                                <h3>Crie o primeiro cenario deste projeto</h3>
                            </div>
                        </div>
                        <p class="hub-note solar-project-simulation-card__summary">
                            A simulacao vira a tela principal de leitura do Solar. Comece por ela para sair do contexto e entrar na analise.
                        </p>
                    </article>
                @endforelse
            </div>
        </article>

        <article class="hub-card hub-card--subtle solar-project-show__card solar-project-simulations-panel">
            <div class="solar-flow-section__header">
                <div>
                    <p class="solar-section-eyebrow">Orcamentos</p>
                    <h2>Propostas relacionadas</h2>
                    <p class="hub-note">Os orcamentos consolidam materiais, servicos, preco final e margem para envio ao cliente.</p>
                </div>

                <div class="solar-project-showcase__status is-market">
                    <span class="solar-project-showcase__status-label">Pipeline comercial</span>
                    <strong>{{ $quoteCount }} {{ $quoteCount === 1 ? 'proposta vinculada' : 'propostas vinculadas' }}</strong>
                    <p>Valide uma simulacao e siga para o orcamento quando o cenario estiver pronto.</p>
                </div>
            </div>

            <div class="solar-project-simulations-panel__grid">
                @forelse ($quotes as $quote)
                    @php
                        $quoteStatusLabel = match ($quote->status) {
                            'draft' => 'Rascunho',
                            'review' => 'Em analise',
                            'sent' => 'Enviado',
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
                            <span class="solar-mini-badge solar-mini-badge--automatic">{{ $quoteStatusLabel }}</span>
                        </div>

                        <div class="solar-project-simulation-card__body">
                            <p class="hub-note solar-project-simulation-card__summary">{{ $simulationSnapshot['name'] ?? $quote->simulation?->name ?: 'Sem simulacao vinculada' }}</p>

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
                                <h3>Nenhuma proposta criada ainda</h3>
                            </div>
                        </div>
                        <p class="hub-note solar-project-simulation-card__summary">
                            Valide uma simulacao e gere o primeiro orcamento quando o cenario estiver pronto.
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
