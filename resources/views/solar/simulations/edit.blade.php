@extends('solar.layout')

@section('title', 'Editar simulacao | Voltrune Solar')

@php
    $statusOptions = [
        'draft' => 'Base automatica',
        'qualified' => 'Em revisao',
        'proposal' => 'Pronta para orcamento',
        'won' => 'Fechada',
    ];
    $paymentModeOptions = [
        'cash' => 'A vista',
        'financed' => 'Financiado',
    ];
    $customerName = $project->customer?->name ?: '-';
    $basePayload = app(\App\Modules\Solar\Services\SolarSimulationService::class)->buildPayloadFromProject($project, $companySetting);
    $numbersDiffer = static fn ($left, $right, float $precision = 0.01): bool => $left !== null
        && $right !== null
        && abs((float) $left - (float) $right) > $precision;
    $stringsDiffer = static fn ($left, $right): bool => trim((string) $left) !== ''
        && trim((string) $right) !== ''
        && mb_strtolower(trim((string) $left)) !== mb_strtolower(trim((string) $right));
    $fieldOrigins = [
        'system_power' => $numbersDiffer(old('system_power_kwp', $simulation->system_power_kwp), $basePayload['system_power_kwp'] ?? null)
            ? ['state' => 'manual', 'badge' => 'Ajustado nesta simulacao']
            : ['state' => 'base', 'badge' => 'Base do projeto'],
        'module_power' => $numbersDiffer(old('module_power', $simulation->module_power), $basePayload['module_power'] ?? null, 0.5)
            ? ['state' => 'manual', 'badge' => 'Ajustado nesta simulacao']
            : ['state' => 'base', 'badge' => 'Base do projeto'],
        'module_quantity' => $numbersDiffer(old('module_quantity', $simulation->module_quantity), $basePayload['module_quantity'] ?? null, 0.5)
            ? ['state' => 'manual', 'badge' => 'Ajustado nesta simulacao']
            : ['state' => 'base', 'badge' => 'Base do projeto'],
        'generation' => $numbersDiffer(old('estimated_generation_kwh', $simulation->estimated_generation_kwh), $basePayload['estimated_generation_kwh'] ?? null)
            ? ['state' => 'manual', 'badge' => 'Ajustado nesta simulacao']
            : ['state' => 'base', 'badge' => 'Base do projeto'],
        'price' => $numbersDiffer(old('suggested_price', $simulation->suggested_price), $basePayload['suggested_price'] ?? null)
            ? ['state' => 'manual', 'badge' => 'Ajustado nesta simulacao']
            : ['state' => 'base', 'badge' => 'Base do projeto'],
        'inverter' => $stringsDiffer(old('inverter_model', $simulation->inverter_model), $basePayload['inverter_model'] ?? null)
            ? ['state' => 'manual', 'badge' => 'Ajustado nesta simulacao']
            : ['state' => 'base', 'badge' => 'Base do projeto'],
    ];
    $manualOriginCount = collect($fieldOrigins)->where('state', 'manual')->count();
@endphp

@section('solar-content')
    <section class="hub-card solar-project-show solar-project-shell">
        <div class="hub-actions solar-project-show__actions">
            <a href="{{ route('solar.simulations.show', $simulation->id) }}" class="hub-btn hub-btn--subtle">Voltar a simulacao</a>
            <a href="{{ route('solar.projects.show', $project->id) }}" class="hub-btn hub-btn--subtle">Abrir projeto base</a>
        </div>

        <section class="hub-card hub-card--subtle solar-project-showcase">
            <div class="solar-project-showcase__header">
                <div>
                    <p class="solar-section-eyebrow">Editar simulacao</p>
                    <h2>{{ $simulation->name }}</h2>
                    <p class="hub-note">Ajuste a simulacao sem reescrever a base do projeto e sem perder o historico das outras revisoes.</p>
                </div>

                <div class="solar-project-showcase__status is-ready">
                    <span class="solar-project-showcase__status-label">Projeto base</span>
                    <strong>{{ $project->name }}</strong>
                    <p>{{ $customerName }}</p>
                </div>
            </div>

            <div class="solar-project-showcase__hero-grid">
                <article class="solar-project-showcase-metric">
                    <span class="solar-project-showcase-metric__label">Potencia atual</span>
                    <strong class="solar-project-showcase-metric__value">{{ $simulation->system_power_kwp ? number_format((float) $simulation->system_power_kwp, 2, ',', '.') . ' kWp' : '-' }}</strong>
                </article>
                <article class="solar-project-showcase-metric solar-project-showcase-metric--highlight">
                    <span class="solar-project-showcase-metric__label">Preco sugerido</span>
                    <strong class="solar-project-showcase-metric__value">{{ $simulation->suggested_price ? 'R$ ' . number_format((float) $simulation->suggested_price, 2, ',', '.') : '-' }}</strong>
                </article>
                <article class="solar-project-showcase-metric solar-project-showcase-metric--commercial">
                    <span class="solar-project-showcase-metric__label">Economia mensal</span>
                    <strong class="solar-project-showcase-metric__value">{{ $simulation->estimated_monthly_savings ? 'R$ ' . number_format((float) $simulation->estimated_monthly_savings, 2, ',', '.') : '-' }}</strong>
                </article>
                <article class="solar-project-showcase-metric">
                    <span class="solar-project-showcase-metric__label">Orcamentos vinculados</span>
                    <strong class="solar-project-showcase-metric__value">{{ $simulation->quotes->count() }}</strong>
                </article>
            </div>
        </section>

        <section class="hub-card hub-card--subtle solar-flow-section">
            <div class="solar-flow-section__header solar-flow-section__header--stacked-md">
                <div>
                    <p class="solar-section-eyebrow">Ajuste da simulacao</p>
                    <h2>Parametros tecnicos e comerciais</h2>
                    <p class="hub-note">Edite a simulacao atual sem alterar cliente, local ou consumo do projeto.</p>
                </div>
                <div class="solar-project-showcase__status {{ $manualOriginCount > 0 ? 'is-market' : 'is-ready' }}">
                    <span class="solar-project-showcase__status-label">Origem da leitura</span>
                    <strong>{{ $manualOriginCount }} {{ $manualOriginCount === 1 ? 'campo ajustado' : 'campos ajustados' }}</strong>
                    <p>{{ $manualOriginCount > 0 ? 'Os campos marcados como ajustados nao seguem mais a base atual do projeto.' : 'Os campos principais ainda espelham a base atual do projeto.' }}</p>
                </div>
            </div>

            <div class="solar-origin-grid">
                <article class="solar-origin-card solar-origin-card--{{ $fieldOrigins['system_power']['state'] }}">
                    <span class="solar-origin-card__label">Potencia do sistema</span>
                    <strong>{{ $fieldOrigins['system_power']['badge'] }}</strong>
                    <p>Compare a potencia atual com a potencia herdada do projeto base.</p>
                </article>
                <article class="solar-origin-card solar-origin-card--{{ $fieldOrigins['module_power']['state'] }}">
                    <span class="solar-origin-card__label">Potencia do modulo</span>
                    <strong>{{ $fieldOrigins['module_power']['badge'] }}</strong>
                    <p>Identifica quando a configuracao do modulo foi refinada nesta revisao.</p>
                </article>
                <article class="solar-origin-card solar-origin-card--{{ $fieldOrigins['generation']['state'] }}">
                    <span class="solar-origin-card__label">Geracao estimada</span>
                    <strong>{{ $fieldOrigins['generation']['badge'] }}</strong>
                    <p>Mostra se a leitura de geracao ainda acompanha o calculo herdado do projeto.</p>
                </article>
            </div>

            <form action="{{ route('solar.simulations.update', $simulation->id) }}" method="POST" class="hub-auth-form">
                @csrf
                @method('PUT')

                <div class="hub-grid solar-quote-item-form__grid solar-quote-item-form__grid--duo">
                    <div>
                        <label class="hub-auth-label" for="simulation-name">Nome da simulacao</label>
                        <input id="simulation-name" name="name" type="text" class="hub-auth-input" value="{{ old('name', $simulation->name) }}" required>
                    </div>

                    <div>
                        <label class="hub-auth-label" for="simulation-status">Status</label>
                        <select id="simulation-status" name="status" class="hub-auth-input" required>
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $simulation->status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="hub-grid solar-quote-item-form__grid solar-quote-item-form__grid--metrics">
                    <div>
                        <label class="hub-auth-label" for="simulation-power">Potencia do sistema (kWp)</label>
                        <input id="simulation-power" name="system_power_kwp" type="number" step="0.01" min="0" class="hub-auth-input" value="{{ old('system_power_kwp', $simulation->system_power_kwp) }}">
                        <p class="solar-field-note"><span class="solar-origin-pill solar-origin-pill--{{ $fieldOrigins['system_power']['state'] }}">{{ $fieldOrigins['system_power']['badge'] }}</span> Use este campo para criar uma alternativa diferente da potencia original do projeto.</p>
                    </div>

                    <div>
                        <label class="hub-auth-label" for="simulation-module-power">Potencia do modulo (W)</label>
                        <input id="simulation-module-power" name="module_power" type="number" step="1" min="1" class="hub-auth-input" value="{{ old('module_power', $simulation->module_power ?: ($companySetting?->default_module_power ?: 550)) }}">
                        <p class="solar-field-note"><span class="solar-origin-pill solar-origin-pill--{{ $fieldOrigins['module_power']['state'] }}">{{ $fieldOrigins['module_power']['badge'] }}</span> Altere quando quiser comparar outro modulo sem mexer no projeto base.</p>
                    </div>

                    <div>
                        <label class="hub-auth-label" for="simulation-module-quantity">Quantidade de modulos</label>
                        <input id="simulation-module-quantity" name="module_quantity" type="number" step="1" min="1" class="hub-auth-input" value="{{ old('module_quantity', $simulation->module_quantity) }}">
                        <p class="solar-field-note"><span class="solar-origin-pill solar-origin-pill--{{ $fieldOrigins['module_quantity']['state'] }}">{{ $fieldOrigins['module_quantity']['badge'] }}</span> Bom para testar composicao comercial sem regravar o projeto.</p>
                    </div>
                </div>

                <div class="hub-grid solar-quote-item-form__grid solar-quote-item-form__grid--metrics">
                    <div>
                        <label class="hub-auth-label" for="simulation-generation">Geracao estimada (kWh/mes)</label>
                        <input id="simulation-generation" name="estimated_generation_kwh" type="number" step="0.01" min="0" class="hub-auth-input" value="{{ old('estimated_generation_kwh', $simulation->estimated_generation_kwh) }}">
                        <p class="solar-field-note"><span class="solar-origin-pill solar-origin-pill--{{ $fieldOrigins['generation']['state'] }}">{{ $fieldOrigins['generation']['badge'] }}</span> Refine a leitura quando precisar defender uma previsao mais realista.</p>
                    </div>

                    <div>
                        <label class="hub-auth-label" for="simulation-price">Preco sugerido (R$)</label>
                        <input id="simulation-price" name="suggested_price" type="number" step="0.01" min="0" class="hub-auth-input" value="{{ old('suggested_price', $simulation->suggested_price) }}">
                        <p class="solar-field-note"><span class="solar-origin-pill solar-origin-pill--{{ $fieldOrigins['price']['state'] }}">{{ $fieldOrigins['price']['badge'] }}</span> Ajuste aqui quando quiser comparar outra leitura comercial.</p>
                    </div>

                    <div>
                        <label class="hub-auth-label" for="simulation-inverter">Modelo do inversor</label>
                        <input id="simulation-inverter" name="inverter_model" type="text" class="hub-auth-input" value="{{ old('inverter_model', $simulation->inverter_model) }}">
                        <p class="solar-field-note"><span class="solar-origin-pill solar-origin-pill--{{ $fieldOrigins['inverter']['state'] }}">{{ $fieldOrigins['inverter']['badge'] }}</span> Troque apenas quando esta simulacao exigir outra configuracao de inversor.</p>
                    </div>
                </div>

                <section class="hub-card hub-card--subtle solar-flow-section solar-financial-panel">
                    <div class="solar-flow-section__header solar-flow-section__header--stacked-md">
                        <div>
                            <p class="solar-section-eyebrow">Cenario financeiro</p>
                            <h2>Pagamento e fluxo de economia</h2>
                            <p class="hub-note">Defina se a leitura e a vista ou financiada para o Solar recalcular parcela, beneficio liquido e economia projetada.</p>
                        </div>
                        <div class="solar-project-showcase__status is-market">
                            <span class="solar-project-showcase__status-label">Leitura comercial</span>
                            <strong>{{ ($simulation->payment_mode ?? 'cash') === 'financed' ? 'Cenario financiado ativo' : 'Cenario a vista ativo' }}</strong>
                            <p>{{ ($simulation->payment_mode ?? 'cash') === 'financed' ? 'Use entrada, parcelas e taxa para defender o ganho liquido durante o financiamento.' : 'A simulacao segue como leitura a vista, com economia liquida igual a economia mensal estimada.' }}</p>
                        </div>
                    </div>

                    <div class="hub-grid solar-quote-item-form__grid solar-quote-item-form__grid--duo">
                        <div>
                            <label class="hub-auth-label" for="simulation-payment-mode">Modo de pagamento</label>
                            <select id="simulation-payment-mode" name="payment_mode" class="hub-auth-input">
                                @foreach ($paymentModeOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('payment_mode', $simulation->payment_mode ?: 'cash') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="hub-auth-label" for="simulation-upfront-payment">Entrada prevista (R$)</label>
                            <input id="simulation-upfront-payment" name="upfront_payment" type="number" step="0.01" min="0" class="hub-auth-input" value="{{ old('upfront_payment', $simulation->upfront_payment) }}">
                        </div>
                    </div>

                    <div class="hub-grid solar-quote-item-form__grid solar-quote-item-form__grid--metrics">
                        <div>
                            <label class="hub-auth-label" for="simulation-installment-count">Quantidade de parcelas</label>
                            <input id="simulation-installment-count" name="installment_count" type="number" step="1" min="1" class="hub-auth-input" value="{{ old('installment_count', $simulation->installment_count ?: \App\Modules\Solar\Services\SolarSizingService::DEFAULT_INSTALLMENT_COUNT) }}">
                        </div>

                        <div>
                            <label class="hub-auth-label" for="simulation-interest-rate">Taxa mensal (%)</label>
                            <input id="simulation-interest-rate" name="monthly_interest_rate" type="number" step="0.001" min="0" class="hub-auth-input" value="{{ old('monthly_interest_rate', $simulation->monthly_interest_rate ?: \App\Modules\Solar\Services\SolarSizingService::DEFAULT_MONTHLY_INTEREST_RATE) }}">
                        </div>

                        <div>
                            <label class="hub-auth-label" for="simulation-tariff-growth">Reajuste tarifario anual (%)</label>
                            <input id="simulation-tariff-growth" name="tariff_growth_yearly" type="number" step="0.01" min="0" class="hub-auth-input" value="{{ old('tariff_growth_yearly', $simulation->tariff_growth_yearly ?: \App\Modules\Solar\Services\SolarSizingService::DEFAULT_TARIFF_GROWTH_YEARLY) }}">
                        </div>
                    </div>

                    <p class="solar-field-note">
                        Se o modo for financiado, o Solar recalcula automaticamente valor financiado, parcela estimada, beneficio liquido mensal e economia projetada em cinco anos.
                    </p>
                </section>

                <div>
                    <label class="hub-auth-label" for="simulation-notes">Observacoes</label>
                    <textarea id="simulation-notes" name="notes" rows="6" class="hub-auth-input">{{ old('notes', $simulation->notes) }}</textarea>
                </div>

                <div class="hub-actions">
                    <button type="submit" class="hub-btn">Salvar simulacao</button>
                </div>
            </form>
        </section>
    </section>
@endsection
