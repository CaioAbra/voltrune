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
                    <p class="hub-note">Leitura rapida do projeto para o instalador entender cliente, contexto, sistema sugerido e valor comercial sem reler todo o cadastro.</p>
                </div>

                <div class="solar-project-command__status {{ $usesMarketPriceFallback ? 'is-market' : 'is-ready' }}">
                    <span class="solar-project-command__status-label">Status do pre-orcamento</span>
                    <strong>{{ $usesMarketPriceFallback ? 'Ativo com media de mercado' : 'Valor sugerido ativo' }}</strong>
                    <p>
                        @if (! $usesMarketPriceFallback)
                            O projeto ja usa a regra simples de preco por kWp da empresa.
                        @else
                            O projeto esta usando {{ 'R$ ' . number_format((float) $effectivePricePerKwp, 2, ',', '.') }}/kWp como referencia inicial.
                        @endif
                    </p>
                </div>
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
                    <span class="solar-sizing-chip__label">Status</span>
                    <strong class="solar-sizing-chip__value">{{ $statusLabel }}</strong>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Consumo mensal</span>
                    <strong class="solar-sizing-chip__value">{{ $project->monthly_consumption_kwh ? number_format((float) $project->monthly_consumption_kwh, 2, ',', '.') . ' kWh' : 'Aguardando consumo' }}</strong>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Potencia sugerida</span>
                    <strong class="solar-sizing-chip__value">{{ $project->system_power_kwp ? number_format((float) $project->system_power_kwp, 2, ',', '.') . ' kWp' : ($estimatedRequiredPowerKwp !== null ? number_format($estimatedRequiredPowerKwp, 2, ',', '.') . ' kWp' : 'Aguardando consumo') }}</strong>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Preco sugerido</span>
                    <strong class="solar-sizing-chip__value">
                        {{ $project->suggested_price ? 'R$ ' . number_format((float) $project->suggested_price, 2, ',', '.') : ($suggestedCommercialPrice ? 'R$ ' . number_format((float) $suggestedCommercialPrice, 2, ',', '.') : 'Aguardando configuracao') }}
                    </strong>
                </article>
            </div>
        </section>

        <div class="hub-grid hub-grid--billing solar-project-show__grid">
            <article class="hub-card hub-card--subtle solar-project-show__card">
                <h2>Cliente e instalacao</h2>
                <p><strong>Cliente:</strong> {{ $project->customer?->name ?: '-' }}</p>
                <p><strong>Endereco:</strong> {{ $project->address ?: 'Endereco ainda em preparacao.' }}</p>
                <p><strong>Tipo de imovel:</strong> {{ $project->property_type ?: '-' }}</p>
                <p><strong>Geocodificacao:</strong> {{ strtoupper($project->geocoding_status ?? 'pending') }}</p>
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
                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Potencia do sistema</span>
                    <strong class="solar-sizing-chip__value">{{ $project->system_power_kwp ? number_format((float) $project->system_power_kwp, 2, ',', '.') . ' kWp' : ($estimatedRequiredPowerKwp !== null ? number_format($estimatedRequiredPowerKwp, 2, ',', '.') . ' kWp' : '-') }}</strong>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Modulos</span>
                    <strong class="solar-sizing-chip__value">{{ $project->module_quantity ?: ($suggestedModuleQuantity ?: '-') }}</strong>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Geracao estimada</span>
                    <strong class="solar-sizing-chip__value">{{ $project->estimated_generation_kwh ? number_format((float) $project->estimated_generation_kwh, 2, ',', '.') . ' kWh' : ($suggestedGenerationKwh ? number_format($suggestedGenerationKwh, 2, ',', '.') . ' kWh' : '-') }}</strong>
                </article>
            </div>

            <p><strong>Potencia do modulo:</strong> {{ $project->module_power ? number_format((int) $project->module_power, 0, ',', '.') . ' W' : '-' }}</p>
            <p><strong>Modelo do inversor:</strong> {{ $project->inverter_model ?: ($companySetting?->default_inverter_model ?: '-') }}</p>
            <p><strong>Concessionaria:</strong> {{ $project->utility_company ?: '-' }}</p>
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
                    <strong class="solar-sizing-chip__value">
                        {{ 'R$ ' . number_format((float) $effectivePricePerKwp, 2, ',', '.') }}
                    </strong>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Preco sugerido</span>
                    <strong class="solar-sizing-chip__value">
                        {{ $project->suggested_price ? 'R$ ' . number_format((float) $project->suggested_price, 2, ',', '.') : ($suggestedCommercialPrice ? 'R$ ' . number_format((float) $suggestedCommercialPrice, 2, ',', '.') : '-') }}
                    </strong>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Economia estimada</span>
                    <strong class="solar-sizing-chip__value">
                        {{ $estimatedMonthlySavings ? 'R$ ' . number_format((float) $estimatedMonthlySavings, 2, ',', '.') . '/mes' : 'Nao informada' }}
                    </strong>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Margem de referencia</span>
                    <strong class="solar-sizing-chip__value">
                        {{ $companySetting?->margin_percent ? number_format((float) $companySetting->margin_percent, 2, ',', '.') . '%' : 'Nao configurada' }}
                    </strong>
                </article>
            </div>

            @if (! $usesMarketPriceFallback)
                <p class="hub-note">Preco inicial calculado pela regra simples: potencia do sistema x preco por kWp da empresa. O campo permanece ajustavel na edicao do projeto.</p>
            @else
                <p class="hub-note">Preco inicial calculado com media de mercado de {{ 'R$ ' . number_format((float) $effectivePricePerKwp, 2, ',', '.') }}/kWp. A empresa pode substituir esse valor em <a href="{{ route('solar.settings.edit') }}">/solar/settings</a> quando quiser.</p>
            @endif

            @if ($project->pricing_notes)
                <p><strong>Observacoes comerciais:</strong> {{ $project->pricing_notes }}</p>
            @endif
        </article>

        @if ($project->notes)
            <article class="hub-card hub-card--subtle solar-project-show__card">
                <h2>Observacoes</h2>
                <p>{{ $project->notes }}</p>
            </article>
        @endif
    </section>
@endsection
