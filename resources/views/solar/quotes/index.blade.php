@extends('solar.layout')

@section('title', 'Orcamentos | Solar')

@php
    $statusLabels = [
        'draft' => 'Rascunho',
        'review' => 'Em analise',
        'sent' => 'Enviado',
        'approved' => 'Aprovado',
        'won' => 'Fechado',
        'lost' => 'Perdido',
    ];
@endphp

@section('solar-content')
    <section class="hub-card solar-project-show solar-project-shell">
        <section class="hub-card hub-card--subtle solar-project-context-hero">
            <div class="solar-project-context-hero__header">
                <div>
                    <p class="solar-section-eyebrow">Propostas e orcamentos</p>
                    <h2>Pipeline comercial do Solar</h2>
                    <p class="hub-note">As propostas nascem da simulacao e consolidam o preco final, o status de envio e a negociacao com o cliente.</p>
                </div>

                <div class="solar-project-context-hero__focus">
                    <span class="solar-project-showcase__status-label">Fluxo recomendado</span>
                    <strong>Simulacao -> proposta -> fechamento</strong>
                    <p>Compare cenarios no projeto, escolha a melhor simulacao e transforme o resultado em proposta editavel.</p>
                </div>
            </div>
        </section>

        <article class="hub-card hub-card--subtle solar-project-show__card solar-project-simulations-panel">
            <div class="solar-flow-section__header">
                <div>
                    <p class="solar-section-eyebrow">Lista de propostas</p>
                    <h2>Orcamentos gerados</h2>
                </div>
                <div class="solar-project-showcase__status is-ready">
                    <span class="solar-project-showcase__status-label">Leitura operacional</span>
                    <strong>{{ $quotes->count() }} {{ $quotes->count() === 1 ? 'proposta ativa' : 'propostas ativas' }}</strong>
                    <p>Novas propostas sao criadas a partir da tela de simulacao.</p>
                </div>
            </div>

            <div class="solar-project-simulations-panel__grid">
                @forelse ($quotes as $quote)
                    <article class="solar-project-simulation-card">
                        <div class="solar-project-simulation-card__header">
                            <div>
                                <span class="solar-project-simulation-card__eyebrow">Proposta</span>
                                <h3>{{ $quote->title }}</h3>
                            </div>
                            <span class="solar-mini-badge solar-mini-badge--automatic">{{ $statusLabels[$quote->status] ?? strtoupper((string) $quote->status) }}</span>
                        </div>

                        <div class="solar-project-simulation-card__body">
                            <p class="hub-note solar-project-simulation-card__summary">
                                {{ $quote->project?->customer?->name ?: 'Cliente nao vinculado' }}
                                @if ($quote->simulation)
                                    | origem: {{ $quote->simulation->name }}
                                @endif
                            </p>

                            <div class="solar-project-simulation-card__metrics">
                                <span><strong>Itens</strong>{{ $quote->items->count() }}</span>
                                <span><strong>Preco final</strong>{{ $quote->final_price ? 'R$ ' . number_format((float) $quote->final_price, 2, ',', '.') : '-' }}</span>
                                <span><strong>Economia</strong>{{ $quote->estimated_savings ? 'R$ ' . number_format((float) $quote->estimated_savings, 2, ',', '.') . '/mes' : '-' }}</span>
                                <span><strong>Payback</strong>{{ $quote->payback_months ? $quote->payback_months . ' meses' : '-' }}</span>
                                <span><strong>Projeto</strong>{{ $quote->project?->name ?: '-' }}</span>
                            </div>
                        </div>

                        <div class="solar-project-simulation-card__footer">
                            <a href="{{ route('solar.quotes.edit', $quote->id) }}" class="hub-btn">Abrir proposta</a>
                        </div>
                    </article>
                @empty
                    <article class="solar-project-simulation-card solar-project-simulation-card--empty">
                        <div class="solar-project-simulation-card__header">
                            <div>
                                <span class="solar-project-simulation-card__eyebrow">Sem propostas</span>
                                <h3>Nenhum orcamento criado</h3>
                            </div>
                        </div>
                        <p class="hub-note">Abra uma simulacao e use a acao principal "Gerar proposta" para iniciar o fluxo comercial.</p>
                    </article>
                @endforelse
            </div>
        </article>
    </section>
@endsection
