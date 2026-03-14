@extends('solar.layout')

@section('title', 'Editar simulacao | Voltrune Solar')

@php
    $statusOptions = [
        'draft' => 'Rascunho',
        'qualified' => 'Em analise',
        'proposal' => 'Pronta para proposta',
        'won' => 'Fechada',
    ];
    $customerName = $project->customer?->name ?: '-';
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
                    <p class="solar-section-eyebrow">Editor de simulacao</p>
                    <h2>{{ $simulation->name }}</h2>
                    <p class="hub-note">Agora o cenario pode ser ajustado sem reescrever a base do projeto nem destruir o historico das outras simulacoes.</p>
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
                    <p class="solar-section-eyebrow">Ajuste do cenario</p>
                    <h2>Parametros tecnicos e comerciais</h2>
                    <p class="hub-note">Edite o cenario atual sem alterar cliente, local ou consumo do projeto.</p>
                </div>
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
                    </div>

                    <div>
                        <label class="hub-auth-label" for="simulation-module-power">Potencia do modulo (W)</label>
                        <input id="simulation-module-power" name="module_power" type="number" step="1" min="1" class="hub-auth-input" value="{{ old('module_power', $simulation->module_power ?: ($companySetting?->default_module_power ?: 550)) }}">
                    </div>

                    <div>
                        <label class="hub-auth-label" for="simulation-module-quantity">Quantidade de modulos</label>
                        <input id="simulation-module-quantity" name="module_quantity" type="number" step="1" min="1" class="hub-auth-input" value="{{ old('module_quantity', $simulation->module_quantity) }}">
                    </div>
                </div>

                <div class="hub-grid solar-quote-item-form__grid solar-quote-item-form__grid--metrics">
                    <div>
                        <label class="hub-auth-label" for="simulation-generation">Geracao estimada (kWh/mes)</label>
                        <input id="simulation-generation" name="estimated_generation_kwh" type="number" step="0.01" min="0" class="hub-auth-input" value="{{ old('estimated_generation_kwh', $simulation->estimated_generation_kwh) }}">
                    </div>

                    <div>
                        <label class="hub-auth-label" for="simulation-price">Preco sugerido (R$)</label>
                        <input id="simulation-price" name="suggested_price" type="number" step="0.01" min="0" class="hub-auth-input" value="{{ old('suggested_price', $simulation->suggested_price) }}">
                    </div>

                    <div>
                        <label class="hub-auth-label" for="simulation-inverter">Modelo do inversor</label>
                        <input id="simulation-inverter" name="inverter_model" type="text" class="hub-auth-input" value="{{ old('inverter_model', $simulation->inverter_model) }}">
                    </div>
                </div>

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
