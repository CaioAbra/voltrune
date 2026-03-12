@extends('solar.layout')

@section('title', 'Editar orcamento | Voltrune Solar')

@php
    $customerName = $project?->customer?->name ?: '-';
    $statusOptions = [
        'draft' => 'Rascunho',
        'review' => 'Em revisao',
        'sent' => 'Enviada',
        'approved' => 'Aprovada',
        'won' => 'Fechada',
        'lost' => 'Perdida',
    ];
@endphp

@section('solar-content')
    <section class="hub-card solar-project-show solar-project-shell">
        <div class="hub-actions solar-project-show__actions">
            @if ($simulation)
                <a href="{{ route('solar.simulations.show', $simulation->id) }}" class="hub-btn hub-btn--subtle">Voltar para simulacao</a>
            @endif
            <a href="{{ route('solar.quotes.index') }}" class="hub-btn hub-btn--subtle">Todas as propostas</a>
        </div>

        <section class="hub-card hub-card--subtle solar-project-showcase solar-quote-editor-hero">
            <div class="solar-project-showcase__header">
                <div>
                    <p class="solar-section-eyebrow">Proposta comercial</p>
                    <h2>{{ $quote->title }}</h2>
                    <p class="hub-note">Edite a proposta criada a partir da simulacao e ajuste o fechamento comercial antes do envio ao cliente.</p>
                    <div class="solar-project-showcase__chips">
                        <span class="solar-mini-badge solar-mini-badge--automatic">{{ $customerName }}</span>
                        <span class="solar-mini-badge solar-mini-badge--editable">{{ $simulation?->name ?: 'Proposta manual' }}</span>
                        <span class="solar-mini-badge solar-mini-badge--automatic">{{ strtoupper((string) $quote->status) }}</span>
                    </div>
                </div>

                <div class="solar-project-showcase__status is-market">
                    <span class="solar-project-showcase__status-label">Origem comercial</span>
                    <strong>Baseada em uma simulacao pronta</strong>
                    <p>Use esta proposta para consolidar preco final, status comercial e observacoes de envio.</p>
                </div>
            </div>

            <div class="solar-project-showcase__hero-grid">
                <article class="solar-project-showcase-metric solar-project-showcase-metric--highlight">
                    <span class="solar-project-showcase-metric__label">Preco final</span>
                    <strong class="solar-project-showcase-metric__value">
                        {{ $quote->final_price ? 'R$ ' . number_format((float) $quote->final_price, 2, ',', '.') : 'Defina o valor final' }}
                    </strong>
                </article>
                <article class="solar-project-showcase-metric solar-project-showcase-metric--energy">
                    <span class="solar-project-showcase-metric__label">Potencia da simulacao</span>
                    <strong class="solar-project-showcase-metric__value">
                        {{ $simulation?->system_power_kwp ? number_format((float) $simulation->system_power_kwp, 2, ',', '.') . ' kWp' : '-' }}
                    </strong>
                </article>
                <article class="solar-project-showcase-metric solar-project-showcase-metric--energy">
                    <span class="solar-project-showcase-metric__label">Economia mensal</span>
                    <strong class="solar-project-showcase-metric__value">
                        {{ $simulation?->estimated_monthly_savings ? 'R$ ' . number_format((float) $simulation->estimated_monthly_savings, 2, ',', '.') : '-' }}
                    </strong>
                </article>
                <article class="solar-project-showcase-metric">
                    <span class="solar-project-showcase-metric__label">Payback</span>
                    <strong class="solar-project-showcase-metric__value">
                        {{ $simulation?->estimated_payback_months ? $simulation->estimated_payback_months . ' meses' : '-' }}
                    </strong>
                </article>
            </div>
        </section>

        <div class="hub-grid hub-grid--billing solar-project-show__grid">
            <article class="hub-card hub-card--subtle solar-project-show__card">
                <h2>Base da proposta</h2>
                <div class="solar-project-show__info-grid">
                    <p><strong>Projeto</strong><span>{{ $project?->name ?: '-' }}</span></p>
                    <p><strong>Cliente</strong><span>{{ $customerName }}</span></p>
                    <p><strong>Simulacao</strong><span>{{ $simulation?->name ?: 'Nao vinculada' }}</span></p>
                    <p><strong>Preco sugerido</strong><span>{{ $simulation?->suggested_price ? 'R$ ' . number_format((float) $simulation->suggested_price, 2, ',', '.') : '-' }}</span></p>
                </div>
            </article>

            <article class="hub-card hub-card--subtle solar-project-show__card">
                <h2>Edicao comercial</h2>
                <form action="{{ route('solar.quotes.update', $quote->id) }}" method="POST" class="hub-auth-form">
                    @csrf
                    @method('PUT')

                    <label class="hub-auth-label" for="quote-title">Titulo da proposta</label>
                    <input id="quote-title" name="title" type="text" class="hub-auth-input" value="{{ old('title', $quote->title) }}" required>

                    <label class="hub-auth-label" for="quote-status">Status</label>
                    <select id="quote-status" name="status" class="hub-auth-input" required>
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $quote->status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>

                    <label class="hub-auth-label" for="quote-final-price">Preco final</label>
                    <input id="quote-final-price" name="final_price" type="number" step="0.01" min="0" class="hub-auth-input" value="{{ old('final_price', $quote->final_price) }}">

                    <label class="hub-auth-label" for="quote-notes">Observacoes</label>
                    <textarea id="quote-notes" name="notes" class="hub-auth-input" rows="8">{{ old('notes', $quote->notes) }}</textarea>

                    <div class="hub-actions">
                        <button type="submit" class="hub-btn">Salvar proposta</button>
                    </div>
                </form>
            </article>
        </div>
    </section>
@endsection
