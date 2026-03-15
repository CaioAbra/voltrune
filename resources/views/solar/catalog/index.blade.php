@extends('solar.layout')

@section('title', 'Catalogo | Solar')

@php
    $statusOptions = [
        '' => 'Todos',
        'active' => 'Ativos',
        'inactive' => 'Inativos',
    ];
@endphp

@section('solar-content')
    <section class="solar-page-shell">
        <section class="hub-card hub-card--subtle solar-page-intro">
            <div class="solar-page-intro__header">
                <div class="solar-page-intro__copy">
                    <p class="solar-section-eyebrow">Catalogo</p>
                    <h2>Equipamentos e servicos da sua operacao</h2>
                    <p class="hub-note">Mantenha aqui os itens reais usados pela empresa para montar propostas com menos improviso e mais consistencia de custo.</p>
                </div>

                <div class="solar-page-intro__meta">
                    <span class="solar-project-showcase__status-label">Uso recomendado</span>
                    <strong>Preencha custo, venda e ativacao de cada item</strong>
                    <p>Os itens ativos podem entrar direto no orcamento e tambem servir de base para a composicao inicial das propostas.</p>
                </div>
            </div>
        </section>

        <section class="solar-page-grid solar-page-grid--cards">
            <article class="hub-card hub-card--subtle solar-quick-card">
                <span class="solar-quick-card__eyebrow">Total</span>
                <h3>{{ $summary['total'] }}</h3>
                <p>Itens cadastrados entre equipamentos e servicos.</p>
            </article>
            <article class="hub-card hub-card--subtle solar-quick-card">
                <span class="solar-quick-card__eyebrow">Ativos</span>
                <h3>{{ $summary['active'] }}</h3>
                <p>Itens prontos para entrar nas propostas sem novo cadastro.</p>
            </article>
            <article class="hub-card hub-card--subtle solar-quick-card">
                <span class="solar-quick-card__eyebrow">Materiais</span>
                <h3>{{ $summary['materials'] }}</h3>
                <p>Equipamentos e insumos usados no kit da instalacao.</p>
            </article>
            <article class="hub-card hub-card--subtle solar-quick-card">
                <span class="solar-quick-card__eyebrow">Servicos</span>
                <h3>{{ $summary['services'] }}</h3>
                <p>Mao de obra, homologacao, projeto e demais entregas comerciais.</p>
            </article>
        </section>

        <section class="hub-card solar-page-panel">
            <section class="hub-card hub-card--subtle solar-filter-panel">
                <div class="solar-page-panel__header">
                    <h2>Busca e filtros</h2>
                    <p class="hub-note">Pesquise por nome, marca, SKU ou fornecedor e filtre apenas os itens que importam para a proposta atual.</p>
                </div>

                <form method="get" action="{{ route('solar.catalog.index') }}" class="solar-filter-grid solar-filter-grid--wide">
                    <div>
                        <label for="catalog-q" class="hub-auth-label">Buscar item</label>
                        <input id="catalog-q" name="q" type="text" class="hub-auth-input" value="{{ $filters['q'] }}" placeholder="Nome, marca, SKU ou fornecedor">
                    </div>

                    <div>
                        <label for="catalog-type" class="hub-auth-label">Tipo</label>
                        <select id="catalog-type" name="type" class="hub-auth-input">
                            <option value="">Todos</option>
                            @foreach ($typeOptions as $value => $label)
                                <option value="{{ $value }}" @selected($filters['type'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="catalog-category" class="hub-auth-label">Categoria</label>
                        <select id="catalog-category" name="category" class="hub-auth-input">
                            <option value="">Todas</option>
                            @foreach ($categoryOptions as $value => $label)
                                <option value="{{ $value }}" @selected($filters['category'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="catalog-status" class="hub-auth-label">Status</label>
                        <select id="catalog-status" name="status" class="hub-auth-input">
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="catalog-sort" class="hub-auth-label">Ordenar por</label>
                        <select id="catalog-sort" name="sort" class="hub-auth-input">
                            @foreach ($sortOptions as $value => $label)
                                <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="solar-filter-actions">
                        <button type="submit" class="hub-btn">Aplicar filtros</button>
                        <a href="{{ route('solar.catalog.index') }}" class="hub-btn hub-btn--subtle">Limpar</a>
                    </div>
                </form>

                <p class="solar-filter-summary">
                    {{ $summary['filtered'] }} {{ $summary['filtered'] === 1 ? 'item encontrado' : 'itens encontrados' }}
                    @if ($hasActiveFilters)
                        com os filtros atuais.
                    @else
                        no catalogo operacional da empresa.
                    @endif
                </p>
            </section>

            @if (session('solar_status'))
                <div
                    class="hub-alert hub-alert--success solar-flash-alert"
                    data-flash-alert
                    data-flash-timeout="5000"
                    role="status"
                    aria-live="polite"
                >
                    <div class="solar-flash-alert__content">
                        {{ session('solar_status') }}
                    </div>
                    <button type="button" class="solar-flash-alert__close" data-flash-close aria-label="Fechar aviso">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="hub-grid solar-project-show__grid solar-project-show__grid--quote-summary">
                <section class="hub-card hub-card--subtle solar-flow-section">
                    <div class="solar-flow-section__header solar-flow-section__header--stacked-md">
                        <div>
                            <p class="solar-section-eyebrow">Novo item</p>
                            <h2>Cadastrar no catalogo</h2>
                            <p class="hub-note">Cadastre apenas o que faz sentido repetir com frequencia no dia a dia comercial.</p>
                        </div>
                    </div>

                    <form action="{{ route('solar.catalog.store') }}" method="POST" class="hub-auth-form">
                        @csrf

                        <div class="hub-grid solar-quote-item-form__grid solar-quote-item-form__grid--duo">
                            <div>
                                <label class="hub-auth-label" for="catalog-type-create">Tipo</label>
                                <select id="catalog-type-create" name="type" class="hub-auth-input" required>
                                    @foreach ($typeOptions as $value => $label)
                                        <option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="hub-auth-label" for="catalog-category-create">Categoria</label>
                                <select id="catalog-category-create" name="category" class="hub-auth-input" required>
                                    @foreach ($categoryOptions as $value => $label)
                                        <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="hub-grid solar-quote-item-form__grid solar-quote-item-form__grid--duo">
                            <div>
                                <label class="hub-auth-label" for="catalog-name-create">Nome</label>
                                <input id="catalog-name-create" name="name" type="text" class="hub-auth-input" value="{{ old('name') }}" required>
                            </div>

                            <div>
                                <label class="hub-auth-label" for="catalog-brand-create">Marca</label>
                                <input id="catalog-brand-create" name="brand" type="text" class="hub-auth-input" value="{{ old('brand') }}">
                            </div>
                        </div>

                        <div class="hub-grid solar-quote-item-form__grid solar-quote-item-form__grid--duo">
                            <div>
                                <label class="hub-auth-label" for="catalog-sku-create">SKU</label>
                                <input id="catalog-sku-create" name="sku" type="text" class="hub-auth-input" value="{{ old('sku') }}">
                            </div>

                            <div>
                                <label class="hub-auth-label" for="catalog-supplier-create">Fornecedor</label>
                                <input id="catalog-supplier-create" name="supplier" type="text" class="hub-auth-input" value="{{ old('supplier') }}">
                            </div>
                        </div>

                        <div class="hub-grid solar-quote-item-form__grid solar-quote-item-form__grid--metrics">
                            <div>
                                <label class="hub-auth-label" for="catalog-unit-create">Unidade</label>
                                <input id="catalog-unit-create" name="unit_label" type="text" class="hub-auth-input" value="{{ old('unit_label', 'un') }}">
                            </div>

                            <div>
                                <label class="hub-auth-label" for="catalog-quantity-create">Quantidade base</label>
                                <input id="catalog-quantity-create" name="default_quantity" type="number" step="0.01" min="0.01" class="hub-auth-input" value="{{ old('default_quantity', '1') }}" required>
                            </div>

                            <div>
                                <label class="hub-auth-label" for="catalog-cost-create">Custo base</label>
                                <input id="catalog-cost-create" name="default_cost" type="number" step="0.01" min="0" class="hub-auth-input" value="{{ old('default_cost', '0') }}" required>
                            </div>

                            <div>
                                <label class="hub-auth-label" for="catalog-price-create">Preco base</label>
                                <input id="catalog-price-create" name="default_price" type="number" step="0.01" min="0" class="hub-auth-input" value="{{ old('default_price', '0') }}" required>
                            </div>
                        </div>

                        <div>
                            <label class="hub-auth-label" for="catalog-notes-create">Observacoes</label>
                            <textarea id="catalog-notes-create" name="notes" rows="4" class="hub-auth-input">{{ old('notes') }}</textarea>
                        </div>

                        <label class="solar-settings-range-check">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                            <span>Deixar este item ativo para propostas</span>
                        </label>

                        <div class="hub-actions">
                            <button type="submit" class="hub-btn">Salvar no catalogo</button>
                        </div>
                    </form>
                </section>

                <section class="hub-card hub-card--subtle solar-flow-section solar-catalog-panel">
                    <div class="solar-flow-section__header solar-flow-section__header--stacked-md">
                        <div>
                            <p class="solar-section-eyebrow">Operacao</p>
                            <h2>Itens cadastrados</h2>
                            <p class="hub-note">Cada card pode ser ajustado rapidamente para manter custo, venda e status atualizados.</p>
                        </div>
                        <div class="solar-project-showcase__status is-ready">
                            <span class="solar-project-showcase__status-label">Catalogo ativo</span>
                            <strong>{{ $summary['active'] }} {{ $summary['active'] === 1 ? 'item ativo' : 'itens ativos' }}</strong>
                            <p>Itens ativos aparecem como base pronta para o orcamento comercial.</p>
                        </div>
                    </div>

                    @if ($items->isEmpty())
                        <p class="solar-field-note">
                            {{ $hasActiveFilters
                                ? 'Os filtros atuais nao retornaram itens. Ajuste os criterios para recuperar o catalogo desejado.'
                                : 'Nenhum item cadastrado ainda. Comece pelos equipamentos e servicos mais usados na rotina comercial.' }}
                        </p>
                    @else
                        <div class="solar-catalog-grid">
                            @foreach ($items as $item)
                                <article class="solar-catalog-card {{ $item->is_active ? 'is-active' : 'is-inactive' }}">
                                    <div class="solar-catalog-card__header">
                                        <div>
                                            <span class="solar-project-simulation-card__eyebrow">{{ $typeOptions[$item->type] ?? ucfirst((string) $item->type) }}</span>
                                            <h3>{{ $item->name }}</h3>
                                        </div>
                                        <div class="solar-project-simulation-card__chips">
                                            <span class="solar-mini-badge {{ $item->is_active ? 'solar-mini-badge--automatic' : '' }}">{{ $item->is_active ? 'Ativo' : 'Inativo' }}</span>
                                            <span class="solar-mini-badge">{{ $categoryOptions[$item->category] ?? ucfirst((string) $item->category) }}</span>
                                        </div>
                                    </div>

                                    <form action="{{ route('solar.catalog.update', $item->id) }}" method="POST" class="hub-auth-form solar-catalog-card__form">
                                        @csrf
                                        @method('PUT')

                                        <div class="hub-grid solar-quote-item-form__grid solar-quote-item-form__grid--duo">
                                            <div>
                                                <label class="hub-auth-label" for="catalog-name-{{ $item->id }}">Nome</label>
                                                <input id="catalog-name-{{ $item->id }}" name="name" type="text" class="hub-auth-input" value="{{ old('name', $item->name) }}" required>
                                            </div>

                                            <div>
                                                <label class="hub-auth-label" for="catalog-brand-{{ $item->id }}">Marca</label>
                                                <input id="catalog-brand-{{ $item->id }}" name="brand" type="text" class="hub-auth-input" value="{{ old('brand', $item->brand) }}">
                                            </div>
                                        </div>

                                        <div class="hub-grid solar-quote-item-form__grid solar-quote-item-form__grid--duo">
                                            <div>
                                                <label class="hub-auth-label" for="catalog-sku-{{ $item->id }}">SKU</label>
                                                <input id="catalog-sku-{{ $item->id }}" name="sku" type="text" class="hub-auth-input" value="{{ old('sku', $item->sku) }}">
                                            </div>

                                            <div>
                                                <label class="hub-auth-label" for="catalog-supplier-{{ $item->id }}">Fornecedor</label>
                                                <input id="catalog-supplier-{{ $item->id }}" name="supplier" type="text" class="hub-auth-input" value="{{ old('supplier', $item->supplier) }}">
                                            </div>
                                        </div>

                                        <div class="hub-grid solar-quote-item-form__grid solar-quote-item-form__grid--metrics">
                                            <div>
                                                <label class="hub-auth-label" for="catalog-type-{{ $item->id }}">Tipo</label>
                                                <select id="catalog-type-{{ $item->id }}" name="type" class="hub-auth-input" required>
                                                    @foreach ($typeOptions as $value => $label)
                                                        <option value="{{ $value }}" @selected($item->type === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div>
                                                <label class="hub-auth-label" for="catalog-category-{{ $item->id }}">Categoria</label>
                                                <select id="catalog-category-{{ $item->id }}" name="category" class="hub-auth-input" required>
                                                    @foreach ($categoryOptions as $value => $label)
                                                        <option value="{{ $value }}" @selected($item->category === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div>
                                                <label class="hub-auth-label" for="catalog-unit-{{ $item->id }}">Unidade</label>
                                                <input id="catalog-unit-{{ $item->id }}" name="unit_label" type="text" class="hub-auth-input" value="{{ $item->unit_label }}">
                                            </div>

                                            <div>
                                                <label class="hub-auth-label" for="catalog-quantity-{{ $item->id }}">Quantidade base</label>
                                                <input id="catalog-quantity-{{ $item->id }}" name="default_quantity" type="number" step="0.01" min="0.01" class="hub-auth-input" value="{{ $item->default_quantity }}" required>
                                            </div>

                                            <div>
                                                <label class="hub-auth-label" for="catalog-cost-{{ $item->id }}">Custo base</label>
                                                <input id="catalog-cost-{{ $item->id }}" name="default_cost" type="number" step="0.01" min="0" class="hub-auth-input" value="{{ $item->default_cost }}" required>
                                            </div>

                                            <div>
                                                <label class="hub-auth-label" for="catalog-price-{{ $item->id }}">Preco base</label>
                                                <input id="catalog-price-{{ $item->id }}" name="default_price" type="number" step="0.01" min="0" class="hub-auth-input" value="{{ $item->default_price }}" required>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="hub-auth-label" for="catalog-notes-{{ $item->id }}">Observacoes</label>
                                            <textarea id="catalog-notes-{{ $item->id }}" name="notes" rows="3" class="hub-auth-input">{{ $item->notes }}</textarea>
                                        </div>

                                        <label class="solar-settings-range-check">
                                            <input type="checkbox" name="is_active" value="1" @checked($item->is_active)>
                                            <span>Manter ativo para propostas</span>
                                        </label>

                                        <div class="solar-catalog-card__footer">
                                            <button type="submit" class="hub-btn">Salvar item</button>
                                        </div>
                                    </form>

                                    <div class="solar-catalog-card__footer solar-catalog-card__footer--secondary">
                                        <form action="{{ route('solar.catalog.destroy', $item->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="hub-btn hub-btn--subtle" onclick="return confirm('Remover este item do catalogo?');">Remover</button>
                                        </form>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>
        </section>
    </section>
@endsection
