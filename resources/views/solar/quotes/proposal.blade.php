<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proposta comercial | Voltrune Solar</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Marcellus&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
@php
    $statusLabel = match ($quote->status) {
        'draft' => 'Em montagem',
        'review' => 'Em revisao interna',
        'sent' => 'Enviado ao cliente',
        'approved' => 'Aprovado',
        'won' => 'Fechado',
        'lost' => 'Perdido',
        default => strtoupper((string) $quote->status),
    };
    $customerName = $project?->customer?->name ?: '-';
    $simulationSnapshot = is_array($quote->simulation_snapshot_json) ? $quote->simulation_snapshot_json : [];
    $resolvedFinalPrice = $quoteSummary['item_count'] > 0 ? $quoteSummary['total_price'] : $quote->final_price;
    $versionLabel = 'V' . str_pad((string) max((int) ($quote->version_number ?: 1), 1), 2, '0', STR_PAD_LEFT);
    $timelineMoments = collect([
        ['label' => 'Enviado', 'at' => $quote->sent_at],
        ['label' => 'Aprovado', 'at' => $quote->approved_at],
        ['label' => 'Fechado', 'at' => $quote->won_at],
        ['label' => 'Perdido', 'at' => $quote->lost_at],
    ])->filter(fn (array $moment) => $moment['at'])->values();
    $historyEntries = $quote->events->take(6);
@endphp
<body class="solar-body solar-proposal-page">
    <main class="solar-proposal-document">
        <section class="solar-proposal-document__hero">
            <div>
                <p class="solar-section-eyebrow">Proposta comercial</p>
                <h1>{{ $quote->title }}</h1>
                <p class="solar-proposal-document__description">
                    Documento comercial pronto para compartilhar. Use a impressao do navegador para salvar esta proposta em PDF com a mesma estrutura visual.
                </p>

                <div class="solar-project-showcase__chips">
                    <span class="solar-mini-badge solar-mini-badge--editable">{{ $statusLabel }}</span>
                    <span class="solar-mini-badge solar-mini-badge--automatic">{{ $versionLabel }}</span>
                    <span class="solar-mini-badge">{{ $quote->proposal_code ?: 'Codigo em preparacao' }}</span>
                    @if ($quote->owner_name)
                        <span class="solar-mini-badge">{{ $quote->owner_name }}</span>
                    @endif
                </div>
            </div>

            <div class="solar-proposal-document__actions no-print">
                <a href="{{ route('solar.quotes.edit', $quote->id) }}" class="hub-btn hub-btn--subtle">Voltar ao orcamento</a>
                <button type="button" class="hub-btn" onclick="window.print()">Imprimir / salvar em PDF</button>
            </div>
        </section>

        <section class="solar-proposal-document__metrics">
            <article class="solar-proposal-metric">
                <span>Cliente</span>
                <strong>{{ $customerName }}</strong>
                <small>{{ $project?->name ?: 'Projeto nao vinculado' }}</small>
            </article>
            <article class="solar-proposal-metric">
                <span>Preco final</span>
                <strong>{{ $resolvedFinalPrice ? 'R$ ' . number_format((float) $resolvedFinalPrice, 2, ',', '.') : '-' }}</strong>
                <small>{{ $quoteSummary['item_count'] }} {{ $quoteSummary['item_count'] === 1 ? 'item' : 'itens' }}</small>
            </article>
            <article class="solar-proposal-metric">
                <span>Economia mensal</span>
                <strong>{{ $quote->estimated_savings ? 'R$ ' . number_format((float) $quote->estimated_savings, 2, ',', '.') : '-' }}</strong>
                <small>{{ ($simulationSnapshot['estimated_payback_months'] ?? $simulation?->estimated_payback_months) ? ($simulationSnapshot['estimated_payback_months'] ?? $simulation?->estimated_payback_months) . ' meses de payback' : 'Payback pendente' }}</small>
            </article>
            <article class="solar-proposal-metric">
                <span>Potencia</span>
                <strong>{{ ($simulationSnapshot['system_power_kwp'] ?? $simulation?->system_power_kwp) ? number_format((float) ($simulationSnapshot['system_power_kwp'] ?? $simulation?->system_power_kwp), 2, ',', '.') . ' kWp' : '-' }}</strong>
                <small>{{ $simulationSnapshot['name'] ?? $simulation?->name ?: 'Simulacao base' }}</small>
            </article>
        </section>

        <section class="solar-proposal-document__grid">
            <article class="solar-proposal-sheet">
                <p class="solar-section-eyebrow">Escopo comercial</p>
                <h2>Resumo da proposta</h2>

                <div class="solar-project-show__info-grid">
                    <p><strong>Codigo comercial</strong><span>{{ $quote->proposal_code ?: '-' }}</span></p>
                    <p><strong>Versao</strong><span>{{ $versionLabel }}</span></p>
                    <p><strong>Responsavel</strong><span>{{ $quote->owner_name ?: '-' }}</span></p>
                    <p><strong>Proximo contato</strong><span>{{ $quote->next_contact_at?->format('d/m/Y H:i') ?: '-' }}</span></p>
                    <p><strong>Simulacao base</strong><span>{{ $simulationSnapshot['name'] ?? $simulation?->name ?: '-' }}</span></p>
                    <p><strong>Preco sugerido</strong><span>{{ ($simulationSnapshot['suggested_price'] ?? $simulation?->suggested_price) ? 'R$ ' . number_format((float) ($simulationSnapshot['suggested_price'] ?? $simulation?->suggested_price), 2, ',', '.') : '-' }}</span></p>
                    <p><strong>Geracao estimada</strong><span>{{ ($simulationSnapshot['estimated_generation_kwh'] ?? $simulation?->estimated_generation_kwh) ? number_format((float) ($simulationSnapshot['estimated_generation_kwh'] ?? $simulation?->estimated_generation_kwh), 2, ',', '.') . ' kWh/mes' : '-' }}</span></p>
                    <p><strong>Inversor</strong><span>{{ $simulationSnapshot['inverter_model'] ?? $simulation?->inverter_model ?: '-' }}</span></p>
                </div>

                @if ($quote->notes)
                    <div class="solar-proposal-sheet__notes">
                        <strong>Observacoes comerciais</strong>
                        <p>{!! nl2br(e($quote->notes)) !!}</p>
                    </div>
                @endif
            </article>

            <article class="solar-proposal-sheet">
                <p class="solar-section-eyebrow">Fluxo comercial</p>
                <h2>Linha do tempo e versoes</h2>

                @if ($timelineMoments->isNotEmpty())
                    <div class="solar-proposal-timeline">
                        @foreach ($timelineMoments as $moment)
                            <article class="solar-proposal-timeline__item is-ready">
                                <span>{{ $moment['label'] }}</span>
                                <strong>{{ $moment['at']->format('d/m/Y H:i') }}</strong>
                            </article>
                        @endforeach
                    </div>
                @else
                    <p class="solar-field-note">Esta proposta ainda nao tem marcos comerciais registrados.</p>
                @endif

                <div class="solar-proposal-versions">
                    @foreach ($proposalVersions as $version)
                        @php
                            $versionStatusLabel = match ($version->status) {
                                'draft' => 'Em montagem',
                                'review' => 'Em revisao interna',
                                'sent' => 'Enviado ao cliente',
                                'approved' => 'Aprovado',
                                'won' => 'Fechado',
                                'lost' => 'Perdido',
                                default => strtoupper((string) $version->status),
                            };
                        @endphp
                        <div class="solar-proposal-version {{ $version->id === $quote->id ? 'is-current' : '' }}">
                            <span class="solar-proposal-version__label">Versao {{ str_pad((string) max((int) ($version->version_number ?: 1), 1), 2, '0', STR_PAD_LEFT) }}</span>
                            <strong>{{ $version->proposal_code ?: 'Codigo pendente' }}</strong>
                            <small>{{ $versionStatusLabel }}</small>
                        </div>
                    @endforeach
                </div>
            </article>
        </section>

        <section class="solar-proposal-sheet">
            <div class="solar-flow-section__header solar-flow-section__header--stacked-md">
                <div>
                    <p class="solar-section-eyebrow">Composicao</p>
                    <h2>Itens incluidos</h2>
                </div>
                <div class="solar-project-showcase__status is-ready">
                    <span class="solar-project-showcase__status-label">Totais</span>
                    <strong>{{ $quoteSummary['item_count'] }} {{ $quoteSummary['item_count'] === 1 ? 'item' : 'itens' }}</strong>
                    <p>Custo estimado: {{ $quoteSummary['item_count'] > 0 ? 'R$ ' . number_format((float) $quoteSummary['total_cost'], 2, ',', '.') : '-' }}</p>
                </div>
            </div>

            <div class="solar-table-wrap">
                <table class="hub-table solar-table solar-table--quote-items">
                    <thead>
                        <tr>
                            <th>Categoria</th>
                            <th>Nome</th>
                            <th>Quantidade</th>
                            <th>Preco unitario</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($quote->items as $item)
                            <tr class="solar-table__row">
                                <td data-label="Categoria" class="solar-table__cell">{{ ucfirst((string) $item->category) }}</td>
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
                                <td data-label="Preco unitario" class="solar-table__cell">R$ {{ number_format((float) $item->unit_price, 2, ',', '.') }}</td>
                                <td data-label="Total" class="solar-table__cell">R$ {{ number_format((float) $item->total_price, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr class="solar-table__row">
                                <td colspan="5" class="solar-table__cell solar-table__cell--primary">
                                    <strong class="solar-table__entity">Nenhum item detalhado nesta versao.</strong>
                                    <div class="hub-table__sub solar-table__meta">Abra o editor do orcamento para montar a composicao completa antes de enviar ao cliente.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @if ($historyEntries->isNotEmpty())
            <section class="solar-proposal-sheet">
                <p class="solar-section-eyebrow">Historico recente</p>
                <h2>Ultimos movimentos desta versao</h2>

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
            </section>
        @endif
    </main>
</body>
</html>
