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
    $kitCost = $sizingService->estimateKitCost($project->suggested_price ?: $suggestedCommercialPrice, $companySetting?->margin_percent);
    $grossProfit = $sizingService->estimateGrossProfit($project->suggested_price ?: $suggestedCommercialPrice, $kitCost);
    $kitBreakdown = $sizingService->estimateKitCostBreakdown($kitCost);
    $systemComposition = $sizingService->resolveSystemComposition(
        $project->module_quantity ?: $suggestedModuleQuantity,
        $project->module_power,
        $project->inverter_model ?: $companySetting?->default_inverter_model,
        $project->system_power_kwp ?: $estimatedRequiredPowerKwp,
    );
    $estimatedAreaSquareMeters = $estimatedAreaSquareMeters
        ?? $sizingService->estimateAreaFromModules($project->module_quantity ?: $suggestedModuleQuantity);
@endphp

@section('solar-content')
    <section class="hub-card solar-project-show">
        <div class="hub-actions solar-project-show__actions">
            <a href="{{ route('solar.projects.index') }}" class="hub-btn hub-btn--subtle">Voltar para projetos</a>
            <a href="{{ route('solar.projects.edit', $project->id) }}" class="hub-btn">Editar projeto</a>
        </div>

        <section class="hub-card hub-card--subtle solar-project-command solar-project-show__summary">
            <div class="solar-project-command__header">
                <div>
                    <p class="solar-section-eyebrow">Resumo comercial</p>
                    <h2>{{ $project->name }}</h2>
                    <p class="hub-note">Leitura rapida do projeto para o instalador validar contexto, sistema sugerido e argumento comercial sem reler o cadastro inteiro.</p>
                </div>

                <div class="solar-project-command__status {{ $usesMarketPriceFallback ? 'is-market' : 'is-ready' }}">
                    <span class="solar-project-command__status-label">Automacao comercial</span>
                    <strong>
                        {{ match ($pricingReferenceSource ?? null) {
                            'company' => 'Preco proprio ativo',
                            'regional' => 'Media regional ativa',
                            default => 'Fallback padrao ativo',
                        } }}
                    </strong>
                    <p>
                        @if (($pricingReferenceSource ?? null) === 'company')
                            O projeto esta usando o preco por kWp definido pela empresa.
                        @elseif (($pricingReferenceSource ?? null) === 'regional')
                            O projeto esta usando media regional por UF para compor o pre-orcamento.
                        @else
                            O projeto esta usando {{ 'R$ ' . number_format((float) $effectivePricePerKwp, 2, ',', '.') }}/kWp como fallback padrao.
                        @endif
                    </p>
                </div>
            </div>

            <div class="solar-project-command__meta">
                <span class="solar-project-command__signal">
                    <strong>Fator solar</strong>
                    {{ number_format($resolvedSolarFactor, 2, ',', '.') }} kWh/kWp/mes
                </span>
                <span class="solar-project-command__signal">
                    <strong>Origem</strong>
                    {{ strtoupper(($solarFactorData['source'] ?? 'fallback') === 'pvgis' ? 'PVGIS' : 'padrao') }}
                </span>
                <span class="solar-project-command__signal">
                    <strong>Precisao</strong>
                    {{ $geocodingPrecisionLabel }}
                </span>
                <span class="solar-project-command__signal">
                    <strong>Preco por kWp</strong>
                    {{ 'R$ ' . number_format((float) $effectivePricePerKwp, 2, ',', '.') }}
                </span>
                @if (($solarFactorData['message'] ?? null) !== null)
                    <span class="solar-project-command__fallback solar-project-command__signal">{{ $solarFactorData['message'] }}</span>
                @endif
            </div>

            <div class="solar-project-command__summary-board solar-project-command__summary-board--compact">
                <article class="solar-summary-metric">
                    <span class="solar-summary-metric__label">Cliente</span>
                    <strong class="solar-summary-metric__value">{{ $project->customer?->name ?: 'Cliente pendente' }}</strong>
                </article>

                <article class="solar-summary-metric">
                    <span class="solar-summary-metric__label">Cidade / UF</span>
                    <strong class="solar-summary-metric__value">{{ $locationSummary !== '' ? $locationSummary : 'Local pendente' }}</strong>
                </article>

                <article class="solar-summary-metric solar-summary-metric--commercial">
                    <span class="solar-summary-metric__label">Potencia sugerida</span>
                    <strong class="solar-summary-metric__value">{{ $project->system_power_kwp ? number_format((float) $project->system_power_kwp, 2, ',', '.') . ' kWp' : ($estimatedRequiredPowerKwp !== null ? number_format($estimatedRequiredPowerKwp, 2, ',', '.') . ' kWp' : 'Aguardando consumo') }}</strong>
                </article>

                <article class="solar-summary-metric solar-summary-metric--commercial">
                    <span class="solar-summary-metric__label">Geracao estimada</span>
                    <strong class="solar-summary-metric__value">{{ $project->estimated_generation_kwh ? number_format((float) $project->estimated_generation_kwh, 2, ',', '.') . ' kWh' : ($suggestedGenerationKwh ? number_format($suggestedGenerationKwh, 2, ',', '.') . ' kWh' : 'Aguardando sistema') }}</strong>
                </article>

                <article class="solar-summary-metric solar-summary-metric--hero">
                    <span class="solar-summary-metric__label">Preco sugerido</span>
                    <strong class="solar-summary-metric__value">
                        {{ $project->suggested_price ? 'R$ ' . number_format((float) $project->suggested_price, 2, ',', '.') : ($suggestedCommercialPrice ? 'R$ ' . number_format((float) $suggestedCommercialPrice, 2, ',', '.') : 'Aguardando configuracao') }}
                    </strong>
                    <span class="solar-summary-metric__meta">Valor inicial para a proposta comercial.</span>
                </article>

                <article class="solar-summary-metric solar-summary-metric--hero">
                    <span class="solar-summary-metric__label">Economia mensal</span>
                    <strong class="solar-summary-metric__value">{{ $estimatedMonthlySavings !== null ? 'R$ ' . number_format((float) $estimatedMonthlySavings, 2, ',', '.') : 'Aguardando conta' }}</strong>
                    <span class="solar-summary-metric__meta">Mensagem principal de valor para a conversa com o cliente.</span>
                </article>
            </div>

            <div class="solar-project-command__highlights">
                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Area estimada</span>
                    <strong class="solar-sizing-chip__value">{{ $estimatedAreaSquareMeters !== null ? number_format((float) $estimatedAreaSquareMeters, 2, ',', '.') . ' m2' : '-' }}</strong>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Retorno estimado</span>
                    <strong class="solar-sizing-chip__value">{{ $estimatedPaybackMonths !== null ? $estimatedPaybackMonths . ' meses' : 'Aguardando simulacao' }}</strong>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Origem do preco</span>
                    <strong class="solar-sizing-chip__value">
                        {{ match ($pricingReferenceSource ?? null) {
                            'company' => 'Preco da empresa',
                            'regional' => 'Media regional',
                            default => 'Fallback padrao',
                        } }}
                    </strong>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">ROI aproximado</span>
                    <strong class="solar-sizing-chip__value">{{ $estimatedRoiPercentage !== null ? number_format((float) $estimatedRoiPercentage, 1, ',', '.') . '%' : 'Aguardando simulacao' }}</strong>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Pronto para proposta</span>
                    <strong class="solar-sizing-chip__value">{{ ($project->suggested_price || $suggestedCommercialPrice) && $estimatedMonthlySavings !== null ? 'Sim' : 'Em preparacao' }}</strong>
                </article>
            </div>
        </section>

        <div class="hub-grid hub-grid--billing solar-project-show__grid">
            <article class="hub-card hub-card--subtle solar-project-show__card">
                <h2>Cliente e instalacao</h2>
                <p><strong>Cliente:</strong> {{ $project->customer?->name ?: '-' }}</p>
                <p><strong>Endereco:</strong> {{ $project->address ?: 'Endereco ainda em preparacao.' }}</p>
                <p><strong>Tipo de imovel:</strong> {{ $project->property_type ?: '-' }}</p>
                <p><strong>Geocodificacao:</strong> {{ $geocodingStatusLabel }}</p>
                <p><strong>Precisao usada:</strong> {{ $geocodingPrecisionLabel }}</p>
                <p><strong>Concessionaria:</strong> {{ $project->utility_company ?: '-' }}</p>
            </article>

            <article class="hub-card hub-card--subtle solar-project-show__card">
                <h2>Consumo e leitura comercial</h2>
                <p><strong>Consumo mensal:</strong> {{ $project->monthly_consumption_kwh ? number_format((float) $project->monthly_consumption_kwh, 2, ',', '.') . ' kWh' : '-' }}</p>
                <p><strong>Valor da conta:</strong> {{ $project->energy_bill_value ? 'R$ ' . number_format((float) $project->energy_bill_value, 2, ',', '.') : '-' }}</p>
                <p><strong>Economia estimada:</strong> {{ $estimatedMonthlySavings ? 'R$ ' . number_format((float) $estimatedMonthlySavings, 2, ',', '.') . '/mes' : 'Informe o valor da conta para gerar esta leitura.' }}</p>
                <p><strong>Status:</strong> {{ $statusLabel }}</p>
            </article>
        </div>

        <article class="hub-card hub-card--subtle solar-sizing-panel solar-project-show__card">
            <h2>Sistema sugerido</h2>
            <div class="solar-sizing-panel__highlights">
                <article class="solar-sizing-chip solar-sizing-chip--featured">
                    <span class="solar-sizing-chip__label">Potencia do sistema</span>
                    <strong class="solar-sizing-chip__value">{{ $project->system_power_kwp ? number_format((float) $project->system_power_kwp, 2, ',', '.') . ' kWp' : ($estimatedRequiredPowerKwp !== null ? number_format($estimatedRequiredPowerKwp, 2, ',', '.') . ' kWp' : '-') }}</strong>
                    <span class="solar-sizing-chip__meta">Sugestao automatica usada como base para o pre-orcamento.</span>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Fator regional</span>
                    <strong class="solar-sizing-chip__value">{{ number_format((float) ($solarFactorData['factor'] ?? \App\Modules\Solar\Services\SolarSizingService::DEFAULT_SOLAR_FACTOR), 2, ',', '.') }} kWh/kWp/mes</strong>
                    <span class="solar-sizing-chip__meta">{{ ($solarFactorData['source'] ?? 'fallback') === 'pvgis' ? ($project->geocoding_precision === 'address' ? 'Calculo regional vindo do PVGIS com endereco refinado.' : 'Calculo regional vindo do PVGIS com aproximacao por cidade.') : 'Fallback padrao usado neste projeto.' }}</span>
                </article>

                <article class="solar-sizing-chip solar-sizing-chip--featured">
                    <span class="solar-sizing-chip__label">Modulos sugeridos</span>
                    <strong class="solar-sizing-chip__value">{{ $project->module_quantity ?: ($suggestedModuleQuantity ?: '-') }}</strong>
                    <span class="solar-sizing-chip__meta">Quantidade inicial para apresentar uma solucao viavel ao cliente.</span>
                </article>

                <article class="solar-sizing-chip solar-sizing-chip--featured">
                    <span class="solar-sizing-chip__label">Geracao estimada</span>
                    <strong class="solar-sizing-chip__value">{{ $project->estimated_generation_kwh ? number_format((float) $project->estimated_generation_kwh, 2, ',', '.') . ' kWh' : ($suggestedGenerationKwh ? number_format($suggestedGenerationKwh, 2, ',', '.') . ' kWh' : '-') }}</strong>
                    <span class="solar-sizing-chip__meta">Leitura automatica para apoiar a simulacao comercial.</span>
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

            <p class="solar-inline-tip">O sistema sugere automaticamente esses numeros com base no consumo, mas todos podem ser ajustados na edicao do projeto.</p>
            <p><strong>Potencia do modulo:</strong> {{ $project->module_power ? number_format((int) $project->module_power, 0, ',', '.') . ' W' : '-' }}</p>
            <p><strong>Modelo do inversor:</strong> {{ $project->inverter_model ?: ($companySetting?->default_inverter_model ?: '-') }}</p>
            <p><strong>Tipo de conexao:</strong> {{ match ($project->connection_type) {
                'mono' => 'Monofasico',
                'bi' => 'Bifasico',
                'tri' => 'Trifasico',
                default => '-',
            } }}</p>
        </article>

        <article class="hub-card hub-card--subtle solar-pricing-panel solar-project-show__card">
            <h2>Pre-orcamento comercial</h2>
            <div class="solar-sizing-panel__highlights solar-pricing-panel__highlights">
                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Preco por kWp</span>
                    <strong class="solar-sizing-chip__value">{{ 'R$ ' . number_format((float) $effectivePricePerKwp, 2, ',', '.') }}</strong>
                </article>

                <article class="solar-sizing-chip solar-sizing-chip--featured">
                    <span class="solar-sizing-chip__label">Preco sugerido</span>
                    <strong class="solar-sizing-chip__value">
                        {{ $project->suggested_price ? 'R$ ' . number_format((float) $project->suggested_price, 2, ',', '.') : ($suggestedCommercialPrice ? 'R$ ' . number_format((float) $suggestedCommercialPrice, 2, ',', '.') : '-') }}
                    </strong>
                    <span class="solar-sizing-chip__meta">Valor automatico para comecar a proposta com mais velocidade.</span>
                </article>

                <article class="solar-sizing-chip solar-sizing-chip--featured">
                    <span class="solar-sizing-chip__label">Economia estimada</span>
                    <strong class="solar-sizing-chip__value">
                        {{ $estimatedMonthlySavings ? 'R$ ' . number_format((float) $estimatedMonthlySavings, 2, ',', '.') . '/mes' : 'Nao informada' }}
                    </strong>
                    <span class="solar-sizing-chip__meta">Mensagem comercial simples para mostrar valor percebido logo no primeiro contato.</span>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Origem do preco</span>
                    <strong class="solar-sizing-chip__value">
                        {{ match ($pricingReferenceSource ?? null) {
                            'company' => 'Preco da empresa',
                            'regional' => 'Media regional',
                            default => 'Fallback padrao',
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
                    <strong class="solar-sizing-chip__value">{{ $estimatedRoiPercentage !== null ? number_format((float) $estimatedRoiPercentage, 1, ',', '.') . '%' : '-' }}</strong>
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

            @if (($pricingReferenceSource ?? null) === 'company')
                <p class="hub-note">Preco inicial calculado pela regra simples: potencia do sistema x preco por kWp da empresa.</p>
            @elseif (($pricingReferenceSource ?? null) === 'regional')
                <p class="hub-note">Preco inicial calculado com media regional da UF para acelerar o pre-orcamento quando a empresa ainda nao definiu preco proprio.</p>
            @else
                <p class="hub-note">Preco inicial calculado com fallback padrao de {{ 'R$ ' . number_format((float) $effectivePricePerKwp, 2, ',', '.') }}/kWp.</p>
            @endif

            <p class="solar-inline-tip"><strong>Status comercial:</strong> {{ $statusLabel }}. O projeto ja esta estruturado para evoluir de pre-orcamento para proposta sem perder o contexto comercial.</p>

            @if ($project->pricing_notes)
                <p><strong>Observacoes comerciais:</strong> {{ $project->pricing_notes }}</p>
            @endif
        </article>

        <article class="hub-card hub-card--subtle solar-financial-panel solar-project-show__card">
            <h2>Simulacao financeira</h2>
            <div class="solar-sizing-panel__highlights solar-financial-panel__highlights">
                <article class="solar-sizing-chip solar-sizing-chip--featured solar-sizing-chip--commercial">
                    <span class="solar-sizing-chip__label">Economia mensal estimada</span>
                    <strong class="solar-sizing-chip__value">{{ $estimatedMonthlySavings !== null ? 'R$ ' . number_format((float) $estimatedMonthlySavings, 2, ',', '.') : 'Informe a conta de energia' }}</strong>
                    <span class="solar-sizing-chip__meta">Considerando custo minimo residual de {{ 'R$ ' . number_format((float) $residualMinimumCost, 2, ',', '.') }} por mes.</span>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Economia anual</span>
                    <strong class="solar-sizing-chip__value">{{ $estimatedAnnualSavings !== null ? 'R$ ' . number_format((float) $estimatedAnnualSavings, 2, ',', '.') : 'Aguardando simulacao' }}</strong>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Economia em 25 anos</span>
                    <strong class="solar-sizing-chip__value">{{ $estimatedLifetimeSavings !== null ? 'R$ ' . number_format((float) $estimatedLifetimeSavings, 2, ',', '.') : 'Aguardando simulacao' }}</strong>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Retorno estimado</span>
                    <strong class="solar-sizing-chip__value">{{ $estimatedPaybackMonths !== null ? $estimatedPaybackMonths . ' meses' : 'Aguardando simulacao' }}</strong>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">ROI aproximado</span>
                    <strong class="solar-sizing-chip__value">{{ $estimatedRoiPercentage !== null ? number_format((float) $estimatedRoiPercentage, 1, ',', '.') . '%' : 'Aguardando simulacao' }}</strong>
                </article>
            </div>

            <p class="hub-note">Regra inicial simples: valor da conta de energia menos custo minimo residual de {{ 'R$ ' . number_format((float) $residualMinimumCost, 2, ',', '.') }}. Sem inflacao ou projecao tarifaria nesta fase.</p>
        </article>

        @if ($project->notes)
            <article class="hub-card hub-card--subtle solar-project-show__card">
                <h2>Observacoes</h2>
                <p>{{ $project->notes }}</p>
            </article>
        @endif
    </section>
@endsection
