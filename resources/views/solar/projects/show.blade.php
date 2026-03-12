@extends('solar.layout')

@section('title', 'Projeto | Voltrune Solar')

@php
    $sizingService = app(\App\Modules\Solar\Services\SolarSizingService::class);
    $statusLabel = match ($project->status) {
        'draft' => 'Rascunho',
        'qualified' => 'Qualificado',
        'proposal' => 'Proposta',
        'won' => 'Fechado',
        default => strtoupper((string) $project->status),
    };

    $locationSummary = collect([$project->city, $project->state])->filter()->implode(' / ');
    $resolvedSolarFactor = (float) ($solarFactorData['factor'] ?? \App\Modules\Solar\Services\SolarSizingService::DEFAULT_SOLAR_FACTOR);
    $equivalentSolarRadiationDaily = $sizingService->estimateEquivalentSolarRadiationDaily($resolvedSolarFactor);
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
    $activeSimulation = $defaultSimulation;
    $displaySystemPower = $activeSimulation?->system_power_kwp ?: ($project->system_power_kwp ?: $estimatedRequiredPowerKwp);
    $displayGeneration = $activeSimulation?->estimated_generation_kwh ?: ($project->estimated_generation_kwh ?: $suggestedGenerationKwh);
    $displaySuggestedPrice = $activeSimulation?->suggested_price ?: ($project->suggested_price ?: $suggestedCommercialPrice);
    $displayMonthlySavings = $activeSimulation?->estimated_monthly_savings ?: $estimatedMonthlySavings;
    $displayAnnualSavings = $activeSimulation?->estimated_annual_savings ?: $estimatedAnnualSavings;
    $displayLifetimeSavings = $activeSimulation?->estimated_lifetime_savings ?: $estimatedLifetimeSavings;
    $displayPaybackMonths = $activeSimulation?->estimated_payback_months ?: $estimatedPaybackMonths;
    $displayEstimatedRoi = $activeSimulation?->estimated_roi ?: $estimatedRoiPercentage;
    $kitCost = $sizingService->estimateKitCost($displaySuggestedPrice, $companySetting?->margin_percent);
    $grossProfit = $sizingService->estimateGrossProfit($displaySuggestedPrice, $kitCost);
    $kitBreakdown = $sizingService->estimateKitCostBreakdown($kitCost);
    $systemComposition = $activeSimulation?->system_composition_json ?: $sizingService->resolveSystemComposition(
        $activeSimulation?->module_quantity ?: ($project->module_quantity ?: $suggestedModuleQuantity),
        $activeSimulation?->module_power ?: $project->module_power,
        $activeSimulation?->inverter_model ?: ($project->inverter_model ?: $companySetting?->default_inverter_model),
        $displaySystemPower,
    );
    $estimatedAreaSquareMeters = $activeSimulation?->area_estimated
        ?: ($estimatedAreaSquareMeters ?? $sizingService->estimateAreaFromModules($project->module_quantity ?: $suggestedModuleQuantity));
    $readyForProposal = $displaySuggestedPrice && $displayMonthlySavings !== null;
    $displayAddress = $project->address ?: collect([
        $project->street ?: null,
        $project->number ?: null,
        $project->complement ?: null,
        $project->district ?: null,
        $project->city ?: null,
        $project->state ?: null,
    ])->filter()->implode(', ');
@endphp

@section('solar-content')
    <section class="hub-card solar-project-show">
        <div class="hub-actions solar-project-show__actions">
            <a href="{{ route('solar.projects.index') }}" class="hub-btn hub-btn--subtle">Voltar para projetos</a>
            <a href="{{ route('solar.projects.edit', $project->id) }}" class="hub-btn">Editar projeto</a>
        </div>

        <section class="hub-card hub-card--subtle solar-project-showcase" data-solar-showcase>
            <div class="solar-project-showcase__header">
                <div>
                    <p class="solar-section-eyebrow">Hero comercial</p>
                    <h2>{{ $project->name }}</h2>
                    <p class="hub-note">Painel executivo para leitura comercial do projeto sem reler o cadastro inteiro.</p>
                    <div class="solar-project-showcase__chips">
                        <span class="solar-mini-badge solar-mini-badge--automatic">{{ $statusLabel }}</span>
                        <span class="solar-mini-badge solar-mini-badge--editable">{{ $locationSummary !== '' ? $locationSummary : 'Local pendente' }}</span>
                        <span class="solar-mini-badge {{ $readyForProposal ? 'solar-mini-badge--automatic' : 'solar-mini-badge--editable' }}">
                            {{ $readyForProposal ? 'Pronto para proposta' : 'Em preparacao' }}
                        </span>
                    </div>
                </div>

                <div class="solar-project-showcase__status {{ $usesMarketPriceFallback ? 'is-market' : 'is-ready' }}">
                    <span class="solar-project-showcase__status-label">Origem comercial</span>
                    <strong>
                        {{ match ($pricingReferenceSource ?? null) {
                            'company' => 'Preco proprio ativo',
                            'regional' => 'Media regional ativa',
                            default => 'Fallback nacional ativo',
                        } }}
                    </strong>
                    <p>
                        @if (($pricingReferenceSource ?? null) === 'company')
                            O projeto esta usando o preco por kWp definido pela empresa.
                        @elseif (($pricingReferenceSource ?? null) === 'regional')
                            Preco sugerido baseado em media de mercado. Voce pode ajustar manualmente.
                        @else
                            Preco sugerido baseado em media de mercado. Voce pode ajustar manualmente.
                        @endif
                    </p>
                </div>
            </div>

            <div class="solar-project-showcase__hero-grid">
                <article class="solar-project-showcase-metric solar-project-showcase-metric--energy">
                    <span class="solar-project-showcase-metric__label">Potencia do sistema</span>
                    <strong class="solar-project-showcase-metric__value" data-show-animate-number data-show-format="kwp" data-show-value="{{ $displaySystemPower ?: '' }}">
                        {{ $displaySystemPower ? number_format((float) $displaySystemPower, 2, ',', '.') . ' kWp' : 'Aguardando consumo' }}
                    </strong>
                </article>

                <article class="solar-project-showcase-metric solar-project-showcase-metric--highlight">
                    <span class="solar-project-showcase-metric__label">Preco sugerido</span>
                    <strong class="solar-project-showcase-metric__value" data-show-animate-number data-show-format="currency" data-show-value="{{ $displaySuggestedPrice ?: '' }}">
                        {{ $displaySuggestedPrice ? 'R$ ' . number_format((float) $displaySuggestedPrice, 2, ',', '.') : 'Aguardando configuracao' }}
                    </strong>
                </article>

                <article class="solar-project-showcase-metric solar-project-showcase-metric--energy">
                    <span class="solar-project-showcase-metric__label">Economia mensal</span>
                    <strong class="solar-project-showcase-metric__value" data-show-animate-number data-show-format="currency" data-show-value="{{ $displayMonthlySavings ?: '' }}">
                        {{ $displayMonthlySavings !== null ? 'R$ ' . number_format((float) $displayMonthlySavings, 2, ',', '.') : 'Aguardando conta' }}
                    </strong>
                </article>

                <article class="solar-project-showcase-metric">
                    <span class="solar-project-showcase-metric__label">Retorno estimado</span>
                    <strong class="solar-project-showcase-metric__value" data-show-animate-number data-show-format="months" data-show-value="{{ $displayPaybackMonths ?: '' }}">
                        {{ $displayPaybackMonths !== null ? $displayPaybackMonths . ' meses' : 'Aguardando simulacao' }}
                    </strong>
                </article>
            </div>
        </section>

        <section class="hub-card hub-card--subtle solar-technical-panel solar-project-show__summary">
            <div class="solar-flow-section__header">
                <div>
                    <p class="solar-section-eyebrow">Base tecnica</p>
                    <h3>Dados tecnicos do dimensionamento</h3>
                </div>
                <p class="hub-note">Contexto tecnico da simulacao com menor peso visual.</p>
            </div>

            <div class="solar-technical-panel__grid">
                <span class="solar-technical-panel__signal">
                    <strong>Fator solar</strong>
                    {{ number_format($resolvedSolarFactor, 2, ',', '.') }} kWh/kWp/mes
                </span>
                <span class="solar-technical-panel__signal">
                    <strong>Radiacao solar</strong>
                    {{ number_format($equivalentSolarRadiationDaily, 2, ',', '.') }} kWh/m2/dia
                </span>
                <span class="solar-technical-panel__signal">
                    <strong>Origem</strong>
                    {{ strtoupper(($solarFactorData['source'] ?? 'fallback') === 'pvgis' ? 'PVGIS' : 'padrao') }}
                </span>
                <span class="solar-technical-panel__signal">
                    <strong>Precisao</strong>
                    {{ $geocodingPrecisionLabel }}
                </span>
                <span class="solar-technical-panel__signal">
                    <strong>Preco por kWp</strong>
                    {{ 'R$ ' . number_format((float) $effectivePricePerKwp, 2, ',', '.') }}
                </span>
                <span class="solar-technical-panel__signal">
                    <strong>Margem</strong>
                    {{ $companySetting?->margin_percent ? number_format((float) $companySetting->margin_percent, 2, ',', '.') . '%' : 'Nao configurada' }}
                </span>
                @if (($solarFactorData['message'] ?? null) !== null)
                    <span class="solar-technical-panel__fallback solar-technical-panel__signal">{{ $solarFactorData['message'] }}</span>
                @endif
            </div>
        </section>

        <div class="hub-grid hub-grid--billing solar-project-show__grid">
            <article class="hub-card hub-card--subtle solar-project-show__card">
                <h2>Contexto do projeto</h2>
                <div class="solar-project-show__info-grid">
                    <p><strong>Cliente</strong><span>{{ $project->customer?->name ?: '-' }}</span></p>
                    <p><strong>Endereco</strong><span>{{ $displayAddress !== '' ? $displayAddress : 'Endereco ainda em preparacao.' }}</span></p>
                    <p><strong>Tipo de imovel</strong><span>{{ $project->property_type ?: '-' }}</span></p>
                    <p><strong>Geocodificacao</strong><span>{{ $geocodingStatusLabel }}</span></p>
                    <p><strong>Precisao usada</strong><span>{{ $geocodingPrecisionLabel }}</span></p>
                    <p><strong>Concessionaria</strong><span>{{ $project->utility_company ?: '-' }}</span></p>
                </div>
            </article>

            <article class="hub-card hub-card--subtle solar-project-show__card">
                <h2>Consumo e leitura comercial</h2>
                <div class="solar-project-show__info-grid">
                    <p><strong>Consumo mensal</strong><span>{{ $project->monthly_consumption_kwh ? number_format((float) $project->monthly_consumption_kwh, 2, ',', '.') . ' kWh' : '-' }}</span></p>
                    <p><strong>Valor da conta</strong><span>{{ $project->energy_bill_value ? 'R$ ' . number_format((float) $project->energy_bill_value, 2, ',', '.') : '-' }}</span></p>
                    <p><strong>Economia estimada</strong><span>{{ $estimatedMonthlySavings ? 'R$ ' . number_format((float) $estimatedMonthlySavings, 2, ',', '.') . '/mes' : 'Informe a conta para simular.' }}</span></p>
                    <p><strong>Status</strong><span>{{ $statusLabel }}</span></p>
                </div>
            </article>
        </div>

        <article class="hub-card hub-card--subtle solar-project-show__card">
            <div class="solar-flow-section__header">
                <div>
                    <p class="solar-section-eyebrow">Simulacoes</p>
                    <h2>Cenarios do projeto</h2>
                </div>
                <form method="POST" action="{{ route('solar.projects.simulations.store', $project->id) }}">
                    @csrf
                    <button type="submit" class="hub-btn">Criar nova simulacao</button>
                </form>
            </div>

            <p class="hub-note">O projeto guarda o local e o contexto. Cada simulacao representa um cenario tecnico/comercial pronto para evoluir em orcamento.</p>

            <div class="solar-sizing-panel__highlights">
                @forelse ($simulations as $simulation)
                    <article class="solar-sizing-chip {{ $loop->first ? 'solar-sizing-chip--featured solar-sizing-chip--commercial' : '' }}">
                        <span class="solar-sizing-chip__label">{{ $simulation->name }}</span>
                        <strong class="solar-sizing-chip__value">
                            {{ $simulation->system_power_kwp ? number_format((float) $simulation->system_power_kwp, 2, ',', '.') . ' kWp' : 'Potencia pendente' }}
                        </strong>
                        <span class="hub-note">
                            {{ $simulation->suggested_price ? 'R$ ' . number_format((float) $simulation->suggested_price, 2, ',', '.') : 'Preco em construcao' }}
                            ·
                            {{ $simulation->estimated_generation_kwh ? number_format((float) $simulation->estimated_generation_kwh, 2, ',', '.') . ' kWh/mes' : 'Geracao pendente' }}
                        </span>
                        <a href="{{ route('solar.simulations.show', $simulation->id) }}" class="hub-link">Abrir simulacao</a>
                    </article>
                @empty
                    <article class="solar-sizing-chip">
                        <span class="solar-sizing-chip__label">Simulacoes</span>
                        <strong class="solar-sizing-chip__value">Nenhuma simulacao criada</strong>
                        <span class="hub-note">Crie a primeira simulacao para separar o contexto do projeto do cenario comercial.</span>
                    </article>
                @endforelse
            </div>
        </article>

        <article class="hub-card hub-card--subtle solar-sizing-panel solar-project-show__card">
            <h2>Sistema sugerido</h2>
            <div class="solar-sizing-panel__highlights">
                <article class="solar-sizing-chip solar-sizing-chip--featured">
                    <span class="solar-sizing-chip__label">Potencia do sistema</span>
                    <strong class="solar-sizing-chip__value">{{ $displaySystemPower ? number_format((float) $displaySystemPower, 2, ',', '.') . ' kWp' : '-' }}</strong>
                </article>

                <article class="solar-sizing-chip solar-sizing-chip--featured">
                    <span class="solar-sizing-chip__label">Modulos sugeridos</span>
                    <strong class="solar-sizing-chip__value">{{ $project->module_quantity ?: ($suggestedModuleQuantity ?: '-') }}</strong>
                </article>

                <article class="solar-sizing-chip solar-sizing-chip--featured">
                    <span class="solar-sizing-chip__label">Geracao estimada</span>
                    <strong class="solar-sizing-chip__value">{{ $displayGeneration ? number_format((float) $displayGeneration, 2, ',', '.') . ' kWh' : '-' }}</strong>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Area estimada</span>
                    <strong class="solar-sizing-chip__value">{{ $estimatedAreaSquareMeters !== null ? number_format((float) $estimatedAreaSquareMeters, 2, ',', '.') . ' m2' : '-' }}</strong>
                </article>
            </div>

            <div class="solar-composition-list">
                @foreach ($systemComposition as $item)
                    <article class="solar-composition-item">
                        <span class="solar-composition-item__label">{{ $item['label'] }}</span>
                        <strong class="solar-composition-item__value">{{ $item['detail'] }}</strong>
                    </article>
                @endforeach
            </div>

            <div class="solar-project-show__inline-specs">
                <span><strong>Modulo</strong>{{ ($activeSimulation?->module_power ?: $project->module_power) ? number_format((int) ($activeSimulation?->module_power ?: $project->module_power), 0, ',', '.') . ' W' : '-' }}</span>
                <span><strong>Inversor</strong>{{ $activeSimulation?->inverter_model ?: ($project->inverter_model ?: ($companySetting?->default_inverter_model ?: '-')) }}</span>
                <span><strong>Conexao</strong>{{ match ($project->connection_type) {
                    'mono' => 'Monofasico',
                    'bi' => 'Bifasico',
                    'tri' => 'Trifasico',
                    default => '-',
                } }}</span>
            </div>
        </article>

        <article class="hub-card hub-card--subtle solar-pricing-panel solar-project-show__card">
            <h2>Pre-orcamento comercial</h2>
            <div class="solar-sizing-panel__highlights solar-pricing-panel__highlights">
                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Preco sugerido</span>
                    <strong class="solar-sizing-chip__value">
                        {{ $displaySuggestedPrice ? 'R$ ' . number_format((float) $displaySuggestedPrice, 2, ',', '.') : '-' }}
                    </strong>
                </article>

                <article class="solar-sizing-chip solar-sizing-chip--featured">
                    <span class="solar-sizing-chip__label">Economia estimada</span>
                    <strong class="solar-sizing-chip__value">
                        {{ $displayMonthlySavings ? 'R$ ' . number_format((float) $displayMonthlySavings, 2, ',', '.') . '/mes' : 'Nao informada' }}
                    </strong>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Origem do preco</span>
                    <strong class="solar-sizing-chip__value">
                        {{ match ($pricingReferenceSource ?? null) {
                            'company' => 'Preco da empresa',
                            'regional' => 'Media regional',
                            default => 'Fallback padrao nacional',
                        } }}
                    </strong>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Custo estimado do kit</span>
                    <strong class="solar-sizing-chip__value">{{ $kitCost !== null ? 'R$ ' . number_format((float) $kitCost, 2, ',', '.') : '-' }}</strong>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Lucro bruto estimado</span>
                    <strong class="solar-sizing-chip__value">{{ $grossProfit !== null ? 'R$ ' . number_format((float) $grossProfit, 2, ',', '.') : '-' }}</strong>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">ROI aproximado ao ano</span>
                    <strong class="solar-sizing-chip__value">{{ $displayEstimatedRoi !== null ? number_format((float) $displayEstimatedRoi, 1, ',', '.') . '%' : '-' }}</strong>
                </article>
            </div>

            <div class="solar-composition-list solar-composition-list--costs">
                <article class="solar-composition-item">
                    <span class="solar-composition-item__label">Modulos</span>
                    <strong class="solar-composition-item__value">{{ $kitBreakdown['modules'] !== null ? 'R$ ' . number_format((float) $kitBreakdown['modules'], 2, ',', '.') : '-' }}</strong>
                </article>
                <article class="solar-composition-item">
                    <span class="solar-composition-item__label">Inversor</span>
                    <strong class="solar-composition-item__value">{{ $kitBreakdown['inverter'] !== null ? 'R$ ' . number_format((float) $kitBreakdown['inverter'], 2, ',', '.') : '-' }}</strong>
                </article>
                <article class="solar-composition-item">
                    <span class="solar-composition-item__label">Estrutura</span>
                    <strong class="solar-composition-item__value">{{ $kitBreakdown['structure'] !== null ? 'R$ ' . number_format((float) $kitBreakdown['structure'], 2, ',', '.') : '-' }}</strong>
                </article>
                <article class="solar-composition-item">
                    <span class="solar-composition-item__label">Instalacao</span>
                    <strong class="solar-composition-item__value">{{ $kitBreakdown['installation'] !== null ? 'R$ ' . number_format((float) $kitBreakdown['installation'], 2, ',', '.') : '-' }}</strong>
                </article>
            </div>

            <p class="solar-inline-tip"><strong>Origem do preco:</strong> {{ match ($pricingReferenceSource ?? null) {
                'company' => 'Preco da empresa',
                'regional' => 'Media regional',
                default => 'Fallback padrao nacional',
            } }}</p>

            @if ($project->pricing_notes)
                <p><strong>Observacoes comerciais:</strong> {{ $project->pricing_notes }}</p>
            @endif
        </article>

        <article class="hub-card hub-card--subtle solar-financial-panel solar-project-show__card">
            <h2>Simulacao financeira</h2>
            <div class="solar-sizing-panel__highlights solar-financial-panel__highlights">
                <article class="solar-sizing-chip solar-sizing-chip--featured solar-sizing-chip--commercial">
                    <span class="solar-sizing-chip__label">Economia mensal estimada</span>
                    <strong class="solar-sizing-chip__value">{{ $displayMonthlySavings !== null ? 'R$ ' . number_format((float) $displayMonthlySavings, 2, ',', '.') : 'Informe a conta de energia' }}</strong>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Economia anual</span>
                    <strong class="solar-sizing-chip__value">{{ $displayAnnualSavings !== null ? 'R$ ' . number_format((float) $displayAnnualSavings, 2, ',', '.') : 'Aguardando simulacao' }}</strong>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Economia em 25 anos</span>
                    <strong class="solar-sizing-chip__value">{{ $displayLifetimeSavings !== null ? 'R$ ' . number_format((float) $displayLifetimeSavings, 2, ',', '.') : 'Aguardando simulacao' }}</strong>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Retorno estimado</span>
                    <strong class="solar-sizing-chip__value">{{ $displayPaybackMonths !== null ? $displayPaybackMonths . ' meses' : 'Aguardando simulacao' }}</strong>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">ROI aproximado</span>
                    <strong class="solar-sizing-chip__value">{{ $displayEstimatedRoi !== null ? number_format((float) $displayEstimatedRoi, 1, ',', '.') . '%' : 'Aguardando simulacao' }}</strong>
                </article>
            </div>

            <p class="solar-inline-tip"><strong>Leitura financeira:</strong> residual minimo considerado de {{ 'R$ ' . number_format((float) $residualMinimumCost, 2, ',', '.') }}/mes.</p>
        </article>

        @if ($project->notes)
            <article class="hub-card hub-card--subtle solar-project-show__card">
                <h2>Observacoes</h2>
                <p>{{ $project->notes }}</p>
            </article>
        @endif
    </section>
@endsection
