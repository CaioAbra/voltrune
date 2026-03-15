@extends('solar.layout')

@section('title', 'Simulacao | Voltrune Solar')

@php
    $projectAddress = collect([
        $project->street ?: null,
        $project->number ?: null,
        $project->complement ?: null,
        $project->district ?: null,
        $project->city ?: null,
        $project->state ?: null,
    ])->filter()->implode(', ');
    $statusLabel = match ($simulation->status) {
        'draft' => 'Rascunho',
        'qualified' => 'Em analise',
        'proposal' => 'Pronta para proposta',
        'won' => 'Fechado',
        default => strtoupper((string) $simulation->status),
    };
    $solarSourceLabel = strtoupper(($simulation->solar_factor_source ?? 'fallback') === 'pvgis' ? 'PVGIS' : 'PADRAO');
    $radiationDaily = $sizingService->estimateEquivalentSolarRadiationDaily($simulation->solar_factor_used);
    $quoteCount = $simulation->quotes->count();
    $readyForProposal = $simulation->suggested_price && $simulation->system_power_kwp;
    $marginLabel = match ($marginContext['source']) {
        'range' => $marginContext['requires_negotiation']
            ? 'Negociacao manual'
            : number_format((float) $marginContext['margin_percent'], 2, ',', '.') . '%',
        'unmatched' => 'Sem faixa',
        'pending' => 'Aguardando potencia',
        'default' => number_format((float) \App\Modules\Solar\Services\SolarSizingService::DEFAULT_GROSS_MARGIN_PERCENT, 2, ',', '.') . '%',
        default => $marginContext['margin_percent'] !== null
            ? number_format((float) $marginContext['margin_percent'], 2, ',', '.') . '%'
            : 'Nao configurada',
    };
    $marginNote = match ($marginContext['source']) {
        'range' => $marginContext['requires_negotiation']
            ? 'A configuracao comercial da empresa exige tratativa manual nesta faixa de potencia.'
            : 'Margem vinda da faixa de potencia configurada pela empresa.',
        'unmatched' => 'A potencia atual nao encontrou nenhuma faixa cadastrada para margem automatica.',
        'pending' => 'A margem por faixa depende da potencia calculada do sistema.',
        'default' => 'Sem margem fixa cadastrada. O Solar usa o padrao interno como referencia.',
        default => 'Margem de referencia da configuracao comercial da empresa.',
    };
@endphp

@section('solar-content')
    <section class="hub-card solar-project-show solar-project-shell">
        <div class="hub-actions solar-project-show__actions">
            <form action="{{ route('solar.simulations.quotes.store', $simulation->id) }}" method="POST">
                @csrf
                <button type="submit" class="hub-btn">Gerar orcamento</button>
            </form>

            <form action="{{ route('solar.simulations.duplicate', $simulation->id) }}" method="POST">
                @csrf
                <button type="submit" class="hub-btn hub-btn--subtle">Duplicar simulacao</button>
            </form>

            <a href="{{ route('solar.simulations.edit', $simulation->id) }}" class="hub-btn hub-btn--subtle">Editar simulacao</a>
            <a href="{{ route('solar.projects.show', $project->id) }}" class="hub-btn hub-btn--subtle">Voltar ao projeto</a>
        </div>

        <section class="hub-card hub-card--subtle solar-project-showcase">
            <div class="solar-project-showcase__header">
                <div>
                    <p class="solar-section-eyebrow">Simulacao solar</p>
                    <h2>{{ $simulation->name }}</h2>
                    <p class="hub-note">
                        Esta e a tela principal de leitura do cenario. Revise sistema, indicadores financeiros e siga para o orcamento quando estiver pronto.
                    </p>

                    <div class="solar-project-showcase__chips">
                        <span class="solar-mini-badge solar-mini-badge--editable">{{ $statusLabel }}</span>
                        <span class="solar-mini-badge solar-mini-badge--automatic">{{ $quoteCount }} {{ $quoteCount === 1 ? 'orcamento' : 'orcamentos' }}</span>
                        <span class="solar-mini-badge">{{ $project->name }}</span>
                    </div>
                </div>

                <div class="solar-project-showcase__status {{ $readyForProposal ? 'is-ready' : 'is-market' }}">
                    <span class="solar-project-showcase__status-label">Leitura comercial</span>
                    <strong>{{ $readyForProposal ? 'Cenario pronto para orcamento' : 'Revise a base antes de propor' }}</strong>
                    <p>
                        {{ $readyForProposal ? 'A simulacao ja apresenta potencia e preco sugerido. O passo seguinte e gerar um orcamento com composicao real.' : 'Complete os dados do cenario para deixar a proposta mais consistente.' }}
                    </p>

                    @if ($latestQuote)
                        <a href="{{ route('solar.quotes.edit', $latestQuote->id) }}" class="hub-btn solar-project-showcase__cta">Abrir ultimo orcamento</a>
                    @endif
                </div>
            </div>

            <div class="solar-project-showcase__hero-grid">
                <article class="solar-project-showcase-metric solar-project-showcase-metric--energy">
                    <span class="solar-project-showcase-metric__label">Potencia do sistema</span>
                    <strong class="solar-project-showcase-metric__value" data-show-animate-number data-show-format="kwp" data-show-value="{{ $simulation->system_power_kwp ?: '' }}">
                        {{ $simulation->system_power_kwp ? number_format((float) $simulation->system_power_kwp, 2, ',', '.') . ' kWp' : '-' }}
                    </strong>
                </article>
                <article class="solar-project-showcase-metric solar-project-showcase-metric--highlight">
                    <span class="solar-project-showcase-metric__label">Preco sugerido</span>
                    <strong class="solar-project-showcase-metric__value" data-show-animate-number data-show-format="currency" data-show-value="{{ $simulation->suggested_price ?: '' }}">
                        {{ $simulation->suggested_price ? 'R$ ' . number_format((float) $simulation->suggested_price, 2, ',', '.') : '-' }}
                    </strong>
                </article>
                <article class="solar-project-showcase-metric solar-project-showcase-metric--commercial">
                    <span class="solar-project-showcase-metric__label">Economia mensal</span>
                    <strong class="solar-project-showcase-metric__value" data-show-animate-number data-show-format="currency" data-show-value="{{ $simulation->estimated_monthly_savings ?: '' }}">
                        {{ $simulation->estimated_monthly_savings ? 'R$ ' . number_format((float) $simulation->estimated_monthly_savings, 2, ',', '.') : '-' }}
                    </strong>
                </article>
                <article class="solar-project-showcase-metric">
                    <span class="solar-project-showcase-metric__label">Payback estimado</span>
                    <strong class="solar-project-showcase-metric__value" data-show-animate-number data-show-format="months" data-show-value="{{ $simulation->estimated_payback_months ?: '' }}">
                        {{ $simulation->estimated_payback_months ? $simulation->estimated_payback_months . ' meses' : '-' }}
                    </strong>
                </article>
            </div>
        </section>

        <div class="hub-grid solar-project-show__grid">
            <section class="hub-card hub-card--subtle solar-project-show__card solar-sizing-panel">
                <div class="solar-flow-section__header solar-flow-section__header--stacked-md">
                    <div>
                        <p class="solar-section-eyebrow">Sistema tecnico</p>
                        <h2>Composicao sugerida do sistema</h2>
                        <p class="hub-note">Os dados tecnicos continuam presentes, mas com peso visual menor que os indicadores comerciais.</p>
                    </div>
                    <div class="solar-project-showcase__status is-ready">
                        <span class="solar-project-showcase__status-label">Origem do fator solar</span>
                        <strong>{{ $solarSourceLabel }}</strong>
                        <p>{{ $projectAddress !== '' ? $projectAddress : 'Endereco pendente' }}</p>
                    </div>
                </div>

                <div class="solar-sizing-panel__highlights">
                    <article class="solar-sizing-chip"><span class="solar-sizing-chip__label">Modulos</span><strong class="solar-sizing-chip__value">{{ $simulation->module_quantity ?: '-' }}</strong></article>
                    <article class="solar-sizing-chip"><span class="solar-sizing-chip__label">Potencia do modulo</span><strong class="solar-sizing-chip__value">{{ $simulation->module_power ? number_format((int) $simulation->module_power, 0, ',', '.') . ' W' : '-' }}</strong></article>
                    <article class="solar-sizing-chip"><span class="solar-sizing-chip__label">Inversor</span><strong class="solar-sizing-chip__value">{{ $simulation->inverter_model ?: '-' }}</strong></article>
                    <article class="solar-sizing-chip"><span class="solar-sizing-chip__label">Area estimada</span><strong class="solar-sizing-chip__value">{{ $simulation->area_estimated ? number_format((float) $simulation->area_estimated, 2, ',', '.') . ' m2' : '-' }}</strong></article>
                    <article class="solar-sizing-chip solar-sizing-chip--featured"><span class="solar-sizing-chip__label">Geracao estimada</span><strong class="solar-sizing-chip__value">{{ $simulation->estimated_generation_kwh ? number_format((float) $simulation->estimated_generation_kwh, 2, ',', '.') . ' kWh/mes' : '-' }}</strong></article>
                    <article class="solar-sizing-chip"><span class="solar-sizing-chip__label">Fator solar</span><strong class="solar-sizing-chip__value">{{ $simulation->solar_factor_used ? number_format((float) $simulation->solar_factor_used, 2, ',', '.') . ' kWh/kWp/mes' : '-' }}</strong></article>
                    <article class="solar-sizing-chip"><span class="solar-sizing-chip__label">Radiacao equivalente</span><strong class="solar-sizing-chip__value">{{ $simulation->solar_factor_used ? number_format((float) $radiationDaily, 2, ',', '.') . ' kWh/m2/dia' : '-' }}</strong></article>
                    <article class="solar-sizing-chip"><span class="solar-sizing-chip__label">Local base</span><strong class="solar-sizing-chip__value">{{ $projectAddress !== '' ? $projectAddress : 'Endereco pendente' }}</strong></article>
                </div>
            </section>

            <section class="hub-card hub-card--subtle solar-flow-section solar-financial-panel">
                <div class="solar-flow-section__header solar-flow-section__header--stacked-md">
                    <div>
                        <p class="solar-section-eyebrow">Indicadores financeiros</p>
                        <h2>Leitura comercial do cenario</h2>
                        <p class="hub-note">Aqui ficam os numeros principais para defender a proposta e comparar alternativas.</p>
                    </div>
                    <div class="solar-project-showcase__status is-market">
                        <span class="solar-project-showcase__status-label">Proposta</span>
                        <strong>{{ $quoteCount > 0 ? 'Ja existe orcamento vinculado' : 'Pronta para virar proposta' }}</strong>
                        <p>{{ $quoteCount > 0 ? 'Revise o orcamento mais recente para fechar composicao, margem e envio.' : 'Gere um orcamento para montar materiais, servicos e fechamento comercial.' }}</p>
                    </div>
                </div>

                <div class="solar-sizing-panel__highlights solar-financial-panel__highlights">
                    <article class="solar-sizing-chip solar-sizing-chip--commercial"><span class="solar-sizing-chip__label">Economia mensal</span><strong class="solar-sizing-chip__value">{{ $simulation->estimated_monthly_savings ? 'R$ ' . number_format((float) $simulation->estimated_monthly_savings, 2, ',', '.') : '-' }}</strong></article>
                    <article class="solar-sizing-chip"><span class="solar-sizing-chip__label">Economia anual</span><strong class="solar-sizing-chip__value">{{ $simulation->estimated_annual_savings ? 'R$ ' . number_format((float) $simulation->estimated_annual_savings, 2, ',', '.') : '-' }}</strong></article>
                    <article class="solar-sizing-chip"><span class="solar-sizing-chip__label">ROI</span><strong class="solar-sizing-chip__value">{{ $simulation->estimated_roi ? number_format((float) $simulation->estimated_roi, 1, ',', '.') . '%' : '-' }}</strong></article>
                    <article class="solar-sizing-chip"><span class="solar-sizing-chip__label">Payback</span><strong class="solar-sizing-chip__value">{{ $simulation->estimated_payback_months ? $simulation->estimated_payback_months . ' meses' : '-' }}</strong></article>
                    <article class="solar-sizing-chip"><span class="solar-sizing-chip__label">Margem aplicada</span><strong class="solar-sizing-chip__value">{{ $marginLabel }}</strong></article>
                    <article class="solar-sizing-chip solar-sizing-chip--featured"><span class="solar-sizing-chip__label">Economia em 25 anos</span><strong class="solar-sizing-chip__value">{{ $simulation->estimated_lifetime_savings ? 'R$ ' . number_format((float) $simulation->estimated_lifetime_savings, 2, ',', '.') : '-' }}</strong></article>
                </div>

                <p class="solar-sizing-panel__note solar-financial-panel__note">
                    {{ $marginNote }} Compare cenarios antes de enviar ao cliente para manter a proposta enxuta, coerente e defendida por indicadores claros.
                </p>
            </section>
        </div>
    </section>
@endsection
