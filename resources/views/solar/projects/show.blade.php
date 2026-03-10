@extends('solar.layout')

@section('title', 'Projeto | Voltrune Solar')

@section('solar-content')
    <section class="hub-card solar-project-show">
        <div class="hub-actions solar-project-show__actions">
            <a href="{{ route('solar.projects.index') }}" class="hub-btn hub-btn--subtle">Voltar para projetos</a>
            <a href="{{ route('solar.projects.edit', $project->id) }}" class="hub-btn">Editar projeto</a>
        </div>

        <div class="hub-grid hub-grid--billing solar-project-show__grid">
            <article class="hub-card hub-card--subtle solar-project-show__card">
                <h2>{{ $project->name }}</h2>
                <p class="hub-note">Cliente vinculado: {{ $project->customer?->name ?: '-' }}</p>
                <p class="hub-note">{{ $project->address ?: 'Endereco ainda em preparacao.' }}</p>
            </article>

            <article class="hub-card hub-card--subtle solar-project-show__card">
                <h2>Consumo e rede</h2>
                <p><strong>Consumo mensal:</strong> {{ $project->monthly_consumption_kwh ? number_format((float) $project->monthly_consumption_kwh, 2, ',', '.') . ' kWh' : '-' }}</p>
                <p><strong>Valor da conta:</strong> {{ $project->energy_bill_value ? 'R$ ' . number_format((float) $project->energy_bill_value, 2, ',', '.') : '-' }}</p>
                <p><strong>Concessionaria:</strong> {{ $project->utility_company ?: '-' }}</p>
                <p><strong>Tipo de conexao:</strong> {{ match ($project->connection_type) {
                    'mono' => 'Monofasico',
                    'bi' => 'Bifasico',
                    'tri' => 'Trifasico',
                    default => '-',
                } }}</p>
            </article>
        </div>

        <div class="hub-grid hub-grid--billing solar-project-show__grid">
            <article class="hub-card hub-card--subtle solar-project-show__card">
                <h2>Estimativa inicial</h2>
                <p class="hub-note">Base simples para a futura simulacao de dimensionamento do sistema solar.</p>
                <p>
                    <strong>Potencia estimada:</strong>
                    {{ $estimatedRequiredPowerKwp !== null ? number_format($estimatedRequiredPowerKwp, 2, ',', '.') . ' kWp' : 'Informe o consumo mensal para estimar.' }}
                </p>
            </article>

            <article class="hub-card hub-card--subtle solar-project-show__card">
                <h2>Status e contexto</h2>
                <p><strong>Status:</strong> {{ strtoupper($project->status) }}</p>
                <p><strong>Geocodificacao:</strong> {{ strtoupper($project->geocoding_status ?? 'pending') }}</p>
                <p><strong>Tipo de imovel:</strong> {{ $project->property_type ?: '-' }}</p>
            </article>
        </div>

        <article class="hub-card hub-card--subtle solar-sizing-panel solar-project-show__card">
            <h2>Sistema sugerido</h2>
            <div class="solar-sizing-panel__highlights">
                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Potencia do sistema</span>
                    <strong class="solar-sizing-chip__value">{{ $project->system_power_kwp ? number_format((float) $project->system_power_kwp, 2, ',', '.') . ' kWp' : '-' }}</strong>
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
            <p><strong>Modelo do inversor:</strong> {{ $project->inverter_model ?: '-' }}</p>
        </article>

        <article class="hub-card hub-card--subtle solar-pricing-panel solar-project-show__card">
            <h2>Pre-orcamento comercial</h2>
            <div class="solar-sizing-panel__highlights solar-pricing-panel__highlights">
                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Preco por kWp</span>
                    <strong class="solar-sizing-chip__value">
                        {{ $companySetting?->price_per_kwp ? 'R$ ' . number_format((float) $companySetting->price_per_kwp, 2, ',', '.') : 'Nao configurado' }}
                    </strong>
                </article>

                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Preco sugerido</span>
                    <strong class="solar-sizing-chip__value">
                        {{ $project->suggested_price ? 'R$ ' . number_format((float) $project->suggested_price, 2, ',', '.') : ($suggestedCommercialPrice ? 'R$ ' . number_format((float) $suggestedCommercialPrice, 2, ',', '.') : '-') }}
                    </strong>
                </article>
            </div>

            @if ($companySetting?->price_per_kwp)
                <p class="hub-note">Preco inicial calculado pela regra simples: potencia do sistema x preco por kWp da empresa.</p>
            @else
                <p class="hub-note">Defina o preco por kWp em <strong>/solar/settings</strong> para ativar o pre-orcamento automatico.</p>
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
