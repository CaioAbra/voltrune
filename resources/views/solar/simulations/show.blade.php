@extends('solar.layout')

@section('title', 'Simulacao | Voltrune Solar')

@php
    $projectAddress = collect([
        $project->street ?: null,
        $project->number ?: null,
        $project->complement ?: null,
        $project->district ?: null,
        $project->city ?: null,
        $project->state ?: null,
    ])->filter()->implode(', ');
    $statusLabel = match ($simulation->status) {
        'draft' => 'Rascunho',
        'qualified' => 'Em analise',
        'proposal' => 'Pronta para proposta',
        'won' => 'Fechado',
        default => strtoupper((string) $simulation->status),
    };
    $solarSourceLabel = strtoupper(($simulation->solar_factor_source ?? 'fallback') === 'pvgis' ? 'PVGIS' : 'PADRAO');
    $radiationDaily = $sizingService->estimateEquivalentSolarRadiationDaily($simulation->solar_factor_used);
    $quoteCount = $simulation->quotes->count();
    $readyForProposal = $simulation->suggested_price && $simulation->system_power_kwp;
@endphp

@section('solar-content')
    <section class="space-y-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Simulacao solar</p>
                <h1 class="text-3xl font-semibold text-slate-900">{{ $simulation->name }}</h1>
                <p class="mt-2 max-w-3xl text-sm text-slate-600">
                    Esta e a tela principal de leitura do cenario. Revise sistema, indicadores financeiros e siga para o orcamento quando estiver pronto.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <form action="{{ route('solar.simulations.quotes.store', $simulation->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-slate-800">
                        Gerar orcamento
                    </button>
                </form>

                <form action="{{ route('solar.simulations.duplicate', $simulation->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
                        Duplicar simulacao
                    </button>
                </form>

                <a href="{{ route('solar.projects.edit', $project->id) }}" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
                    Editar simulacao
                </a>
                <a href="{{ route('solar.projects.show', $project->id) }}" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
                    Voltar ao projeto
                </a>
            </div>
        </div>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-6 lg:grid-cols-[minmax(0,1.6fr)_minmax(280px,0.9fr)]">
                <div>
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">{{ $statusLabel }}</span>
                        <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">{{ $quoteCount }} {{ $quoteCount === 1 ? 'orcamento' : 'orcamentos' }}</span>
                        <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700">{{ $project->name }}</span>
                    </div>

                    <div class="mt-6 grid grid-cols-2 gap-4 xl:grid-cols-4">
                        <article class="rounded-2xl border border-slate-200 bg-slate-50 p-5 shadow-sm transition-colors hover:bg-white">
                            <span class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Potencia do sistema</span>
                            <p class="mt-3 text-2xl font-semibold text-slate-900" data-show-animate-number data-show-format="kwp" data-show-value="{{ $simulation->system_power_kwp ?: '' }}">
                                {{ $simulation->system_power_kwp ? number_format((float) $simulation->system_power_kwp, 2, ',', '.') . ' kWp' : '-' }}
                            </p>
                        </article>

                        <article class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm transition-colors hover:bg-amber-100/70">
                            <span class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">Preco sugerido</span>
                            <p class="mt-3 text-2xl font-semibold text-slate-900" data-show-animate-number data-show-format="currency" data-show-value="{{ $simulation->suggested_price ?: '' }}">
                                {{ $simulation->suggested_price ? 'R$ ' . number_format((float) $simulation->suggested_price, 2, ',', '.') : '-' }}
                            </p>
                        </article>

                        <article class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm transition-colors hover:bg-emerald-100/70">
                            <span class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">Economia mensal</span>
                            <p class="mt-3 text-2xl font-semibold text-slate-900" data-show-animate-number data-show-format="currency" data-show-value="{{ $simulation->estimated_monthly_savings ?: '' }}">
                                {{ $simulation->estimated_monthly_savings ? 'R$ ' . number_format((float) $simulation->estimated_monthly_savings, 2, ',', '.') : '-' }}
                            </p>
                        </article>

                        <article class="rounded-2xl border border-sky-200 bg-sky-50 p-5 shadow-sm transition-colors hover:bg-sky-100/70">
                            <span class="text-xs font-semibold uppercase tracking-[0.18em] text-sky-700">Payback estimado</span>
                            <p class="mt-3 text-2xl font-semibold text-slate-900" data-show-animate-number data-show-format="months" data-show-value="{{ $simulation->estimated_payback_months ?: '' }}">
                                {{ $simulation->estimated_payback_months ? $simulation->estimated_payback_months . ' meses' : '-' }}
                            </p>
                        </article>
                    </div>
                </div>

                <aside class="rounded-2xl border border-slate-200 bg-gradient-to-br from-slate-900 to-slate-800 p-5 text-white shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-300">Leitura comercial</p>
                    <h2 class="mt-3 text-xl font-semibold">
                        {{ $readyForProposal ? 'Cenario pronto para orcamento' : 'Revise a base antes de propor' }}
                    </h2>
                    <p class="mt-3 text-sm leading-6 text-slate-300">
                        {{ $readyForProposal ? 'A simulacao ja apresenta potencia e preco sugerido. O passo seguinte e gerar um orcamento com composicao real.' : 'Complete os dados do cenario para deixar a proposta mais consistente.' }}
                    </p>

                    @if ($latestQuote)
                        <a href="{{ route('solar.quotes.edit', $latestQuote->id) }}" class="mt-5 inline-flex items-center rounded-xl bg-white px-4 py-2.5 text-sm font-medium text-slate-900 transition-colors hover:bg-slate-100">
                            Abrir ultimo orcamento
                        </a>
                    @endif

                    <p class="mt-4 text-xs uppercase tracking-[0.18em] text-slate-400">
                        Compare cenarios antes de enviar ao cliente.
                    </p>
                </aside>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)]">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Sistema tecnico</p>
                        <h2 class="text-2xl font-semibold text-slate-900">Composicao sugerida do sistema</h2>
                    </div>
                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700" title="Origem do fator solar utilizado">{{ $solarSourceLabel }}</span>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4 transition-colors hover:bg-white">
                        <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Modulos</span>
                        <p class="mt-2 text-lg font-semibold text-slate-900">{{ $simulation->module_quantity ?: '-' }}</p>
                    </article>
                    <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4 transition-colors hover:bg-white">
                        <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Potencia do modulo</span>
                        <p class="mt-2 text-lg font-semibold text-slate-900">{{ $simulation->module_power ? number_format((int) $simulation->module_power, 0, ',', '.') . ' W' : '-' }}</p>
                    </article>
                    <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4 transition-colors hover:bg-white">
                        <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Quantidade</span>
                        <p class="mt-2 text-lg font-semibold text-slate-900">{{ $simulation->module_quantity ?: '-' }}</p>
                    </article>
                    <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4 transition-colors hover:bg-white">
                        <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Inversor</span>
                        <p class="mt-2 text-lg font-semibold text-slate-900">{{ $simulation->inverter_model ?: '-' }}</p>
                    </article>
                    <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4 transition-colors hover:bg-white">
                        <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Area estimada</span>
                        <p class="mt-2 text-lg font-semibold text-slate-900">{{ $simulation->area_estimated ? number_format((float) $simulation->area_estimated, 2, ',', '.') . ' m2' : '-' }}</p>
                    </article>
                    <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4 transition-colors hover:bg-white">
                        <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Geracao estimada</span>
                        <p class="mt-2 text-lg font-semibold text-slate-900">{{ $simulation->estimated_generation_kwh ? number_format((float) $simulation->estimated_generation_kwh, 2, ',', '.') . ' kWh/mes' : '-' }}</p>
                    </article>
                    <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4 transition-colors hover:bg-white">
                        <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Fator solar</span>
                        <p class="mt-2 text-lg font-semibold text-slate-900">{{ $simulation->solar_factor_used ? number_format((float) $simulation->solar_factor_used, 2, ',', '.') . ' kWh/kWp/mes' : '-' }}</p>
                    </article>
                    <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4 transition-colors hover:bg-white">
                        <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Radiacao equivalente</span>
                        <p class="mt-2 text-lg font-semibold text-slate-900">{{ $simulation->solar_factor_used ? number_format((float) $radiationDaily, 2, ',', '.') . ' kWh/m2/dia' : '-' }}</p>
                    </article>
                    <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4 transition-colors hover:bg-white">
                        <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Local base</span>
                        <p class="mt-2 text-lg font-semibold text-slate-900">{{ $projectAddress !== '' ? $projectAddress : 'Endereco pendente' }}</p>
                    </article>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Indicadores financeiros</p>
                    <h2 class="text-2xl font-semibold text-slate-900">Leitura comercial do cenario</h2>
                    <p class="mt-2 text-sm text-slate-600">Aqui ficam os numeros principais para defender a proposta e comparar alternativas.</p>
                </div>

                <div class="mt-6 space-y-3">
                    <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 transition-colors hover:bg-white">
                        <span class="text-sm font-medium text-slate-600">Economia mensal</span>
                        <strong class="text-lg font-semibold text-slate-900">{{ $simulation->estimated_monthly_savings ? 'R$ ' . number_format((float) $simulation->estimated_monthly_savings, 2, ',', '.') : '-' }}</strong>
                    </div>
                    <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 transition-colors hover:bg-white">
                        <span class="text-sm font-medium text-slate-600">Economia anual</span>
                        <strong class="text-lg font-semibold text-slate-900">{{ $simulation->estimated_annual_savings ? 'R$ ' . number_format((float) $simulation->estimated_annual_savings, 2, ',', '.') : '-' }}</strong>
                    </div>
                    <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 transition-colors hover:bg-white">
                        <span class="text-sm font-medium text-slate-600">ROI</span>
                        <strong class="text-lg font-semibold text-slate-900">{{ $simulation->estimated_roi ? number_format((float) $simulation->estimated_roi, 1, ',', '.') . '%' : '-' }}</strong>
                    </div>
                    <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 transition-colors hover:bg-white">
                        <span class="text-sm font-medium text-slate-600">Payback</span>
                        <strong class="text-lg font-semibold text-slate-900">{{ $simulation->estimated_payback_months ? $simulation->estimated_payback_months . ' meses' : '-' }}</strong>
                    </div>
                    <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 transition-colors hover:bg-white">
                        <span class="text-sm font-medium text-slate-600">Economia em 25 anos</span>
                        <strong class="text-lg font-semibold text-slate-900">{{ $simulation->estimated_lifetime_savings ? 'R$ ' . number_format((float) $simulation->estimated_lifetime_savings, 2, ',', '.') : '-' }}</strong>
                    </div>
                </div>

                <div class="mt-6 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-600">
                    {{ $quoteCount > 0 ? 'Esta simulacao ja possui proposta vinculada. Revise o orcamento mais recente para fechar composicao, margem e envio.' : 'Esta simulacao esta pronta para virar proposta. Gere um orcamento para montar materiais, servicos e fechamento comercial.' }}
                </div>
            </section>
        </div>
    </section>
@endsection
