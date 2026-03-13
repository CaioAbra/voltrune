@extends('solar.layout')

@section('title', 'Simulacoes | Solar')

@section('solar-content')
    <section class="hub-card solar-project-show solar-project-shell">
        <section class="hub-card hub-card--subtle solar-project-context-hero">
            <div class="solar-project-context-hero__header">
                <div>
                    <p class="solar-section-eyebrow">Simulacoes</p>
                    <h2>Biblioteca de cenarios</h2>
                    <p class="hub-note">Cada simulacao representa um cenario tecnico-comercial associado a um projeto, pronto para leitura e conversao em orcamento.</p>
                </div>

                <div class="solar-project-context-hero__focus">
                    <span class="solar-project-showcase__status-label">Leitura operacional</span>
                    <strong>{{ $simulations->count() }} {{ $simulations->count() === 1 ? 'cenario ativo' : 'cenarios ativos' }}</strong>
                    <p>Use esta biblioteca para revisar potencia, preco, contexto comercial e seguir para o fluxo de proposta.</p>
                </div>
            </div>
        </section>

        <article class="hub-card hub-card--subtle solar-project-show__card solar-project-simulations-panel">
            <div class="solar-flow-section__header">
                <div>
                    <p class="solar-section-eyebrow">Lista de simulacoes</p>
                    <h2>Cenarios tecnico-comerciais</h2>
                </div>
            </div>

            <div class="solar-project-simulations-panel__grid {{ $simulations->count() === 1 ? 'is-single' : '' }}">
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
                        </div>
                    </article>
                @empty
                    <article class="solar-project-simulation-card solar-project-simulation-card--empty">
                        <div class="solar-project-simulation-card__header">
                            <div>
                                <span class="solar-project-simulation-card__eyebrow">Sem simulacoes</span>
                                <h3>Nenhum cenario criado ainda</h3>
                            </div>
                        </div>
                        <p class="hub-note solar-project-simulation-card__summary">
                            As simulacoes serao geradas a partir dos projetos para preparar comparacoes e orcamentos multiplos.
                        </p>
                    </article>
                @endforelse
            </div>
        </article>
    </section>
@endsection
