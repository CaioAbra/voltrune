@extends('solar.layout')

@section('title', 'Simulações | Solar')

@section('solar-content')
    <section class="hub-card">
        <div class="solar-flow-section__header">
            <div>
                <p class="solar-section-eyebrow">Simulações</p>
                <h2>Biblioteca de cenários</h2>
            </div>
            <p class="hub-note">Cada simulação representa um cenário técnico/comercial associado a um projeto.</p>
        </div>

        <div class="solar-sizing-panel__highlights">
            @forelse ($simulations as $simulation)
                <article class="solar-sizing-chip {{ $loop->first ? 'solar-sizing-chip--featured solar-sizing-chip--commercial' : '' }}">
                    <span class="solar-sizing-chip__label">{{ $simulation->name }}</span>
                    <strong class="solar-sizing-chip__value">
                        {{ $simulation->system_power_kwp ? number_format((float) $simulation->system_power_kwp, 2, ',', '.') . ' kWp' : 'Potência pendente' }}
                    </strong>
                    <span class="hub-note">
                        Projeto: {{ $simulation->project?->name ?: '-' }}
                        ·
                        Cliente: {{ $simulation->project?->customer?->name ?: '-' }}
                    </span>
                    <a href="{{ route('solar.simulations.show', $simulation->id) }}" class="hub-link">Abrir simulacao</a>
                </article>
            @empty
                <article class="solar-sizing-chip">
                    <span class="solar-sizing-chip__label">Simulações</span>
                    <strong class="solar-sizing-chip__value">Nenhum cenário criado</strong>
                    <span class="hub-note">As simulações serão geradas a partir dos projetos para preparar orçamentos múltiplos.</span>
                </article>
            @endforelse
        </div>
    </section>
@endsection
