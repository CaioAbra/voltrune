@extends('solar.layout')

@section('title', 'Editar orcamento | Voltrune Solar')

@php
    $customerName = $project?->customer?->name ?: '-';
    $statusOptions = [
        'draft' => 'Em montagem',
        'review' => 'Em revisao interna',
        'sent' => 'Enviado ao cliente',
        'approved' => 'Aprovada',
        'won' => 'Fechada',
        'lost' => 'Perdida',
    ];
    $typeOptions = [
        'material' => 'Material',
        'service' => 'Servico',
    ];
    $temperatureOptions = [
        'cold' => 'Fria',
        'warm' => 'Morna',
        'hot' => 'Quente',
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
    $simulationSnapshot = is_array($quote->simulation_snapshot_json) ? $quote->simulation_snapshot_json : [];
    $versionLabel = 'V' . str_pad((string) max((int) ($quote->version_number ?: 1), 1), 2, '0', STR_PAD_LEFT);
    $proposalCode = $quote->proposal_code ?: 'Codigo em preparacao';
    $proposalVersions = $proposalVersions->values();
    $latestVersion = $proposalVersions->sortByDesc('version_number')->first();
    $catalogItems = $catalogItems->values();
    $timelineMoments = collect([
        ['label' => 'Enviado', 'at' => $quote->sent_at, 'tone' => 'market'],
        ['label' => 'Aprovado', 'at' => $quote->approved_at, 'tone' => 'ready'],
        ['label' => 'Fechado', 'at' => $quote->won_at, 'tone' => 'ready'],
        ['label' => 'Perdido', 'at' => $quote->lost_at, 'tone' => 'warning'],
    ])->filter(fn (array $moment) => $moment['at'])->values();
    $historyEntries = $quote->events->take(8);
@endphp

@section('solar-content')
    <section class="hub-card solar-project-show solar-project-shell">
        <div class="hub-actions solar-project-show__actions">
            <a href="#adicionar-item" class="hub-btn">Adicionar item</a>
            <a href="{{ route('solar.quotes.proposal', $quote->id) }}" class="hub-btn hub-btn--subtle">Abrir proposta</a>

            <form action="{{ route('solar.quotes.duplicate', $quote->id) }}" method="POST">
                @csrf
                <button type="submit" class="hub-btn hub-btn--subtle">Nova versao</button>
            </form>

            <form action="{{ route('solar.quotes.status.update', $quote->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="review">
                <button type="submit" class="hub-btn hub-btn--subtle">Enviar para revisao</button>
            </form>

            @if ($simulation)
                <a href="{{ route('solar.simulations.show', $simulation->id) }}" class="hub-btn hub-btn--subtle">Voltar a simulacao</a>
            @endif
        </div>

        <section class="hub-card hub-card--subtle solar-project-showcase solar-quote-editor-hero">
            <div class="solar-project-showcase__header">
                <div>
                    <p class="solar-section-eyebrow">Orcamento solar</p>
                    <h2>{{ $quote->title }}</h2>
                    <p class="hub-note">
                        Esta tela organiza a versao comercial do orcamento: composicao real, historico, status e uma proposta pronta para compartilhar.
                    </p>

                    <div class="solar-project-showcase__chips">
                        <span class="solar-mini-badge solar-mini-badge--editable">{{ $statusLabel }}</span>
                        <span class="solar-mini-badge solar-mini-badge--automatic">{{ $versionLabel }}</span>
                        <span class="solar-mini-badge">{{ $proposalCode }}</span>
                        <span class="solar-mini-badge">{{ $quoteSummary['item_count'] }} {{ $quoteSummary['item_count'] === 1 ? 'item' : 'itens' }}</span>
                    </div>
                </div>

                <div class="solar-project-showcase__status is-market">
                    <span class="solar-project-showcase__status-label">Atendimento</span>
                    <strong>{{ $customerName }}</strong>
                        <p>{{ $quote->status === 'sent' ? 'A proposta ja saiu para o cliente. Agora o foco e acompanhar retorno e ajustar negociacao.' : 'Monte a composicao real e avance o status quando a leitura comercial estiver pronta.' }}</p>
                        <p>Base: {{ $project?->name ?: 'Projeto nao vinculado' }}</p>
                    </div>
            </div>

            <div class="solar-project-showcase__hero-grid">
                <article class="solar-project-showcase-metric">
                    <span class="solar-project-showcase-metric__label">Custo total</span>
                    <strong class="solar-project-showcase-metric__value">{{ $quoteSummary['item_count'] > 0 ? 'R$ ' . number_format((float) $quoteSummary['total_cost'], 2, ',', '.') : '-' }}</strong>
                </article>
                <article class="solar-project-showcase-metric solar-project-showcase-metric--highlight">
                    <span class="solar-project-showcase-metric__label">Preco final</span>
                    <strong class="solar-project-showcase-metric__value">{{ $resolvedFinalPrice ? 'R$ ' . number_format((float) $resolvedFinalPrice, 2, ',', '.') : '-' }}</strong>
                </article>
                <article class="solar-project-showcase-metric solar-project-showcase-metric--commercial">
                    <span class="solar-project-showcase-metric__label">Lucro bruto</span>
                    <strong class="solar-project-showcase-metric__value">{{ $quoteSummary['item_count'] > 0 ? 'R$ ' . number_format((float) $quoteSummary['gross_profit'], 2, ',', '.') : '-' }}</strong>
                </article>
                <article class="solar-project-showcase-metric">
                    <span class="solar-project-showcase-metric__label">Margem</span>
                    <strong class="solar-project-showcase-metric__value">{{ $quoteSummary['item_count'] > 0 ? number_format((float) $quoteSummary['margin_percent'], 2, ',', '.') . '%' : '-' }}</strong>
                </article>
            </div>
        </section>

        <div class="hub-grid solar-project-show__grid solar-project-show__grid--quote-summary">
            <section class="hub-card hub-card--subtle solar-project-show__card">
                <p class="solar-section-eyebrow">Resumo comercial</p>
                <h2>Dados da versao atual</h2>

                <div class="solar-project-show__info-grid">
                    <p><strong>Cliente</strong><span>{{ $customerName }}</span></p>
                    <p><strong>Projeto</strong><span>{{ $project?->name ?: '-' }}</span></p>
                    <p><strong>Simulacao base</strong><span>{{ $simulationSnapshot['name'] ?? $simulation?->name ?: 'Nao vinculada' }}</span></p>
                    <p><strong>Codigo comercial</strong><span>{{ $proposalCode }}</span></p>
                    <p><strong>Versao</strong><span>{{ $versionLabel }}</span></p>
                    <p><strong>Responsavel</strong><span>{{ $quote->owner_name ?: 'Nao definido' }}</span></p>
                    <p><strong>Proximo contato</strong><span>{{ $quote->next_contact_at?->format('d/m/Y H:i') ?: '-' }}</span></p>
                    <p><strong>Previsao de fechamento</strong><span>{{ $quote->closing_forecast_at?->format('d/m/Y H:i') ?: '-' }}</span></p>
                    <p><strong>Temperatura</strong><span>{{ $temperatureOptions[$quote->deal_temperature ?? 'warm'] ?? 'Morna' }}</span></p>
                    <p><strong>Preco sugerido</strong><span>{{ ($simulationSnapshot['suggested_price'] ?? $simulation?->suggested_price) ? 'R$ ' . number_format((float) ($simulationSnapshot['suggested_price'] ?? $simulation?->suggested_price), 2, ',', '.') : '-' }}</span></p>
                    <p><strong>Potencia da simulacao</strong><span>{{ ($simulationSnapshot['system_power_kwp'] ?? $simulation?->system_power_kwp) ? number_format((float) ($simulationSnapshot['system_power_kwp'] ?? $simulation?->system_power_kwp), 2, ',', '.') . ' kWp' : '-' }}</span></p>
                    <p><strong>Payback estimado</strong><span>{{ ($simulationSnapshot['estimated_payback_months'] ?? $simulation?->estimated_payback_months) ? ($simulationSnapshot['estimated_payback_months'] ?? $simulation?->estimated_payback_months) . ' meses' : '-' }}</span></p>
                </div>
            </section>

            <section class="hub-card hub-card--subtle solar-flow-section solar-proposal-panel">
                <div class="solar-flow-section__header solar-flow-section__header--stacked-md">
                    <div>
                        <p class="solar-section-eyebrow">Fluxo da proposta</p>
                        <h2>Versao, envio e historico</h2>
                        <p class="hub-note">Aqui voce acompanha o que ja foi enviado, quais versoes existem e acessa a pagina pronta para impressao ou PDF.</p>
                    </div>
                    <div class="solar-project-showcase__status {{ $latestVersion && $latestVersion->id === $quote->id ? 'is-ready' : 'is-market' }}">
                        <span class="solar-project-showcase__status-label">Situacao da versao</span>
                        <strong>{{ $latestVersion && $latestVersion->id === $quote->id ? 'Esta e a versao mais recente' : 'Ja existe versao mais nova' }}</strong>
                        <p>{{ $latestVersion && $latestVersion->id === $quote->id ? 'Use esta versao para revisar composicao, enviar ao cliente e acompanhar o fechamento.' : 'Revise se a negociacao deve seguir nesta versao ou migrar para a revisao mais recente.' }}</p>
                    </div>
                </div>

                <div class="solar-proposal-panel__actions">
                    <a href="{{ route('solar.quotes.proposal', $quote->id) }}" class="hub-btn">Abrir proposta</a>

                    <form action="{{ route('solar.quotes.status.update', $quote->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="sent">
                        <button type="submit" class="hub-btn hub-btn--subtle">Marcar como enviado</button>
                    </form>
                </div>

                @if ($timelineMoments->isNotEmpty())
                    <div class="solar-proposal-timeline">
                        @foreach ($timelineMoments as $moment)
                            <article class="solar-proposal-timeline__item is-{{ $moment['tone'] }}">
                                <span>{{ $moment['label'] }}</span>
                                <strong>{{ $moment['at']->format('d/m/Y H:i') }}</strong>
                            </article>
                        @endforeach
                    </div>
                @else
                    <p class="solar-field-note">Nenhum marco comercial registrado ainda. O historico sera preenchido conforme esta versao avancar.</p>
                @endif

                <div class="solar-proposal-versions">
                    @foreach ($proposalVersions as $version)
                        <a href="{{ route('solar.quotes.edit', $version->id) }}" class="solar-proposal-version {{ $version->id === $quote->id ? 'is-current' : '' }}">
                            <span class="solar-proposal-version__label">Versao {{ str_pad((string) max((int) ($version->version_number ?: 1), 1), 2, '0', STR_PAD_LEFT) }}</span>
                            <strong>{{ $version->proposal_code ?: 'Codigo pendente' }}</strong>
                            <small>{{ $statusOptions[$version->status] ?? strtoupper((string) $version->status) }}</small>
                        </a>
                    @endforeach
                </div>
            </section>
        </div>

        <div class="hub-grid solar-project-show__grid solar-project-show__grid--quote-summary">
            <section class="hub-card hub-card--subtle solar-flow-section">
                <div class="solar-flow-section__header solar-flow-section__header--stacked-md">
                    <div>
                        <p class="solar-section-eyebrow">Edicao</p>
                        <h2>Ajustes do orcamento</h2>
                        <p class="hub-note">O preco final fica automatico quando ha itens cadastrados. Sem itens, o valor ainda pode ser informado manualmente.</p>
                    </div>
                </div>

                <form action="{{ route('solar.quotes.update', $quote->id) }}" method="POST" class="hub-auth-form">
                    @csrf
                    @method('PUT')

                    <div class="hub-grid solar-quote-item-form__grid solar-quote-item-form__grid--duo">
                        <div>
                            <label class="hub-auth-label" for="quote-title">Titulo do orcamento</label>
                            <input id="quote-title" name="title" type="text" class="hub-auth-input" value="{{ old('title', $quote->title) }}" required>
                        </div>

                        <div>
                            <label class="hub-auth-label" for="quote-status">Status</label>
                            <select id="quote-status" name="status" class="hub-auth-input" required>
                                @foreach ($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', $quote->status) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="hub-grid solar-quote-item-form__grid solar-quote-item-form__grid--duo">
                        <div>
                            <label class="hub-auth-label" for="quote-owner-name">Responsavel</label>
                            <input id="quote-owner-name" name="owner_name" type="text" class="hub-auth-input" value="{{ old('owner_name', $quote->owner_name) }}" placeholder="Quem conduz esta negociacao">
                        </div>

                        <div>
                            <label class="hub-auth-label" for="quote-temperature">Temperatura</label>
                            <select id="quote-temperature" name="deal_temperature" class="hub-auth-input" required>
                                @foreach ($temperatureOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('deal_temperature', $quote->deal_temperature ?: 'warm') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="hub-grid solar-quote-item-form__grid solar-quote-item-form__grid--duo">
                        <div>
                            <label class="hub-auth-label" for="quote-next-contact">Proximo contato</label>
                            <input id="quote-next-contact" name="next_contact_at" type="datetime-local" class="hub-auth-input" value="{{ old('next_contact_at', $quote->next_contact_at?->format('Y-m-d\\TH:i')) }}">
                        </div>

                        <div>
                            <label class="hub-auth-label" for="quote-closing-forecast">Previsao de fechamento</label>
                            <input id="quote-closing-forecast" name="closing_forecast_at" type="datetime-local" class="hub-auth-input" value="{{ old('closing_forecast_at', $quote->closing_forecast_at?->format('Y-m-d\\TH:i')) }}">
                        </div>
                    </div>

                    <div>
                        <label class="hub-auth-label" for="quote-final-price">Preco final</label>
                        <input id="quote-final-price" name="final_price" type="number" step="0.01" min="0" class="hub-auth-input" value="{{ old('final_price', $quote->final_price) }}" @disabled($itemsLockedPrice)>
                        <p class="solar-field-note">
                            @if ($itemsLockedPrice)
                                O preco final esta sendo calculado automaticamente com base nos itens adicionados.
                            @else
                                Sem itens cadastrados, o valor final ainda pode ser informado manualmente.
                            @endif
                        </p>
                    </div>

                    <div>
                        <label class="hub-auth-label" for="quote-notes">Observacoes</label>
                        <textarea id="quote-notes" name="notes" rows="7" class="hub-auth-input">{{ old('notes', $quote->notes) }}</textarea>
                    </div>

                    <div class="hub-actions">
                        <button type="submit" class="hub-btn">Salvar orcamento</button>
                    </div>
                </form>
            </section>

            <section class="hub-card hub-card--subtle solar-flow-section solar-proposal-history">
                <div class="solar-flow-section__header solar-flow-section__header--stacked-md">
                    <div>
                        <p class="solar-section-eyebrow">Historico</p>
                        <h2>O que aconteceu nesta versao</h2>
                        <p class="hub-note">Cada mudanca relevante da proposta fica registrada aqui para dar contexto comercial e evitar retrabalho.</p>
                    </div>
                    <div class="solar-project-showcase__status is-ready">
                        <span class="solar-project-showcase__status-label">Eventos registrados</span>
                        <strong>{{ $historyEntries->count() }}</strong>
                        <p>O historico acompanha criacao, versoes, itens e mudancas de status.</p>
                    </div>
                </div>

                @if ($historyEntries->isNotEmpty())
                    <div class="solar-proposal-history__list">
                        @foreach ($historyEntries as $event)
                            <article class="solar-proposal-history__item">
                                <div>
                                    <strong>{{ $event->title }}</strong>
                                    @if ($event->description)
                                        <p>{{ $event->description }}</p>
                                    @endif
                                </div>
                                <span>{{ $event->created_at?->format('d/m/Y H:i') ?: '-' }}</span>
                            </article>
                        @endforeach
                    </div>
                @else
                    <p class="solar-field-note">Ainda nao ha historico registrado para esta versao.</p>
                @endif
            </section>
        </div>

        <div class="hub-grid solar-project-show__grid solar-project-show__grid--quote-composer">
            <section class="hub-card hub-card--subtle solar-flow-section solar-catalog-picker">
                <div class="solar-flow-section__header solar-flow-section__header--stacked-md">
                    <div>
                        <p class="solar-section-eyebrow">Catalogo</p>
                        <h2>Puxar item do catalogo</h2>
                        <p class="hub-note">Use o catalogo da empresa para adicionar item real com custo e venda padronizados antes de editar manualmente.</p>
                    </div>
                    <div class="solar-project-showcase__status {{ $catalogItems->isNotEmpty() ? 'is-ready' : 'is-market' }}">
                        <span class="solar-project-showcase__status-label">Base da empresa</span>
                        <strong>{{ $catalogItems->count() }} {{ $catalogItems->count() === 1 ? 'item ativo' : 'itens ativos' }}</strong>
                        <p>{{ $catalogItems->isNotEmpty() ? 'Selecione um item e ajuste apenas a quantidade para ganhar velocidade no orcamento.' : 'Cadastre itens no catalogo para acelerar a composicao das proximas propostas.' }}</p>
                    </div>
                </div>

                @if ($catalogItems->isNotEmpty())
                    <form action="{{ route('solar.quotes.items.store', $quote->id) }}" method="POST" class="hub-auth-form">
                        @csrf

                        <div class="hub-grid solar-quote-item-form__grid solar-quote-item-form__grid--duo">
                            <div>
                                <label class="hub-auth-label" for="catalog-item-id">Item do catalogo</label>
                                <select id="catalog-item-id" name="catalog_item_id" class="hub-auth-input" required>
                                    <option value="">Selecione um item</option>
                                    @foreach ($catalogItems as $catalogItem)
                                        <option value="{{ $catalogItem->id }}">
                                            {{ $catalogItem->name }} | {{ $categoryOptions[$catalogItem->category] ?? ucfirst((string) $catalogItem->category) }} | R$ {{ number_format((float) $catalogItem->default_price, 2, ',', '.') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="hub-auth-label" for="catalog-item-quantity">Quantidade</label>
                                <input id="catalog-item-quantity" name="quantity" type="number" step="0.01" min="0.01" class="hub-auth-input" value="1" required>
                            </div>
                        </div>

                        <div class="hub-actions">
                            <button type="submit" class="hub-btn">Usar catalogo</button>
                            <a href="{{ route('solar.catalog.index') }}" class="hub-btn hub-btn--subtle">Gerenciar catalogo</a>
                        </div>
                    </form>
                @else
                    <div class="hub-actions">
                        <a href="{{ route('solar.catalog.index') }}" class="hub-btn">Abrir catalogo</a>
                    </div>
                @endif
            </section>

            <section id="adicionar-item" class="hub-card hub-card--subtle solar-flow-section solar-pricing-panel solar-quote-item-panel">
                <div class="solar-flow-section__header solar-quote-item-panel__header">
                    <div class="solar-quote-item-panel__lead">
                        <p class="solar-section-eyebrow">Adicionar item</p>
                        <div class="solar-quote-item-panel__intro">
                            <h2 class="solar-quote-item-panel__title">Material ou servico</h2>

                            <div class="solar-quote-item-panel__support">
                                <p class="hub-note">Monte o orcamento com modulos, inversor, estrutura, cabeamento, instalacao e servicos complementares.</p>
                                <div class="solar-quote-item-panel__tags">
                                    <span class="solar-mini-badge solar-mini-badge--automatic">Kit</span>
                                    <span class="solar-mini-badge">Servico</span>
                                    <span class="solar-mini-badge">Custo e venda</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('solar.quotes.items.store', $quote->id) }}" method="POST" class="hub-auth-form solar-quote-item-form">
                    @csrf

                    <div class="hub-grid solar-quote-item-form__grid solar-quote-item-form__grid--duo">
                        <div>
                            <label class="hub-auth-label" for="item-type">Tipo</label>
                            <select id="item-type" name="type" class="hub-auth-input" required>
                                @foreach ($typeOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="hub-auth-label" for="item-category">Categoria</label>
                            <select id="item-category" name="category" class="hub-auth-input" required>
                                @foreach ($categoryOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="hub-auth-label" for="item-name">Nome</label>
                        <input id="item-name" name="name" type="text" class="hub-auth-input" value="{{ old('name') }}" required>
                    </div>

                    <div>
                        <label class="hub-auth-label" for="item-description">Descricao</label>
                        <textarea id="item-description" name="description" rows="3" class="hub-auth-input">{{ old('description') }}</textarea>
                    </div>

                    <div class="hub-grid solar-quote-item-form__grid solar-quote-item-form__grid--metrics">
                        <div>
                            <label class="hub-auth-label" for="item-quantity">Quantidade</label>
                            <input id="item-quantity" name="quantity" type="number" step="0.01" min="0.01" class="hub-auth-input" value="{{ old('quantity', '1') }}" required>
                        </div>
                        <div>
                            <label class="hub-auth-label" for="item-unit-cost">Custo unitario</label>
                            <input id="item-unit-cost" name="unit_cost" type="number" step="0.01" min="0" class="hub-auth-input" value="{{ old('unit_cost', '0') }}" required>
                        </div>
                        <div>
                            <label class="hub-auth-label" for="item-unit-price">Preco unitario</label>
                            <input id="item-unit-price" name="unit_price" type="number" step="0.01" min="0" class="hub-auth-input" value="{{ old('unit_price', '0') }}" required>
                        </div>
                    </div>

                    <div class="hub-actions">
                        <button type="submit" class="hub-btn">Adicionar item</button>
                    </div>
                </form>
            </section>

            <section class="hub-card hub-card--subtle solar-project-show__card">
                <div class="solar-flow-section__header solar-flow-section__header--stacked-md">
                    <div>
                        <p class="solar-section-eyebrow">Itens do orcamento</p>
                        <h2>Composicao de materiais e servicos</h2>
                    </div>
                    <div class="solar-project-showcase__status is-ready">
                        <span class="solar-project-showcase__status-label">Itens cadastrados</span>
                        <strong>{{ $quoteSummary['item_count'] }} {{ $quoteSummary['item_count'] === 1 ? 'item' : 'itens' }}</strong>
                        <p>O total do orcamento e recalculado a partir da composicao adicionada.</p>
                    </div>
                </div>

                <div class="solar-table-wrap">
                    <table class="hub-table solar-table solar-table--quote-items">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Categoria</th>
                                <th>Nome</th>
                                <th>Quantidade</th>
                                <th>Custo unitario</th>
                                <th>Preco unitario</th>
                                <th>Total</th>
                                <th>Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($quote->items as $item)
                                <tr class="solar-table__row">
                                    <td data-label="Tipo" class="solar-table__cell">{{ $typeOptions[$item->type] ?? ucfirst((string) $item->type) }}</td>
                                    <td data-label="Categoria" class="solar-table__cell">{{ $categoryOptions[$item->category] ?? ucfirst((string) $item->category) }}</td>
                                    <td data-label="Nome" class="solar-table__cell solar-table__cell--primary">
                                        <strong class="solar-table__entity">{{ $item->name }}</strong>
                                        @if ($item->catalogItem)
                                            <div class="solar-project-simulation-card__chips">
                                                <span class="solar-mini-badge solar-mini-badge--automatic">Catalogo</span>
                                                @if ($item->catalogItem->sku)
                                                    <span class="solar-mini-badge">{{ $item->catalogItem->sku }}</span>
                                                @endif
                                            </div>
                                        @endif
                                        @if ($item->description)
                                            <div class="hub-table__sub solar-table__meta">{{ $item->description }}</div>
                                        @endif
                                    </td>
                                    <td data-label="Quantidade" class="solar-table__cell">{{ number_format((float) $item->quantity, 2, ',', '.') }}</td>
                                    <td data-label="Custo unitario" class="solar-table__cell">R$ {{ number_format((float) $item->unit_cost, 2, ',', '.') }}</td>
                                    <td data-label="Preco unitario" class="solar-table__cell">R$ {{ number_format((float) $item->unit_price, 2, ',', '.') }}</td>
                                    <td data-label="Total" class="solar-table__cell">R$ {{ number_format((float) $item->total_price, 2, ',', '.') }}</td>
                                    <td data-label="Acoes" class="solar-table__cell solar-table__cell--actions">
                                        <div class="hub-table-actions solar-table__actions">
                                            <form action="{{ route('solar.quotes.items.destroy', [$quote->id, $item->id]) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="hub-btn hub-btn--subtle" onclick="return confirm('Remover este item do orcamento?');">Remover</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr class="solar-table__row">
                                    <td colspan="8" class="solar-table__cell solar-table__cell--primary">
                                        <strong class="solar-table__entity">Nenhum item adicionado ainda.</strong>
                                        <div class="hub-table__sub solar-table__meta">
                                            Monte o orcamento com modulos, inversor, estrutura, cabeamento, instalacao e servicos complementares.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </section>
@endsection
