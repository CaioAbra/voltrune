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
    $resolvedSolarFactor = (float) ($solarFactorData['factor'] ?? \App\Modules\Solar\Services\SolarSizingService::DEFAULT_SOLAR_FACTOR);
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
                    <p class="hub-note">Leitura rápida do projeto para o instalador entender cliente, contexto, sistema sugerido e valor comercial sem reler todo o cadastro.</p>
                </div>

                <div class="solar-project-command__status {{ $usesMarketPriceFallback ? 'is-market' : 'is-ready' }}">
                    <span class="solar-project-command__status-label">Status do pré-orçamento</span>
                    <strong>{{ $usesMarketPriceFallback ? 'Ativo com média de mercado' : 'Valor sugerido ativo' }}</strong>
                    <p>
                        @if (! $usesMarketPriceFallback)
                            O projeto já usa a regra simples de preço por kWp da empresa.
                        @else
                            O projeto está usando {{ 'R$ ' . number_format((float) $effectivePricePerKwp, 2, ',', '.') }}/kWp como referência inicial.
                        @endif
                    </p>
                </div>
            </div>

            <div class="solar-project-command__meta">
                <span class="solar-project-command__signal">
                    <strong>Fator solar regional</strong>
                    {{ number_format($resolvedSolarFactor, 2, ',', '.') }} kWh/kWp/mês
                </span>
                <span class="solar-project-command__signal">
                    <strong>Origem do cálculo</strong>
                    {{ strtoupper(($solarFactorData['source'] ?? 'default') === 'pvgis' ? 'PVGIS' : 'padrão') }}
                </span>
                @if (($solarFactorData['message'] ?? null) !== null)
                    <span class="solar-project-command__fallback solar-project-command__signal">{{ $solarFactorData['message'] }}</span>
                @endif
            </div>

            <div class="solar-project-command__summary-board">
                <article class="solar-summary-metric solar-summary-metric--hero">
                    <span class="solar-summary-metric__label">Preço sugerido</span>
                    <strong class="solar-summary-metric__value">
                        {{ $project->suggested_price ? 'R$ ' . number_format((float) $project->suggested_price, 2, ',', '.') : ($suggestedCommercialPrice ? 'R$ ' . number_format((float) $suggestedCommercialPrice, 2, ',', '.') : 'Aguardando configuração') }}
                    </strong>
                    <span class="solar-summary-metric__meta">Valor inicial para a proposta comercial.</span>
                </article>

                <article class="solar-summary-metric solar-summary-metric--hero">
                    <span class="solar-summary-metric__label">Economia mensal</span>
                    <strong class="solar-summary-metric__value">{{ $estimatedMonthlySavings !== null ? 'R$ ' . number_format((float) $estimatedMonthlySavings, 2, ',', '.') : 'Aguardando conta' }}</strong>
                    <span class="solar-summary-metric__meta">Leitura rápida de valor percebido para o cliente final.</span>
                </article>

                <article class="solar-summary-metric">
                    <span class="solar-summary-metric__label">Potência sugerida</span>
                    <strong class="solar-summary-metric__value">{{ $project->system_power_kwp ? number_format((float) $project->system_power_kwp, 2, ',', '.') . ' kWp' : ($estimatedRequiredPowerKwp !== null ? number_format($estimatedRequiredPowerKwp, 2, ',', '.') . ' kWp' : 'Aguardando consumo') }}</strong>
                </article>

                <article class="solar-summary-metric">
                    <span class="solar-summary-metric__label">Módulos sugeridos</span>
                    <strong class="solar-summary-metric__value">{{ $project->module_quantity ?: ($suggestedModuleQuantity ?: 'Aguardando sistema') }}</strong>
                </article>

                <article class="solar-summary-metric">
                    <span class="solar-summary-metric__label">Geração estimada</span>
                    <strong class="solar-summary-metric__value">{{ $project->estimated_generation_kwh ? number_format((float) $project->estimated_generation_kwh, 2, ',', '.') . ' kWh' : ($suggestedGenerationKwh ? number_format($suggestedGenerationKwh, 2, ',', '.') . ' kWh' : 'Aguardando sistema') }}</strong>
                </article>

                <article class="solar-summary-metric">
                    <span class="solar-summary-metric__label">Economia anual</span>
                    <strong class="solar-summary-metric__value">{{ $estimatedAnnualSavings !== null ? 'R$ ' . number_format((float) $estimatedAnnualSavings, 2, ',', '.') : 'Aguardando simulação' }}</strong>
                </article>

                <article class="solar-summary-metric">
                    <span class="solar-summary-metric__label">Economia em 25 anos</span>
                    <strong class="solar-summary-metric__value">{{ $estimatedLifetimeSavings !== null ? 'R$ ' . number_format((float) $estimatedLifetimeSavings, 2, ',', '.') : 'Aguardando simulação' }}</strong>
                </article>

                <article class="solar-summary-metric">
                    <span class="solar-summary-metric__label">Status comercial</span>
                    <strong class="solar-summary-metric__value">{{ $statusLabel }}</strong>
                </article>
            </div>

            <div class="solar-project-command__highlights">
                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Cliente</span>
                    <strong class="solar-sizing-chip__value">{{ $project->customer?->name ?: '-' }}</strong>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Cidade / UF</span>
                    <strong class="solar-sizing-chip__value">{{ $locationSummary !== '' ? $locationSummary : 'Local pendente' }}</strong>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Origem do fator</span>
                    <strong class="solar-sizing-chip__value">{{ strtoupper(($solarFactorData['source'] ?? 'default') === 'pvgis' ? 'PVGIS' : 'padrão') }}</strong>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Pronto para proposta</span>
                    <strong class="solar-sizing-chip__value">{{ ($project->suggested_price || $suggestedCommercialPrice) && $estimatedMonthlySavings !== null ? 'Sim' : 'Em preparação' }}</strong>
                </article>
            </div>
        </section>

        <div class="hub-grid hub-grid--billing solar-project-show__grid">
            <article class="hub-card hub-card--subtle solar-project-show__card">
                <h2>Cliente e instalação</h2>
                <p><strong>Cliente:</strong> {{ $project->customer?->name ?: '-' }}</p>
                <p><strong>Endereço:</strong> {{ $project->address ?: 'Endereço ainda em preparação.' }}</p>
                <p><strong>Tipo de imóvel:</strong> {{ $project->property_type ?: '-' }}</p>
                <p><strong>Geocodificação:</strong> {{ strtoupper($project->geocoding_status ?? 'pending') }}</p>
            </article>

            <article class="hub-card hub-card--subtle solar-project-show__card">
                <h2>Consumo e leitura comercial</h2>
                <p><strong>Consumo mensal:</strong> {{ $project->monthly_consumption_kwh ? number_format((float) $project->monthly_consumption_kwh, 2, ',', '.') . ' kWh' : '-' }}</p>
                <p><strong>Valor da conta:</strong> {{ $project->energy_bill_value ? 'R$ ' . number_format((float) $project->energy_bill_value, 2, ',', '.') : '-' }}</p>
                <p><strong>Economia estimada:</strong> {{ $estimatedMonthlySavings ? 'R$ ' . number_format((float) $estimatedMonthlySavings, 2, ',', '.') . '/mês' : 'Informe o valor da conta para gerar esta leitura.' }}</p>
                <p><strong>Status:</strong> {{ $statusLabel }}</p>
            </article>
        </div>

        <article class="hub-card hub-card--subtle solar-sizing-panel solar-project-show__card">
            <h2>Sistema sugerido</h2>
            <div class="solar-sizing-panel__highlights">
                <article class="solar-sizing-chip solar-sizing-chip--featured">
                    <span class="solar-sizing-chip__label">Potência do sistema</span>
                    <strong class="solar-sizing-chip__value">{{ $project->system_power_kwp ? number_format((float) $project->system_power_kwp, 2, ',', '.') . ' kWp' : ($estimatedRequiredPowerKwp !== null ? number_format($estimatedRequiredPowerKwp, 2, ',', '.') . ' kWp' : '-') }}</strong>
                    <span class="solar-sizing-chip__meta">Sugestão automática usada como base para o pré-orçamento.</span>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Fator regional</span>
                    <strong class="solar-sizing-chip__value">{{ number_format((float) ($solarFactorData['factor'] ?? \App\Modules\Solar\Services\SolarSizingService::DEFAULT_SOLAR_FACTOR), 2, ',', '.') }} kWh/kWp/mês</strong>
                    <span class="solar-sizing-chip__meta">{{ ($solarFactorData['source'] ?? 'default') === 'pvgis' ? 'Cálculo regional vindo do PVGIS.' : 'Fallback padrão usado neste projeto.' }}</span>
                </article>

                <article class="solar-sizing-chip solar-sizing-chip--featured">
                    <span class="solar-sizing-chip__label">Módulos sugeridos</span>
                    <strong class="solar-sizing-chip__value">{{ $project->module_quantity ?: ($suggestedModuleQuantity ?: '-') }}</strong>
                    <span class="solar-sizing-chip__meta">Quantidade inicial para apresentar uma solução viável ao cliente.</span>
                </article>

                <article class="solar-sizing-chip solar-sizing-chip--featured">
                    <span class="solar-sizing-chip__label">Geração estimada</span>
                    <strong class="solar-sizing-chip__value">{{ $project->estimated_generation_kwh ? number_format((float) $project->estimated_generation_kwh, 2, ',', '.') . ' kWh' : ($suggestedGenerationKwh ? number_format($suggestedGenerationKwh, 2, ',', '.') . ' kWh' : '-') }}</strong>
                    <span class="solar-sizing-chip__meta">Leitura automática para apoiar a simulação comercial.</span>
                </article>
            </div>

            <p class="solar-inline-tip">O sistema sugere automaticamente esses números com base no consumo, mas todos podem ser ajustados na edição do projeto.</p>
            <p><strong>Potência do módulo:</strong> {{ $project->module_power ? number_format((int) $project->module_power, 0, ',', '.') . ' W' : '-' }}</p>
            <p><strong>Modelo do inversor:</strong> {{ $project->inverter_model ?: ($companySetting?->default_inverter_model ?: '-') }}</p>
            <p><strong>Concessionária:</strong> {{ $project->utility_company ?: '-' }}</p>
            <p><strong>Tipo de conexão:</strong> {{ match ($project->connection_type) {
                'mono' => 'Monofásico',
                'bi' => 'Bifásico',
                'tri' => 'Trifásico',
                default => '-',
            } }}</p>
        </article>

        <article class="hub-card hub-card--subtle solar-pricing-panel solar-project-show__card">
            <h2>Pré-orçamento comercial</h2>
            <div class="solar-sizing-panel__highlights solar-pricing-panel__highlights">
                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Preço por kWp</span>
                    <strong class="solar-sizing-chip__value">
                        {{ 'R$ ' . number_format((float) $effectivePricePerKwp, 2, ',', '.') }}
                    </strong>
                </article>

                <article class="solar-sizing-chip solar-sizing-chip--featured">
                    <span class="solar-sizing-chip__label">Preço sugerido</span>
                    <strong class="solar-sizing-chip__value">
                        {{ $project->suggested_price ? 'R$ ' . number_format((float) $project->suggested_price, 2, ',', '.') : ($suggestedCommercialPrice ? 'R$ ' . number_format((float) $suggestedCommercialPrice, 2, ',', '.') : '-') }}
                    </strong>
                    <span class="solar-sizing-chip__meta">Valor automático para começar a proposta com mais velocidade.</span>
                </article>

                <article class="solar-sizing-chip solar-sizing-chip--featured">
                    <span class="solar-sizing-chip__label">Economia estimada</span>
                    <strong class="solar-sizing-chip__value">
                        {{ $estimatedMonthlySavings ? 'R$ ' . number_format((float) $estimatedMonthlySavings, 2, ',', '.') . '/mês' : 'Não informada' }}
                    </strong>
                    <span class="solar-sizing-chip__meta">Mensagem comercial simples para mostrar valor percebido logo no primeiro contato.</span>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Margem de referência</span>
                    <strong class="solar-sizing-chip__value">
                        {{ $companySetting?->margin_percent ? number_format((float) $companySetting->margin_percent, 2, ',', '.') . '%' : 'Não configurada' }}
                    </strong>
                </article>
            </div>

            @if (! $usesMarketPriceFallback)
                <p class="hub-note">Preço inicial calculado pela regra simples: potência do sistema x preço por kWp da empresa. O campo permanece ajustável na edição do projeto.</p>
            @else
                <p class="hub-note">Preço inicial calculado com média de mercado de {{ 'R$ ' . number_format((float) $effectivePricePerKwp, 2, ',', '.') }}/kWp. A empresa pode substituir esse valor em <a href="{{ route('solar.settings.edit') }}">/solar/settings</a> quando quiser.</p>
            @endif

            <p class="solar-inline-tip"><strong>Status comercial:</strong> {{ $statusLabel }}. O projeto já está estruturado para evoluir de pré-orçamento para proposta sem perder o contexto comercial.</p>

            @if ($project->pricing_notes)
                <p><strong>Observações comerciais:</strong> {{ $project->pricing_notes }}</p>
            @endif
        </article>

        <article class="hub-card hub-card--subtle solar-financial-panel solar-project-show__card">
            <h2>Simulação financeira</h2>
            <div class="solar-sizing-panel__highlights solar-financial-panel__highlights">
                <article class="solar-sizing-chip solar-sizing-chip--featured">
                    <span class="solar-sizing-chip__label">Economia mensal estimada</span>
                    <strong class="solar-sizing-chip__value">
                        {{ $estimatedMonthlySavings !== null ? 'R$ ' . number_format((float) $estimatedMonthlySavings, 2, ',', '.') : 'Informe a conta de energia' }}
                    </strong>
                    <span class="solar-sizing-chip__meta">Considerando custo mínimo residual de {{ 'R$ ' . number_format((float) $residualMinimumCost, 2, ',', '.') }} por mês.</span>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Economia anual</span>
                    <strong class="solar-sizing-chip__value">
                        {{ $estimatedAnnualSavings !== null ? 'R$ ' . number_format((float) $estimatedAnnualSavings, 2, ',', '.') : 'Aguardando simulação' }}
                    </strong>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Economia em 25 anos</span>
                    <strong class="solar-sizing-chip__value">
                        {{ $estimatedLifetimeSavings !== null ? 'R$ ' . number_format((float) $estimatedLifetimeSavings, 2, ',', '.') : 'Aguardando simulação' }}
                    </strong>
                </article>
            </div>

            <p class="hub-note">Regra inicial simples: valor da conta de energia menos custo mínimo residual de {{ 'R$ ' . number_format((float) $residualMinimumCost, 2, ',', '.') }}. Sem inflação ou projeção tarifária nesta fase.</p>
        </article>

        @if ($project->notes)
            <article class="hub-card hub-card--subtle solar-project-show__card">
                <h2>Observações</h2>
                <p>{{ $project->notes }}</p>
            </article>
        @endif
    </section>
@endsection
