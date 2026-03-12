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
    $typeOptions = [
        'material' => 'Material',
        'service' => 'Servico',
    ];
    $categoryOptions = [
        'module' => 'Modulo',
        'inverter' => 'Inversor',
        'structure' => 'Estrutura',
        'installation' => 'Instalacao',
        'cabling' => 'Cabeamento',
        'approval' => 'Homologacao',
        'electrical_design' => 'Projeto eletrico',
        'art' => 'ART',
        'other' => 'Outro',
    ];
    $itemsLockedPrice = $quoteSummary['item_count'] > 0;
    $statusLabel = $statusOptions[$quote->status] ?? strtoupper((string) $quote->status);
    $resolvedFinalPrice = $quoteSummary['item_count'] > 0 ? $quoteSummary['total_price'] : $quote->final_price;
@endphp

@section('solar-content')
    <section class="space-y-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Orcamento solar</p>
                <h1 class="text-3xl font-semibold text-slate-900">{{ $quote->title }}</h1>
                <p class="mt-2 max-w-3xl text-sm text-slate-600">
                    Esta tela organiza a proposta comercial com materiais, servicos, custo, preco final e margem.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="#adicionar-item" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-slate-800">
                    Adicionar item
                </a>

                <form action="{{ route('solar.quotes.duplicate', $quote->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
                        Duplicar orcamento
                    </button>
                </form>

                <form action="{{ route('solar.quotes.status.update', $quote->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="review">
                    <button type="submit" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
                        Gerar proposta
                    </button>
                </form>

                @if ($simulation)
                    <a href="{{ route('solar.simulations.show', $simulation->id) }}" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
                        Voltar a simulacao
                    </a>
                @endif
            </div>
        </div>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-6 lg:grid-cols-[minmax(0,1.5fr)_minmax(280px,0.9fr)]">
                <div>
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">{{ $statusLabel }}</span>
                        <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">{{ $quoteSummary['item_count'] }} {{ $quoteSummary['item_count'] === 1 ? 'item' : 'itens' }}</span>
                        <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700">{{ $simulation?->name ?: 'Proposta manual' }}</span>
                    </div>

                    <div class="mt-6 grid grid-cols-2 gap-4 xl:grid-cols-4">
                        <article class="rounded-2xl border border-slate-200 bg-slate-50 p-5 shadow-sm transition-colors hover:bg-white">
                            <span class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Custo total</span>
                            <p class="mt-3 text-2xl font-semibold text-slate-900">{{ $quoteSummary['item_count'] > 0 ? 'R$ ' . number_format((float) $quoteSummary['total_cost'], 2, ',', '.') : '-' }}</p>
                        </article>
                        <article class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm transition-colors hover:bg-amber-100/70">
                            <span class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">Preco final</span>
                            <p class="mt-3 text-2xl font-semibold text-slate-900">{{ $resolvedFinalPrice ? 'R$ ' . number_format((float) $resolvedFinalPrice, 2, ',', '.') : '-' }}</p>
                        </article>
                        <article class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm transition-colors hover:bg-emerald-100/70">
                            <span class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">Lucro bruto</span>
                            <p class="mt-3 text-2xl font-semibold text-slate-900">{{ $quoteSummary['item_count'] > 0 ? 'R$ ' . number_format((float) $quoteSummary['gross_profit'], 2, ',', '.') : '-' }}</p>
                        </article>
                        <article class="rounded-2xl border border-sky-200 bg-sky-50 p-5 shadow-sm transition-colors hover:bg-sky-100/70">
                            <span class="text-xs font-semibold uppercase tracking-[0.18em] text-sky-700">Margem</span>
                            <p class="mt-3 text-2xl font-semibold text-slate-900">{{ $quoteSummary['item_count'] > 0 ? number_format((float) $quoteSummary['margin_percent'], 2, ',', '.') . '%' : '-' }}</p>
                        </article>
                    </div>
                </div>

                <aside class="rounded-2xl border border-slate-200 bg-gradient-to-br from-slate-900 to-slate-800 p-5 text-white shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-300">Contexto comercial</p>
                    <h2 class="mt-3 text-xl font-semibold">{{ $customerName }}</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-300">
                        {{ $quote->status === 'sent' ? 'Proposta enviada. Agora o foco e acompanhar retorno e ajustar negociacao.' : 'Monte a composicao real do kit e avance o status quando a proposta estiver pronta.' }}
                    </p>
                    <p class="mt-4 text-xs uppercase tracking-[0.18em] text-slate-400">
                        Base: {{ $project?->name ?: 'Projeto nao vinculado' }}
                    </p>
                </aside>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Resumo da proposta</p>
                <h2 class="mt-2 text-2xl font-semibold text-slate-900">Dados comerciais e origem</h2>

                <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Projeto</span>
                        <p class="mt-2 text-base font-semibold text-slate-900">{{ $project?->name ?: '-' }}</p>
                    </article>
                    <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Cliente</span>
                        <p class="mt-2 text-base font-semibold text-slate-900">{{ $customerName }}</p>
                    </article>
                    <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Simulacao</span>
                        <p class="mt-2 text-base font-semibold text-slate-900">{{ $simulation?->name ?: 'Nao vinculada' }}</p>
                    </article>
                    <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Preco sugerido</span>
                        <p class="mt-2 text-base font-semibold text-slate-900">{{ $simulation?->suggested_price ? 'R$ ' . number_format((float) $simulation->suggested_price, 2, ',', '.') : '-' }}</p>
                    </article>
                    <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Potencia da simulacao</span>
                        <p class="mt-2 text-base font-semibold text-slate-900">{{ $simulation?->system_power_kwp ? number_format((float) $simulation->system_power_kwp, 2, ',', '.') . ' kWp' : '-' }}</p>
                    </article>
                    <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Payback estimado</span>
                        <p class="mt-2 text-base font-semibold text-slate-900">{{ $simulation?->estimated_payback_months ? $simulation->estimated_payback_months . ' meses' : '-' }}</p>
                    </article>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Edicao</p>
                <h2 class="mt-2 text-2xl font-semibold text-slate-900">Ajustes da proposta</h2>

                <form action="{{ route('solar.quotes.update', $quote->id) }}" method="POST" class="mt-6 space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700" for="quote-title">Titulo da proposta</label>
                        <input id="quote-title" name="title" type="text" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition-colors focus:border-slate-400" value="{{ old('title', $quote->title) }}" required>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700" for="quote-status">Status</label>
                        <select id="quote-status" name="status" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition-colors focus:border-slate-400" required>
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $quote->status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700" for="quote-final-price">Preco final</label>
                        <input id="quote-final-price" name="final_price" type="number" step="0.01" min="0" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition-colors focus:border-slate-400 disabled:bg-slate-100" value="{{ old('final_price', $quote->final_price) }}" @disabled($itemsLockedPrice)>
                        <p class="mt-2 text-xs leading-5 text-slate-500">
                            @if ($itemsLockedPrice)
                                O preco final esta sendo calculado automaticamente com base nos itens adicionados.
                            @else
                                Sem itens cadastrados, o valor final ainda pode ser informado manualmente.
                            @endif
                        </p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700" for="quote-notes">Observacoes</label>
                        <textarea id="quote-notes" name="notes" rows="7" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition-colors focus:border-slate-400">{{ old('notes', $quote->notes) }}</textarea>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-slate-800">
                            Salvar proposta
                        </button>
                    </div>
                </form>

                <form action="{{ route('solar.quotes.status.update', $quote->id) }}" method="POST" class="mt-3">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="sent">
                    <button type="submit" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50">
                        Marcar como enviado
                    </button>
                </form>
            </section>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(320px,0.9fr)_minmax(0,1.1fr)]">
            <section id="adicionar-item" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Adicionar item</p>
                <h2 class="mt-2 text-2xl font-semibold text-slate-900">Material ou servico</h2>

                <form action="{{ route('solar.quotes.items.store', $quote->id) }}" method="POST" class="mt-6 space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700" for="item-type">Tipo</label>
                            <select id="item-type" name="type" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition-colors focus:border-slate-400" required>
                                @foreach ($typeOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700" for="item-category">Categoria</label>
                            <select id="item-category" name="category" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition-colors focus:border-slate-400" required>
                                @foreach ($categoryOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700" for="item-name">Nome</label>
                        <input id="item-name" name="name" type="text" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition-colors focus:border-slate-400" value="{{ old('name') }}" required>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700" for="item-description">Descricao</label>
                        <textarea id="item-description" name="description" rows="3" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition-colors focus:border-slate-400">{{ old('description') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700" for="item-quantity">Quantidade</label>
                            <input id="item-quantity" name="quantity" type="number" step="0.01" min="0.01" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition-colors focus:border-slate-400" value="{{ old('quantity', '1') }}" required>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700" for="item-unit-cost">Custo unitario</label>
                            <input id="item-unit-cost" name="unit_cost" type="number" step="0.01" min="0" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition-colors focus:border-slate-400" value="{{ old('unit_cost', '0') }}" required>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700" for="item-unit-price">Preco unitario</label>
                            <input id="item-unit-price" name="unit_price" type="number" step="0.01" min="0" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition-colors focus:border-slate-400" value="{{ old('unit_price', '0') }}" required>
                        </div>
                    </div>

                    <button type="submit" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-slate-800">
                        Adicionar item
                    </button>
                </form>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Itens do orcamento</p>
                        <h2 class="text-2xl font-semibold text-slate-900">Composicao de materiais e servicos</h2>
                    </div>
                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">
                        {{ $quoteSummary['item_count'] }} {{ $quoteSummary['item_count'] === 1 ? 'item' : 'itens' }}
                    </span>
                </div>

                <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr class="text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                    <th class="px-4 py-3">Tipo</th>
                                    <th class="px-4 py-3">Categoria</th>
                                    <th class="px-4 py-3">Nome</th>
                                    <th class="px-4 py-3">Quantidade</th>
                                    <th class="px-4 py-3">Custo unitario</th>
                                    <th class="px-4 py-3">Preco unitario</th>
                                    <th class="px-4 py-3">Total</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                @forelse ($quote->items as $item)
                                    <tr class="transition-colors hover:bg-slate-50">
                                        <td class="px-4 py-4 text-slate-700">{{ $typeOptions[$item->type] ?? ucfirst((string) $item->type) }}</td>
                                        <td class="px-4 py-4 text-slate-700">{{ $categoryOptions[$item->category] ?? ucfirst((string) $item->category) }}</td>
                                        <td class="px-4 py-4">
                                            <div class="font-medium text-slate-900">{{ $item->name }}</div>
                                            @if ($item->description)
                                                <div class="mt-1 text-xs text-slate-500">{{ $item->description }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 text-slate-700">{{ number_format((float) $item->quantity, 2, ',', '.') }}</td>
                                        <td class="px-4 py-4 text-slate-700">R$ {{ number_format((float) $item->unit_cost, 2, ',', '.') }}</td>
                                        <td class="px-4 py-4 text-slate-700">R$ {{ number_format((float) $item->unit_price, 2, ',', '.') }}</td>
                                        <td class="px-4 py-4 font-semibold text-slate-900">R$ {{ number_format((float) $item->total_price, 2, ',', '.') }}</td>
                                        <td class="px-4 py-4 text-right">
                                            <form action="{{ route('solar.quotes.items.destroy', [$quote->id, $item->id]) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 transition-colors hover:bg-slate-50" onclick="return confirm('Remover este item do orcamento?');">
                                                    Remover
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-4 py-10 text-center">
                                            <p class="text-sm font-medium text-slate-900">Nenhum item adicionado ainda.</p>
                                            <p class="mt-2 text-sm text-slate-500">Monte a proposta com modulos, inversor, estrutura, cabeamento, instalacao e servicos complementares.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </section>
@endsection
