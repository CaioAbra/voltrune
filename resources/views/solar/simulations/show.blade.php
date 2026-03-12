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
        'qualified' => 'Qualificado',
        'proposal' => 'Proposta',
        'won' => 'Fechado',
        default => strtoupper((string) $simulation->status),
    };
    $solarSourceLabel = strtoupper(($simulation->solar_factor_source ?? 'fallback') === 'pvgis' ? 'PVGIS' : 'PADRAO');
    $radiationDaily = $sizingService->estimateEquivalentSolarRadiationDaily($simulation->solar_factor_used);
    $composition = $simulation->system_composition_json ?: [];
    $quoteCount = $simulation->quotes->count();
    $readyForProposal = $simulation->suggested_price && $simulation->system_power_kwp;
@endphp

@section('solar-content')
    <section class="hub-card solar-project-show">
        <div class="hub-actions solar-project-show__actions">
            <a href="{{ route('solar.projects.show', $project->id) }}" class="hub-btn hub-btn--subtle">Voltar para projeto</a>
            <a href="{{ route('solar.simulations.index') }}" class="hub-btn hub-btn--subtle">Todas as simulacoes</a>
        </div>

        <section class="hub-card hub-card--subtle solar-project-showcase" data-solar-showcase>
            <div class="solar-project-showcase__header">
                <div>
                    <p class="solar-section-eyebrow">Simulacao</p>
                    <h2>{{ $simulation->name }}</h2>
                    <p class="hub-note">Cenario tecnico/comercial para leitura, comparacao entre alternativas e geracao de proposta.</p>
                    <div class="solar-project-showcase__chips">
                        <span class="solar-mini-badge solar-mini-badge--automatic">{{ $statusLabel }}</span>
                        <span class="solar-mini-badge solar-mini-badge--editable">{{ $project->name }}</span>
                        <span class="solar-mini-badge solar-mini-badge--automatic">{{ $project->city ?: 'Local pendente' }}{{ $project->state ? ' / ' . $project->state : '' }}</span>
                        <span class="solar-mini-badge solar-mini-badge--editable">{{ $quoteCount }} {{ $quoteCount === 1 ? 'proposta' : 'propostas' }}</span>
                    </div>
                </div>

                <div class="solar-project-showcase__status is-ready">
                    <span class="solar-project-showcase__status-label">Base para orcamento</span>
                    <strong>{{ $readyForProposal ? 'Simulacao pronta para proposta' : 'Complete o cenario para propor' }}</strong>
                    <p>{{ $readyForProposal ? 'Este cenario concentra resultado, composicao e custo estimado para virar proposta editavel.' : 'Revise preco sugerido e potencia do sistema para transformar este cenario em proposta.' }}</p>
                    <form action="{{ route('solar.simulations.quotes.store', $simulation->id) }}" method="POST" class="solar-project-showcase__cta">
                        @csrf
                        <button type="submit" class="hub-btn">Gerar proposta</button>
                    </form>
                    @if ($latestQuote)
                        <a href="{{ route('solar.quotes.edit', $latestQuote->id) }}" class="hub-btn hub-btn--subtle">Abrir ultima proposta</a>
                    @endif
                </div>
            </div>

            <div class="solar-project-showcase__hero-grid">
                <article class="solar-project-showcase-metric solar-project-showcase-metric--energy">
                    <span class="solar-project-showcase-metric__label">Potencia do sistema</span>
                    <strong class="solar-project-showcase-metric__value" data-show-animate-number data-show-format="kwp" data-show-value="{{ $simulation->system_power_kwp ?: '' }}">
                        {{ $simulation->system_power_kwp ? number_format((float) $simulation->system_power_kwp, 2, ',', '.') . ' kWp' : 'Aguardando consumo' }}
                    </strong>
                </article>

                <article class="solar-project-showcase-metric solar-project-showcase-metric--highlight">
                    <span class="solar-project-showcase-metric__label">Preco sugerido</span>
                    <strong class="solar-project-showcase-metric__value" data-show-animate-number data-show-format="currency" data-show-value="{{ $simulation->suggested_price ?: '' }}">
                        {{ $simulation->suggested_price ? 'R$ ' . number_format((float) $simulation->suggested_price, 2, ',', '.') : 'Aguardando precificacao' }}
                    </strong>
                </article>

                <article class="solar-project-showcase-metric solar-project-showcase-metric--energy">
                    <span class="solar-project-showcase-metric__label">Geracao estimada</span>
                    <strong class="solar-project-showcase-metric__value" data-show-animate-number data-show-format="kwh" data-show-value="{{ $simulation->estimated_generation_kwh ?: '' }}">
                        {{ $simulation->estimated_generation_kwh ? number_format((float) $simulation->estimated_generation_kwh, 2, ',', '.') . ' kWh/mes' : 'Aguardando consumo' }}
                    </strong>
                </article>

                <article class="solar-project-showcase-metric solar-project-showcase-metric--energy">
                    <span class="solar-project-showcase-metric__label">Economia mensal</span>
                    <strong class="solar-project-showcase-metric__value" data-show-animate-number data-show-format="currency" data-show-value="{{ $simulation->estimated_monthly_savings ?: '' }}">
                        {{ $simulation->estimated_monthly_savings ? 'R$ ' . number_format((float) $simulation->estimated_monthly_savings, 2, ',', '.') : 'Aguardando conta' }}
                    </strong>
                </article>

                <article class="solar-project-showcase-metric">
                    <span class="solar-project-showcase-metric__label">Payback estimado</span>
                    <strong class="solar-project-showcase-metric__value" data-show-animate-number data-show-format="months" data-show-value="{{ $simulation->estimated_payback_months ?: '' }}">
                        {{ $simulation->estimated_payback_months ? $simulation->estimated_payback_months . ' meses' : 'Aguardando simulacao' }}
                    </strong>
                </article>

                <article class="solar-project-showcase-metric solar-project-showcase-metric--highlight">
                    <span class="solar-project-showcase-metric__label">ROI</span>
                    <strong class="solar-project-showcase-metric__value" data-show-animate-number data-show-format="percent" data-show-value="{{ $simulation->estimated_roi ?: '' }}">
                        {{ $simulation->estimated_roi ? number_format((float) $simulation->estimated_roi, 1, ',', '.') . '%' : 'Aguardando simulacao' }}
                    </strong>
                </article>
            </div>

            <div class="solar-inline-tip solar-inline-tip--hero">
                {{ $quoteCount > 0 ? 'Esta simulacao ja gerou proposta. Continue refinando o cenario ou ajuste a proposta antes do envio.' : 'Esta simulacao esta pronta para virar proposta. Compare cenarios antes de enviar ao cliente.' }}
            </div>
        </section>

        <section class="hub-card hub-card--subtle solar-technical-panel solar-project-show__summary">
            <div class="solar-flow-section__header">
                <div>
                    <p class="solar-section-eyebrow">Sistema tecnico</p>
                    <h3>Base tecnica do sistema</h3>
                </div>
                <p class="hub-note">Aqui fica a leitura de componentes, fator solar e configuracao usada para este cenario.</p>
            </div>

            <div class="solar-technical-panel__grid">
                <span class="solar-technical-panel__signal">
                    <strong>Modulos</strong>
                    {{ $simulation->module_quantity ?: '-' }}
                </span>
                <span class="solar-technical-panel__signal">
                    <strong>Potencia do modulo</strong>
                    {{ $simulation->module_power ? number_format((int) $simulation->module_power, 0, ',', '.') . ' W' : '-' }}
                </span>
                <span class="solar-technical-panel__signal">
                    <strong>Inversor</strong>
                    {{ $simulation->inverter_model ?: '-' }}
                </span>
                <span class="solar-technical-panel__signal">
                    <strong>Fator solar</strong>
                    {{ $simulation->solar_factor_used ? number_format((float) $simulation->solar_factor_used, 2, ',', '.') . ' kWh/kWp/mes' : '-' }}
                </span>
                <span class="solar-technical-panel__signal">
                    <strong>Radiacao</strong>
                    {{ $simulation->solar_factor_used ? number_format((float) $radiationDaily, 2, ',', '.') . ' kWh/m2/dia' : '-' }}
                </span>
                <span class="solar-technical-panel__signal">
                    <strong>Origem</strong>
                    {{ $solarSourceLabel }}
                </span>
                <span class="solar-technical-panel__signal">
                    <strong>Geracao estimada</strong>
                    {{ $simulation->estimated_generation_kwh ? number_format((float) $simulation->estimated_generation_kwh, 2, ',', '.') . ' kWh/mes' : '-' }}
                </span>
                <span class="solar-technical-panel__signal">
                    <strong>Area estimada</strong>
                    {{ $simulation->area_estimated ? number_format((float) $simulation->area_estimated, 2, ',', '.') . ' m2' : '-' }}
                </span>
                <span class="solar-technical-panel__signal">
                    <strong>Cenario</strong>
                    {{ $simulation->name }}
                </span>
            </div>
        </section>

        <div class="hub-grid hub-grid--billing solar-project-show__grid">
            <article class="hub-card hub-card--subtle solar-project-show__card">
                <h2>Observacoes e status</h2>
                <div class="solar-project-show__info-grid">
                    <p><strong>Nome da simulacao</strong><span>{{ $simulation->name }}</span></p>
                    <p><strong>Status</strong><span>{{ $statusLabel }}</span></p>
                    <p><strong>Projeto</strong><span>{{ $project->name }}</span></p>
                    <p><strong>Cliente</strong><span>{{ $project->customer?->name ?: '-' }}</span></p>
                    <p><strong>Local</strong><span>{{ $projectAddress !== '' ? $projectAddress : 'Endereco pendente.' }}</span></p>
                    <p><strong>Concessionaria</strong><span>{{ $project->utility_company ?: '-' }}</span></p>
                    <p><strong>Uso comercial</strong><span>{{ $quoteCount > 0 ? 'Cenario ja convertido em proposta.' : 'Cenario pronto para virar proposta.' }}</span></p>
                    <p><strong>Comparacao futura</strong><span>Estruture alternativas como simulacao padrao, ampliada e premium.</span></p>
                </div>
                <p class="solar-sizing-panel__note">Projeto guarda contexto do local. Simulacao concentra resultado tecnico/comercial para decisao e proposta.</p>
            </article>

            <article class="hub-card hub-card--subtle solar-project-show__card solar-financial-panel">
                <h2>Simulacao financeira</h2>
                <div class="solar-project-show__info-grid">
                    <p><strong>Economia mensal</strong><span>{{ $simulation->estimated_monthly_savings ? 'R$ ' . number_format((float) $simulation->estimated_monthly_savings, 2, ',', '.') : '-' }}</span></p>
                    <p><strong>Economia anual</strong><span>{{ $simulation->estimated_annual_savings ? 'R$ ' . number_format((float) $simulation->estimated_annual_savings, 2, ',', '.') : '-' }}</span></p>
                    <p><strong>Economia em 25 anos</strong><span>{{ $simulation->estimated_lifetime_savings ? 'R$ ' . number_format((float) $simulation->estimated_lifetime_savings, 2, ',', '.') : '-' }}</span></p>
                    <p><strong>ROI</strong><span>{{ $simulation->estimated_roi ? number_format((float) $simulation->estimated_roi, 1, ',', '.') . '%' : '-' }}</span></p>
                    <p><strong>Payback</strong><span>{{ $simulation->estimated_payback_months ? $simulation->estimated_payback_months . ' meses' : '-' }}</span></p>
                    <p><strong>Conta atual</strong><span>{{ $project->energy_bill_value ? 'R$ ' . number_format((float) $project->energy_bill_value, 2, ',', '.') : '-' }}</span></p>
                </div>
            </article>
        </div>

        <article class="hub-card hub-card--subtle solar-sizing-panel solar-project-show__card">
            <h2>Composicao do sistema</h2>
            <div class="solar-sizing-panel__highlights">
                <article class="solar-sizing-chip solar-sizing-chip--featured">
                    <span class="solar-sizing-chip__label">Modulos</span>
                    <strong class="solar-sizing-chip__value">{{ $simulation->module_quantity ?: '-' }}</strong>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Potencia do modulo</span>
                    <strong class="solar-sizing-chip__value">{{ $simulation->module_power ? number_format((int) $simulation->module_power, 0, ',', '.') . ' W' : '-' }}</strong>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Inversor</span>
                    <strong class="solar-sizing-chip__value">{{ $simulation->inverter_model ?: '-' }}</strong>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Area estimada</span>
                    <strong class="solar-sizing-chip__value">{{ $simulation->area_estimated ? number_format((float) $simulation->area_estimated, 2, ',', '.') . ' m2' : '-' }}</strong>
                </article>
            </div>

            <div class="solar-composition-list">
                @foreach ($composition as $item)
                    <article class="solar-composition-item">
                        <span class="solar-composition-item__label">{{ $item['label'] }}</span>
                        <strong class="solar-composition-item__value">{{ $item['detail'] }}</strong>
                    </article>
                @endforeach
            </div>

            <p class="solar-sizing-panel__note">Este bloco concentra a composicao tecnica usada para defender a proposta comercial.</p>
        </article>

        <article class="hub-card hub-card--subtle solar-pricing-panel solar-project-show__card">
            <h2>Composicao e custos</h2>
            <div class="solar-composition-list solar-composition-list--costs">
                <article class="solar-composition-item">
                    <span class="solar-composition-item__label">Modulos fotovoltaicos</span>
                    <strong class="solar-composition-item__value">{{ $simulation->estimated_module_cost ? 'R$ ' . number_format((float) $simulation->estimated_module_cost, 2, ',', '.') : '-' }}</strong>
                </article>
                <article class="solar-composition-item">
                    <span class="solar-composition-item__label">Inversor</span>
                    <strong class="solar-composition-item__value">{{ $simulation->estimated_inverter_cost ? 'R$ ' . number_format((float) $simulation->estimated_inverter_cost, 2, ',', '.') : '-' }}</strong>
                </article>
                <article class="solar-composition-item">
                    <span class="solar-composition-item__label">Estrutura</span>
                    <strong class="solar-composition-item__value">{{ $simulation->estimated_structure_cost ? 'R$ ' . number_format((float) $simulation->estimated_structure_cost, 2, ',', '.') : '-' }}</strong>
                </article>
                <article class="solar-composition-item">
                    <span class="solar-composition-item__label">Instalacao</span>
                    <strong class="solar-composition-item__value">{{ $simulation->estimated_installation_cost ? 'R$ ' . number_format((float) $simulation->estimated_installation_cost, 2, ',', '.') : '-' }}</strong>
                </article>
                <article class="solar-composition-item">
                    <span class="solar-composition-item__label">Custo total estimado do kit</span>
                    <strong class="solar-composition-item__value">{{ $simulation->estimated_kit_cost ? 'R$ ' . number_format((float) $simulation->estimated_kit_cost, 2, ',', '.') : '-' }}</strong>
                </article>
                <article class="solar-composition-item">
                    <span class="solar-composition-item__label">Lucro bruto estimado</span>
                    <strong class="solar-composition-item__value">{{ $simulation->estimated_gross_profit ? 'R$ ' . number_format((float) $simulation->estimated_gross_profit, 2, ',', '.') : '-' }}</strong>
                </article>
            </div>

            <p class="solar-sizing-panel__note solar-pricing-panel__note">Preco sugerido, custo estimado e lucro bruto ficam organizados aqui para apoiar a decisao comercial e a conversao em proposta.</p>
        </article>

        <article class="hub-card hub-card--subtle solar-project-show__card">
            <h2>Observacoes comerciais e tecnicas</h2>
            @if ($simulation->notes)
                <p>{{ $simulation->notes }}</p>
            @else
                <p class="hub-note">Sem observacoes registradas nesta simulacao. Use o projeto para contexto do local e esta simulacao para consolidar o cenario que sera proposto.</p>
            @endif
        </article>
    </section>
@endsection
