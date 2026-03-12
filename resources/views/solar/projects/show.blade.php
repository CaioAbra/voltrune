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
    $geocodingPrecisionLabel = match ($project->geocoding_precision ?: 'fallback') {
        'address' => 'Endereco refinado',
        'city' => 'Cidade aproximada',
        default => 'Fallback padrao',
    };
    $geocodingStatusLabel = match ($project->geocoding_status ?: 'pending') {
        'ready' => 'Localizacao pronta',
        'not_found' => 'Localizacao nao encontrada',
        'address_loaded' => 'Endereco parcial carregado',
        'not_requested' => 'Aguardando CEP',
        default => 'Buscando melhor localizacao',
    };
    $simulationCount = $simulations->count();
    $primarySimulation = $defaultSimulation;
@endphp

@section('solar-content')
    <section class="hub-card solar-project-show solar-project-shell">
        <div class="hub-actions solar-project-show__actions">
            <a href="{{ route('solar.projects.index') }}" class="hub-btn hub-btn--subtle">Voltar para projetos</a>
            <a href="{{ route('solar.projects.edit', $project->id) }}" class="hub-btn">Editar projeto</a>
        </div>

        <section class="hub-card hub-card--subtle solar-project-context-hero">
            <div class="solar-project-context-hero__header">
                <div>
                    <p class="solar-section-eyebrow">Projeto base</p>
                    <h2>{{ $project->name }}</h2>
                    <p class="hub-note">O projeto guarda cliente, local de instalacao, consumo base e automacoes do endereco. Os cenarios comerciais vivem nas simulacoes.</p>
                    <div class="solar-project-showcase__chips">
                        <span class="solar-mini-badge solar-mini-badge--automatic">{{ $statusLabel }}</span>
                        <span class="solar-mini-badge solar-mini-badge--editable">{{ $locationSummary !== '' ? $locationSummary : 'Local pendente' }}</span>
                        <span class="solar-mini-badge solar-mini-badge--automatic">{{ $simulationCount }} {{ $simulationCount === 1 ? 'simulacao' : 'simulacoes' }}</span>
                    </div>
                </div>

                <div class="solar-project-context-hero__focus">
                    <span class="solar-project-showcase__status-label">Leitura principal</span>
                    <strong>{{ $primarySimulation?->name ?: 'Crie a primeira simulacao' }}</strong>
                    <p>
                        @if ($primarySimulation)
                            Use a simulacao como centro tecnico/comercial para revisar sistema, retorno, proposta e comparacao entre cenarios.
                        @else
                            Assim que a primeira simulacao for criada, ela passa a concentrar o cenario tecnico/comercial do projeto.
                        @endif
                    </p>
                    @if ($primarySimulation)
                        <a href="{{ route('solar.simulations.show', $primarySimulation->id) }}" class="hub-btn">Abrir simulacao principal</a>
                    @endif
                </div>
            </div>

            <div class="solar-project-context-hero__grid">
                <article class="solar-project-context-tile">
                    <span class="solar-project-context-tile__label">Cliente</span>
                    <strong class="solar-project-context-tile__value">{{ $project->customer?->name ?: '-' }}</strong>
                </article>
                <article class="solar-project-context-tile">
                    <span class="solar-project-context-tile__label">Endereco base</span>
                    <strong class="solar-project-context-tile__value">{{ $displayAddress !== '' ? $displayAddress : 'Endereco em preparacao' }}</strong>
                </article>
                <article class="solar-project-context-tile">
                    <span class="solar-project-context-tile__label">Consumo base</span>
                    <strong class="solar-project-context-tile__value">{{ $project->monthly_consumption_kwh ? number_format((float) $project->monthly_consumption_kwh, 2, ',', '.') . ' kWh/mes' : '-' }}</strong>
                </article>
                <article class="solar-project-context-tile">
                    <span class="solar-project-context-tile__label">Concessionaria</span>
                    <strong class="solar-project-context-tile__value">{{ $project->utility_company ?: '-' }}</strong>
                </article>
            </div>
        </section>

        <div class="hub-grid hub-grid--billing solar-project-show__grid">
            <article class="hub-card hub-card--subtle solar-project-show__card">
                <h2>Contexto do local</h2>
                <div class="solar-project-show__info-grid">
                    <p><strong>Cliente</strong><span>{{ $project->customer?->name ?: '-' }}</span></p>
                    <p><strong>Endereco</strong><span>{{ $displayAddress !== '' ? $displayAddress : 'Endereco ainda em preparacao.' }}</span></p>
                    <p><strong>CEP</strong><span>{{ $project->zip_code ?: '-' }}</span></p>
                    <p><strong>Cidade / UF</strong><span>{{ $locationSummary !== '' ? $locationSummary : '-' }}</span></p>
                    <p><strong>Tipo de imovel</strong><span>{{ $project->property_type ?: '-' }}</span></p>
                    <p><strong>Status base</strong><span>{{ $statusLabel }}</span></p>
                </div>
            </article>

            <article class="hub-card hub-card--subtle solar-project-show__card">
                <h2>Geolocalizacao e automacao</h2>
                <div class="solar-project-show__info-grid">
                    <p><strong>Geocodificacao</strong><span>{{ $geocodingStatusLabel }}</span></p>
                    <p><strong>Precisao</strong><span>{{ $geocodingPrecisionLabel }}</span></p>
                    <p><strong>Latitude</strong><span>{{ $project->latitude !== null ? number_format((float) $project->latitude, 6, ',', '.') : '-' }}</span></p>
                    <p><strong>Longitude</strong><span>{{ $project->longitude !== null ? number_format((float) $project->longitude, 6, ',', '.') : '-' }}</span></p>
                    <p><strong>Concessionaria</strong><span>{{ $project->utility_company ?: '-' }}</span></p>
                    <p><strong>Conta de energia</strong><span>{{ $project->energy_bill_value ? 'R$ ' . number_format((float) $project->energy_bill_value, 2, ',', '.') : '-' }}</span></p>
                </div>
            </article>
        </div>

        <article class="hub-card hub-card--subtle solar-project-show__card solar-project-simulations-panel">
            <div class="solar-flow-section__header">
                <div>
                    <p class="solar-section-eyebrow">Simulacoes</p>
                    <h2>Cenarios tecnico-comerciais</h2>
                </div>
                <form method="POST" action="{{ route('solar.projects.simulations.store', $project->id) }}">
                    @csrf
                    <button type="submit" class="hub-btn">Criar nova simulacao</button>
                </form>
            </div>

            <p class="hub-note">Cada simulacao concentra resultado, sistema, custos, retorno e proposta. O projeto fica como base do local e do consumo.</p>

            <div class="solar-project-simulations-panel__grid">
                @forelse ($simulations as $simulation)
                    @php
                        $simulationStatusLabel = match ($simulation->status) {
                            'draft' => 'Rascunho',
                            'qualified' => 'Qualificada',
                            'proposal' => 'Proposta',
                            'won' => 'Fechada',
                            default => strtoupper((string) $simulation->status),
                        };
                        $simulationCardClasses = collect([
                            'solar-project-simulation-card',
                            $loop->first ? 'is-primary' : null,
                            $simulationCount === 1 ? 'is-single' : null,
                        ])->filter()->implode(' ');
                    @endphp
                    <article class="{{ $simulationCardClasses }}">
                        <div class="solar-project-simulation-card__header">
                            <div>
                                <span class="solar-project-simulation-card__eyebrow">{{ $loop->first ? 'Simulacao principal' : 'Simulacao' }}</span>
                                <h3>{{ $simulation->name }}</h3>
                            </div>
                            <span class="solar-mini-badge {{ $loop->first ? 'solar-mini-badge--automatic' : 'solar-mini-badge--editable' }}">{{ $simulationStatusLabel }}</span>
                        </div>

                        <div class="solar-project-simulation-card__body">
                            <p class="hub-note solar-project-simulation-card__summary">Abra a simulacao para revisar composicao, custos por grupo e leitura financeira completa.</p>

                            <div class="solar-project-simulation-card__metrics">
                                <span><strong>Potencia</strong>{{ $simulation->system_power_kwp ? number_format((float) $simulation->system_power_kwp, 2, ',', '.') . ' kWp' : '-' }}</span>
                                <span><strong>Geracao</strong>{{ $simulation->estimated_generation_kwh ? number_format((float) $simulation->estimated_generation_kwh, 2, ',', '.') . ' kWh/mes' : '-' }}</span>
                                <span><strong>Preco</strong>{{ $simulation->suggested_price ? 'R$ ' . number_format((float) $simulation->suggested_price, 2, ',', '.') : '-' }}</span>
                                <span><strong>Lucro bruto</strong>{{ $simulation->estimated_gross_profit ? 'R$ ' . number_format((float) $simulation->estimated_gross_profit, 2, ',', '.') : '-' }}</span>
                            </div>
                        </div>

                        <div class="solar-project-simulation-card__footer">
                            <a href="{{ route('solar.simulations.show', $simulation->id) }}" class="hub-btn {{ $loop->first ? '' : 'hub-btn--subtle' }}">Abrir simulacao</a>
                        </div>
                    </article>
                @empty
                    <article class="solar-project-simulation-card solar-project-simulation-card--empty">
                        <div class="solar-project-simulation-card__header">
                            <div>
                                <span class="solar-project-simulation-card__eyebrow">Sem cenarios</span>
                                <h3>Nenhuma simulacao criada</h3>
                            </div>
                        </div>
                        <p class="hub-note">Crie a primeira simulacao para transformar o contexto do projeto em um cenario tecnico/comercial real.</p>
                    </article>
                @endforelse
            </div>
        </article>

        @if ($project->pricing_notes || $project->notes)
            <article class="hub-card hub-card--subtle solar-project-show__card">
                <h2>Observacoes do projeto base</h2>
                @if ($project->pricing_notes)
                    <p><strong>Notas comerciais:</strong> {{ $project->pricing_notes }}</p>
                @endif
                @if ($project->notes)
                    <p><strong>Notas gerais:</strong> {{ $project->notes }}</p>
                @endif
            </article>
        @endif
    </section>
@endsection
