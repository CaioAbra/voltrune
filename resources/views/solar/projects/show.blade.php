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
    $displayAddress = $project->address ?: collect([
        $project->street ?: null,
        $project->number ?: null,
        $project->complement ?: null,
        $project->district ?: null,
        $project->city ?: null,
        $project->state ?: null,
    ])->filter()->implode(', ');
    $simulationCount = $simulations->count();
    $primarySimulation = $defaultSimulation;
    $quoteCount = $quotes->count();
@endphp

@section('solar-content')
    <section class="space-y-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Projeto solar</p>
                <h1 class="text-3xl font-semibold text-slate-900">{{ $project->name }}</h1>
                <p class="mt-2 max-w-3xl text-sm text-slate-600">
                    Esta tela concentra cliente, local e consumo. As leituras de cenarios ficam nas simulacoes e a composicao comercial fica nos orcamentos.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <form method="POST" action="{{ route('solar.projects.simulations.store', $project->id) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-slate-800">
                        Nova simulacao
                    </button>
                </form>

                @if ($primarySimulation)
                    <form method="POST" action="{{ route('solar.simulations.quotes.store', $primarySimulation->id) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
                            Novo orcamento
                        </button>
                    </form>
                @endif

                <a href="{{ route('solar.projects.edit', $project->id) }}" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
                    Editar projeto
                </a>
                <a href="{{ route('solar.projects.index') }}" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
                    Voltar para projetos
                </a>
            </div>
        </div>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-6 lg:grid-cols-[minmax(0,1.6fr)_minmax(280px,0.9fr)]">
                <div class="space-y-5">
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">{{ $statusLabel }}</span>
                        <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">{{ $simulationCount }} {{ $simulationCount === 1 ? 'simulacao' : 'simulacoes' }}</span>
                        <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700">{{ $quoteCount }} {{ $quoteCount === 1 ? 'orcamento' : 'orcamentos' }}</span>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                        <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <span class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Cliente</span>
                            <p class="mt-2 text-base font-semibold text-slate-900">{{ $project->customer?->name ?: '-' }}</p>
                        </article>
                        <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <span class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Endereco</span>
                            <p class="mt-2 text-base font-semibold text-slate-900">{{ $displayAddress !== '' ? $displayAddress : 'Endereco em preparacao' }}</p>
                        </article>
                        <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <span class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Cidade / Estado</span>
                            <p class="mt-2 text-base font-semibold text-slate-900">{{ $locationSummary !== '' ? $locationSummary : '-' }}</p>
                        </article>
                        <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <span class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Concessionaria</span>
                            <p class="mt-2 text-base font-semibold text-slate-900">{{ $project->utility_company ?: '-' }}</p>
                        </article>
                        <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <span class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Consumo mensal</span>
                            <p class="mt-2 text-base font-semibold text-slate-900">
                                {{ $project->monthly_consumption_kwh ? number_format((float) $project->monthly_consumption_kwh, 2, ',', '.') . ' kWh/mes' : '-' }}
                            </p>
                        </article>
                    </div>
                </div>

                <aside class="rounded-2xl border border-slate-200 bg-gradient-to-br from-slate-900 to-slate-800 p-5 text-white shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-300">Proximo passo</p>
                    <h2 class="mt-3 text-xl font-semibold">
                        {{ $primarySimulation?->name ?: 'Crie a primeira simulacao' }}
                    </h2>
                    <p class="mt-3 text-sm leading-6 text-slate-300">
                        @if ($primarySimulation)
                            Abra a simulacao principal para revisar potencia, geracao, preco e indicadores financeiros antes de montar a proposta.
                        @else
                            O projeto organiza o contexto. A primeira simulacao abre a leitura tecnica e comercial do cenario.
                        @endif
                    </p>

                    @if ($primarySimulation)
                        <a href="{{ route('solar.simulations.show', $primarySimulation->id) }}" class="mt-5 inline-flex items-center rounded-xl bg-white px-4 py-2.5 text-sm font-medium text-slate-900 transition-colors hover:bg-slate-100">
                            Ver simulacao principal
                        </a>
                    @endif
                </aside>
            </div>
        </section>

        <section class="space-y-4">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Simulacoes</p>
                    <h2 class="text-2xl font-semibold text-slate-900">Cenarios tecnico-comerciais</h2>
                    <p class="mt-1 text-sm text-slate-600">Use esta lista para comparar alternativas e seguir para o orcamento certo.</p>
                </div>

                <form method="POST" action="{{ route('solar.projects.simulations.store', $project->id) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-slate-800">
                        Nova simulacao
                    </button>
                </form>
            </div>

            <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
                @forelse ($simulations as $simulation)
                    @php
                        $simulationStatusLabel = match ($simulation->status) {
                            'draft' => 'Rascunho',
                            'qualified' => 'Em analise',
                            'proposal' => 'Pronta para proposta',
                            'won' => 'Fechada',
                            default => strtoupper((string) $simulation->status),
                        };
                    @endphp
                    <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:-translate-y-0.5 hover:bg-slate-50 hover:shadow-md">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                                    {{ $loop->first ? 'Simulacao principal' : 'Simulacao' }}
                                </p>
                                <h3 class="mt-2 text-xl font-semibold text-slate-900">{{ $simulation->name }}</h3>
                                <p class="mt-2 text-sm text-slate-600">
                                    Cenário de leitura técnica e comercial pronto para revisão e conversão em orçamento.
                                </p>
                            </div>

                            <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">{{ $simulationStatusLabel }}</span>
                        </div>

                        <div class="mt-6 grid grid-cols-2 gap-3">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Potencia</span>
                                <p class="mt-2 text-lg font-semibold text-slate-900">
                                    {{ $simulation->system_power_kwp ? number_format((float) $simulation->system_power_kwp, 2, ',', '.') . ' kWp' : '-' }}
                                </p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Geracao estimada</span>
                                <p class="mt-2 text-lg font-semibold text-slate-900">
                                    {{ $simulation->estimated_generation_kwh ? number_format((float) $simulation->estimated_generation_kwh, 2, ',', '.') . ' kWh/mes' : '-' }}
                                </p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Preco sugerido</span>
                                <p class="mt-2 text-lg font-semibold text-slate-900">
                                    {{ $simulation->suggested_price ? 'R$ ' . number_format((float) $simulation->suggested_price, 2, ',', '.') : '-' }}
                                </p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Economia mensal</span>
                                <p class="mt-2 text-lg font-semibold text-slate-900">
                                    {{ $simulation->estimated_monthly_savings ? 'R$ ' . number_format((float) $simulation->estimated_monthly_savings, 2, ',', '.') : '-' }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-wrap gap-3">
                            <a href="{{ route('solar.simulations.show', $simulation->id) }}" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-slate-800">
                                Ver simulacao
                            </a>

                            <form action="{{ route('solar.simulations.duplicate', $simulation->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50">
                                    Duplicar simulacao
                                </button>
                            </form>

                            <form action="{{ route('solar.simulations.quotes.store', $simulation->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50">
                                    Gerar orcamento
                                </button>
                            </form>
                        </div>
                    </article>
                @empty
                    <article class="rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Sem simulacoes</p>
                        <h3 class="mt-3 text-xl font-semibold text-slate-900">Crie o primeiro cenario deste projeto</h3>
                        <p class="mt-2 text-sm text-slate-600">
                            A simulacao vira a tela principal de leitura do Solar. Comece por ela para sair do contexto e entrar na analise.
                        </p>
                    </article>
                @endforelse
            </div>
        </section>

        <section class="space-y-4">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Orcamentos</p>
                    <h2 class="text-2xl font-semibold text-slate-900">Propostas relacionadas</h2>
                    <p class="mt-1 text-sm text-slate-600">Os orcamentos consolidam materiais, servicos, preco final e margem para envio ao cliente.</p>
                </div>

                @if ($primarySimulation)
                    <form method="POST" action="{{ route('solar.simulations.quotes.store', $primarySimulation->id) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
                            Novo orcamento
                        </button>
                    </form>
                @endif
            </div>

            <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
                @forelse ($quotes as $quote)
                    @php
                        $quoteStatusLabel = match ($quote->status) {
                            'draft' => 'Rascunho',
                            'review' => 'Em analise',
                            'sent' => 'Enviado',
                            'approved' => 'Aprovado',
                            'won' => 'Fechado',
                            'lost' => 'Perdido',
                            default => strtoupper((string) $quote->status),
                        };
                    @endphp
                    <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:-translate-y-0.5 hover:bg-slate-50 hover:shadow-md">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Orcamento</p>
                                <h3 class="mt-2 text-xl font-semibold text-slate-900">{{ $quote->title }}</h3>
                                <p class="mt-2 text-sm text-slate-600">{{ $quote->simulation?->name ?: 'Sem simulacao vinculada' }}</p>
                            </div>
                            <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">{{ $quoteStatusLabel }}</span>
                        </div>

                        <div class="mt-6 grid grid-cols-2 gap-3">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Preco final</span>
                                <p class="mt-2 text-lg font-semibold text-slate-900">{{ $quote->final_price ? 'R$ ' . number_format((float) $quote->final_price, 2, ',', '.') : '-' }}</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Itens</span>
                                <p class="mt-2 text-lg font-semibold text-slate-900">{{ $quote->items->count() }}</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Economia</span>
                                <p class="mt-2 text-lg font-semibold text-slate-900">
                                    {{ $quote->estimated_savings ? 'R$ ' . number_format((float) $quote->estimated_savings, 2, ',', '.') . '/mes' : '-' }}
                                </p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Status</span>
                                <p class="mt-2 text-lg font-semibold text-slate-900">{{ $quoteStatusLabel }}</p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <a href="{{ route('solar.quotes.edit', $quote->id) }}" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50">
                                Abrir orcamento
                            </a>
                        </div>
                    </article>
                @empty
                    <article class="rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Sem orcamentos</p>
                        <h3 class="mt-3 text-xl font-semibold text-slate-900">Nenhuma proposta criada ainda</h3>
                        <p class="mt-2 text-sm text-slate-600">Valide uma simulacao e gere o primeiro orcamento quando o cenario estiver pronto.</p>
                    </article>
                @endforelse
            </div>
        </section>

        @if ($project->pricing_notes || $project->notes)
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Observacoes</p>
                <h2 class="mt-2 text-2xl font-semibold text-slate-900">Anotacoes do projeto base</h2>

                <div class="mt-5 space-y-4 text-sm leading-6 text-slate-700">
                    @if ($project->pricing_notes)
                        <p><strong class="text-slate-900">Notas comerciais:</strong> {{ $project->pricing_notes }}</p>
                    @endif
                    @if ($project->notes)
                        <p><strong class="text-slate-900">Notas gerais:</strong> {{ $project->notes }}</p>
                    @endif
                </div>
            </section>
        @endif
    </section>
@endsection
