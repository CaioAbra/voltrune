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
        'draft' => 'Base automatica',
        'qualified' => 'Em revisao',
        'proposal' => 'Pronta para orcamento',
        'won' => 'Fechado',
        default => strtoupper((string) $simulation->status),
    };
    $solarSourceLabel = strtoupper(($simulation->solar_factor_source ?? 'fallback') === 'pvgis' ? 'PVGIS' : 'PADRAO');
    $radiationDaily = $sizingService->estimateEquivalentSolarRadiationDaily($simulation->solar_factor_used);
    $quoteCount = $simulation->quotes->count();
    $readyForQuote = $simulation->suggested_price && $simulation->system_power_kwp;
    $marginLabel = match ($marginContext['source']) {
        'range' => $marginContext['requires_negotiation']
            ? 'Negociacao manual'
            : number_format((float) $marginContext['margin_percent'], 2, ',', '.') . '%',
        'unmatched' => 'Sem faixa',
        'pending' => 'Aguardando potencia',
        'default' => number_format((float) \App\Modules\Solar\Services\SolarSizingService::DEFAULT_GROSS_MARGIN_PERCENT, 2, ',', '.') . '%',
        default => $marginContext['margin_percent'] !== null
            ? number_format((float) $marginContext['margin_percent'], 2, ',', '.') . '%'
            : 'Nao configurada',
    };
    $marginNote = match ($marginContext['source']) {
        'range' => $marginContext['requires_negotiation']
            ? 'A configuracao comercial da empresa exige tratativa manual nesta faixa de potencia.'
            : 'Margem vinda da faixa de potencia configurada pela empresa.',
        'unmatched' => 'A potencia atual nao encontrou nenhuma faixa cadastrada para margem automatica.',
        'pending' => 'A margem por faixa depende da potencia calculada do sistema.',
        'default' => 'Sem margem fixa cadastrada. O Solar usa o padrao interno como referencia.',
        default => 'Margem de referencia da configuracao comercial da empresa.',
    };
    $basePayload = app(\App\Modules\Solar\Services\SolarSimulationService::class)->buildPayloadFromProject($project, $companySetting);
    $numbersDiffer = static fn ($left, $right, float $precision = 0.01): bool => $left !== null
        && $right !== null
        && abs((float) $left - (float) $right) > $precision;
    $stringsDiffer = static fn ($left, $right): bool => trim((string) $left) !== ''
        && trim((string) $right) !== ''
        && mb_strtolower(trim((string) $left)) !== mb_strtolower(trim((string) $right));
    $fieldOrigins = [
        ['label' => 'Potencia do sistema', 'state' => $numbersDiffer($simulation->system_power_kwp, $basePayload['system_power_kwp'] ?? null) ? 'manual' : 'base', 'detail' => 'Compara a potencia atual com a base automatica do projeto.'],
        ['label' => 'Potencia do modulo', 'state' => $numbersDiffer($simulation->module_power, $basePayload['module_power'] ?? null, 0.5) ? 'manual' : 'base', 'detail' => 'Mostra se a configuracao do modulo foi alterada nesta revisao.'],
        ['label' => 'Quantidade de modulos', 'state' => $numbersDiffer($simulation->module_quantity, $basePayload['module_quantity'] ?? null, 0.5) ? 'manual' : 'base', 'detail' => 'Ajuda a enxergar quando a composicao deixou de seguir o projeto base.'],
        ['label' => 'Geracao estimada', 'state' => $numbersDiffer($simulation->estimated_generation_kwh, $basePayload['estimated_generation_kwh'] ?? null) ? 'manual' : 'base', 'detail' => 'Indica se a geracao foi refinada dentro da simulacao atual.'],
        ['label' => 'Preco sugerido', 'state' => $numbersDiffer($simulation->suggested_price, $basePayload['suggested_price'] ?? null) ? 'manual' : 'base', 'detail' => 'Mostra se a leitura comercial foi ajustada alem da base do projeto.'],
        ['label' => 'Modelo do inversor', 'state' => $stringsDiffer($simulation->inverter_model, $basePayload['inverter_model'] ?? null) ? 'manual' : 'base', 'detail' => 'Sinaliza quando o inversor desta simulacao nao e o mesmo da base.'],
    ];
    $manualOriginCount = collect($fieldOrigins)->where('state', 'manual')->count();
    $financialPrimary = [
        ['label' => 'Preco sugerido', 'value' => $simulation->suggested_price ? 'R$ ' . number_format((float) $simulation->suggested_price, 2, ',', '.') : '-', 'tone' => 'featured'],
        ['label' => 'Economia mensal', 'value' => $simulation->estimated_monthly_savings ? 'R$ ' . number_format((float) $simulation->estimated_monthly_savings, 2, ',', '.') : '-', 'tone' => 'commercial'],
        ['label' => 'Payback', 'value' => $simulation->estimated_payback_months ? $simulation->estimated_payback_months . ' meses' : '-', 'tone' => 'default'],
        ['label' => 'Economia anual', 'value' => $simulation->estimated_annual_savings ? 'R$ ' . number_format((float) $simulation->estimated_annual_savings, 2, ',', '.') : '-', 'tone' => 'default'],
    ];
    $financialAdvanced = [
        ['label' => 'ROI estimado', 'value' => $simulation->estimated_roi ? number_format((float) $simulation->estimated_roi, 1, ',', '.') . '%' : '-'],
        ['label' => 'Margem aplicada', 'value' => $marginLabel],
        ['label' => 'Lucro bruto estimado', 'value' => $simulation->estimated_gross_profit ? 'R$ ' . number_format((float) $simulation->estimated_gross_profit, 2, ',', '.') : '-'],
        ['label' => 'Economia em 25 anos', 'value' => $simulation->estimated_lifetime_savings ? 'R$ ' . number_format((float) $simulation->estimated_lifetime_savings, 2, ',', '.') : '-'],
    ];
    $paymentModeLabel = $simulation->payment_mode === 'financed' ? 'Financiado' : 'A vista';
    $financingMetrics = [
        ['label' => 'Modo de pagamento', 'value' => $paymentModeLabel],
        ['label' => 'Entrada prevista', 'value' => $simulation->upfront_payment ? 'R$ ' . number_format((float) $simulation->upfront_payment, 2, ',', '.') : ($simulation->payment_mode === 'financed' ? 'Sem entrada' : 'Nao aplicavel')],
        ['label' => 'Parcelas', 'value' => $simulation->payment_mode === 'financed' && $simulation->installment_count ? $simulation->installment_count . 'x' : 'Nao aplicavel'],
        ['label' => 'Taxa mensal', 'value' => $simulation->payment_mode === 'financed' && $simulation->monthly_interest_rate ? number_format((float) $simulation->monthly_interest_rate, 3, ',', '.') . '%' : 'Nao aplicavel'],
        ['label' => 'Parcela estimada', 'value' => $simulation->estimated_installment_value ? 'R$ ' . number_format((float) $simulation->estimated_installment_value, 2, ',', '.') : 'Nao aplicavel'],
        ['label' => 'Beneficio liquido mensal', 'value' => $simulation->estimated_net_monthly_benefit !== null ? 'R$ ' . number_format((float) $simulation->estimated_net_monthly_benefit, 2, ',', '.') : '-'],
        ['label' => 'Reajuste tarifario', 'value' => $simulation->tariff_growth_yearly ? number_format((float) $simulation->tariff_growth_yearly, 2, ',', '.') . '% ao ano' : '-'],
        ['label' => 'Economia projetada em 5 anos', 'value' => $simulation->estimated_five_year_savings ? 'R$ ' . number_format((float) $simulation->estimated_five_year_savings, 2, ',', '.') : '-'],
    ];
@endphp

@section('solar-content')
    <section class="hub-card solar-project-show solar-project-shell">
        <div class="hub-actions solar-project-show__actions">
            <form action="{{ route('solar.simulations.quotes.store', $simulation->id) }}" method="POST">
                @csrf
                <button type="submit" class="hub-btn">Gerar orcamento</button>
            </form>

            <form action="{{ route('solar.simulations.duplicate', $simulation->id) }}" method="POST">
                @csrf
                <button type="submit" class="hub-btn hub-btn--subtle">Duplicar simulacao</button>
            </form>

            <a href="{{ route('solar.simulations.edit', $simulation->id) }}" class="hub-btn hub-btn--subtle">Editar simulacao</a>
            <a href="{{ route('solar.projects.show', $project->id) }}" class="hub-btn hub-btn--subtle">Voltar ao projeto</a>
        </div>

        <section class="hub-card hub-card--subtle solar-project-showcase">
            <div class="solar-project-showcase__header">
                <div>
                    <p class="solar-section-eyebrow">Simulacao solar</p>
                    <h2>{{ $simulation->name }}</h2>
                    <p class="hub-note">
                        Esta e a tela principal de leitura da simulacao. Revise sistema, indicadores financeiros e siga para o orcamento quando estiver pronto.
                    </p>

                    <div class="solar-project-showcase__chips">
                        <span class="solar-mini-badge solar-mini-badge--editable">{{ $statusLabel }}</span>
                        <span class="solar-mini-badge solar-mini-badge--automatic">{{ $quoteCount }} {{ $quoteCount === 1 ? 'orcamento' : 'orcamentos' }}</span>
                        <span class="solar-mini-badge">{{ $project->name }}</span>
                    </div>
                </div>

                <div class="solar-project-showcase__status {{ $readyForQuote ? 'is-ready' : 'is-market' }}">
                    <span class="solar-project-showcase__status-label">Leitura comercial</span>
                    <strong>{{ $readyForQuote ? 'Simulacao pronta para orcamento' : 'Revise a base antes de gerar o orcamento' }}</strong>
                    <p>
                        {{ $readyForQuote ? 'A simulacao ja apresenta potencia e preco sugerido. O passo seguinte e gerar um orcamento com composicao real.' : 'Complete ou revise os dados da simulacao para deixar o orcamento mais consistente.' }}
                    </p>

                    @if ($latestQuote)
                        <a href="{{ route('solar.quotes.edit', $latestQuote->id) }}" class="hub-btn solar-project-showcase__cta">Abrir ultimo orcamento</a>
                    @endif
                </div>
            </div>

            <div class="solar-project-showcase__hero-grid">
                <article class="solar-project-showcase-metric solar-project-showcase-metric--energy">
                    <span class="solar-project-showcase-metric__label">Potencia do sistema</span>
                    <strong class="solar-project-showcase-metric__value" data-show-animate-number data-show-format="kwp" data-show-value="{{ $simulation->system_power_kwp ?: '' }}">
                        {{ $simulation->system_power_kwp ? number_format((float) $simulation->system_power_kwp, 2, ',', '.') . ' kWp' : '-' }}
                    </strong>
                </article>
                <article class="solar-project-showcase-metric solar-project-showcase-metric--highlight">
                    <span class="solar-project-showcase-metric__label">Preco sugerido</span>
                    <strong class="solar-project-showcase-metric__value" data-show-animate-number data-show-format="currency" data-show-value="{{ $simulation->suggested_price ?: '' }}">
                        {{ $simulation->suggested_price ? 'R$ ' . number_format((float) $simulation->suggested_price, 2, ',', '.') : '-' }}
                    </strong>
                </article>
                <article class="solar-project-showcase-metric solar-project-showcase-metric--commercial">
                    <span class="solar-project-showcase-metric__label">Economia mensal</span>
                    <strong class="solar-project-showcase-metric__value" data-show-animate-number data-show-format="currency" data-show-value="{{ $simulation->estimated_monthly_savings ?: '' }}">
                        {{ $simulation->estimated_monthly_savings ? 'R$ ' . number_format((float) $simulation->estimated_monthly_savings, 2, ',', '.') : '-' }}
                    </strong>
                </article>
                <article class="solar-project-showcase-metric">
                    <span class="solar-project-showcase-metric__label">Payback estimado</span>
                    <strong class="solar-project-showcase-metric__value" data-show-animate-number data-show-format="months" data-show-value="{{ $simulation->estimated_payback_months ?: '' }}">
                        {{ $simulation->estimated_payback_months ? $simulation->estimated_payback_months . ' meses' : '-' }}
                    </strong>
                </article>
            </div>
        </section>

        <section class="hub-card hub-card--subtle solar-flow-section solar-origin-panel">
            <div class="solar-flow-section__header solar-flow-section__header--stacked-md">
                <div>
                    <p class="solar-section-eyebrow">Origem da simulacao</p>
                    <h2>O que ainda segue o projeto e o que foi refinado aqui</h2>
                    <p class="hub-note">Esta leitura deixa claro quais numeros ainda espelham a base do projeto e quais ja foram ajustados nesta simulacao.</p>
                </div>
                <div class="solar-project-showcase__status {{ $manualOriginCount > 0 ? 'is-market' : 'is-ready' }}">
                    <span class="solar-project-showcase__status-label">Comparacao com o projeto</span>
                    <strong>{{ $manualOriginCount }} {{ $manualOriginCount === 1 ? 'campo ajustado' : 'campos ajustados' }}</strong>
                    <p>{{ $manualOriginCount > 0 ? 'Esta simulacao ja carrega ajustes proprios e nao depende apenas da base automatica do projeto.' : 'Os campos principais ainda seguem a base automatica do projeto.' }}</p>
                </div>
            </div>

            <div class="solar-origin-grid">
                @foreach ($fieldOrigins as $origin)
                    <article class="solar-origin-card solar-origin-card--{{ $origin['state'] }}">
                        <span class="solar-origin-card__label">{{ $origin['label'] }}</span>
                        <strong>{{ $origin['state'] === 'manual' ? 'Ajustado nesta simulacao' : 'Base do projeto' }}</strong>
                        <p>{{ $origin['detail'] }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        <div class="hub-grid solar-project-show__grid">
            <section class="hub-card hub-card--subtle solar-project-show__card solar-sizing-panel">
                <div class="solar-flow-section__header solar-flow-section__header--stacked-md">
                    <div>
                        <p class="solar-section-eyebrow">Sistema tecnico</p>
                        <h2>Composicao sugerida do sistema</h2>
                        <p class="hub-note">Os dados tecnicos continuam presentes, mas com peso visual menor que os indicadores comerciais.</p>
                    </div>
                    <div class="solar-project-showcase__status is-ready">
                        <span class="solar-project-showcase__status-label">Origem do fator solar</span>
                        <strong>{{ $solarSourceLabel }}</strong>
                        <p>{{ $projectAddress !== '' ? $projectAddress : 'Endereco pendente' }}</p>
                    </div>
                </div>

                <div class="solar-sizing-panel__highlights">
                    <article class="solar-sizing-chip"><span class="solar-sizing-chip__label">Modulos</span><strong class="solar-sizing-chip__value">{{ $simulation->module_quantity ?: '-' }}</strong></article>
                    <article class="solar-sizing-chip"><span class="solar-sizing-chip__label">Potencia do modulo</span><strong class="solar-sizing-chip__value">{{ $simulation->module_power ? number_format((int) $simulation->module_power, 0, ',', '.') . ' W' : '-' }}</strong></article>
                    <article class="solar-sizing-chip"><span class="solar-sizing-chip__label">Inversor</span><strong class="solar-sizing-chip__value">{{ $simulation->inverter_model ?: '-' }}</strong></article>
                    <article class="solar-sizing-chip"><span class="solar-sizing-chip__label">Area estimada</span><strong class="solar-sizing-chip__value">{{ $simulation->area_estimated ? number_format((float) $simulation->area_estimated, 2, ',', '.') . ' m2' : '-' }}</strong></article>
                    <article class="solar-sizing-chip solar-sizing-chip--featured"><span class="solar-sizing-chip__label">Geracao estimada</span><strong class="solar-sizing-chip__value">{{ $simulation->estimated_generation_kwh ? number_format((float) $simulation->estimated_generation_kwh, 2, ',', '.') . ' kWh/mes' : '-' }}</strong></article>
                    <article class="solar-sizing-chip"><span class="solar-sizing-chip__label">Fator solar</span><strong class="solar-sizing-chip__value">{{ $simulation->solar_factor_used ? number_format((float) $simulation->solar_factor_used, 2, ',', '.') . ' kWh/kWp/mes' : '-' }}</strong></article>
                    <article class="solar-sizing-chip"><span class="solar-sizing-chip__label">Radiacao equivalente</span><strong class="solar-sizing-chip__value">{{ $simulation->solar_factor_used ? number_format((float) $radiationDaily, 2, ',', '.') . ' kWh/m2/dia' : '-' }}</strong></article>
                    <article class="solar-sizing-chip"><span class="solar-sizing-chip__label">Local base</span><strong class="solar-sizing-chip__value">{{ $projectAddress !== '' ? $projectAddress : 'Endereco pendente' }}</strong></article>
                </div>
            </section>

            <section class="hub-card hub-card--subtle solar-flow-section solar-financial-panel">
                <div class="solar-flow-section__header solar-flow-section__header--stacked-md">
                    <div>
                        <p class="solar-section-eyebrow">Indicadores financeiros</p>
                        <h2>Leitura comercial da simulacao</h2>
                        <p class="hub-note">Primeiro ficam os numeros que ajudam a decidir rapido. A leitura avancada entra logo abaixo para quem precisa defender margem e retorno com mais profundidade.</p>
                    </div>
                    <div class="solar-project-showcase__status is-market">
                        <span class="solar-project-showcase__status-label">Leitura priorizada</span>
                        <strong>{{ $quoteCount > 0 ? 'Ja existe orcamento vinculado' : 'Pronta para virar orcamento' }}</strong>
                        <p>{{ $quoteCount > 0 ? 'A decisao rapida fica nos quatro indicadores principais. Use a camada avancada para negociar com mais seguranca.' : 'Valide os indicadores principais e gere o orcamento quando a leitura estiver coerente.' }}</p>
                    </div>
                </div>

                <div class="solar-sizing-panel__highlights solar-financial-panel__highlights solar-financial-panel__highlights--primary">
                    @foreach ($financialPrimary as $metric)
                        <article class="solar-sizing-chip @if ($metric['tone'] === 'commercial') solar-sizing-chip--commercial @elseif ($metric['tone'] === 'featured') solar-sizing-chip--featured @endif">
                            <span class="solar-sizing-chip__label">{{ $metric['label'] }}</span>
                            <strong class="solar-sizing-chip__value">{{ $metric['value'] }}</strong>
                        </article>
                    @endforeach
                </div>

                <details class="solar-flow-disclosure solar-financial-panel__advanced" open>
                    <summary class="solar-flow-disclosure__summary">
                        <strong>Leitura avancada</strong>
                        <small>ROI, margem, lucro bruto estimado e economia em horizonte longo.</small>
                    </summary>

                    <div class="solar-flow-disclosure__body">
                        <div class="solar-sizing-panel__highlights solar-financial-panel__highlights">
                            @foreach ($financialAdvanced as $metric)
                                <article class="solar-sizing-chip">
                                    <span class="solar-sizing-chip__label">{{ $metric['label'] }}</span>
                                    <strong class="solar-sizing-chip__value">{{ $metric['value'] }}</strong>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </details>

                <details class="solar-flow-disclosure solar-financial-panel__advanced" open>
                    <summary class="solar-flow-disclosure__summary">
                        <strong>Cenario de pagamento</strong>
                        <small>Entrada, parcelas, taxa e ganho liquido previsto conforme a forma de pagamento.</small>
                    </summary>

                    <div class="solar-flow-disclosure__body">
                        <div class="solar-sizing-panel__highlights solar-financial-panel__highlights">
                            @foreach ($financingMetrics as $metric)
                                <article class="solar-sizing-chip">
                                    <span class="solar-sizing-chip__label">{{ $metric['label'] }}</span>
                                    <strong class="solar-sizing-chip__value">{{ $metric['value'] }}</strong>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </details>

                <p class="solar-sizing-panel__note solar-financial-panel__note">
                    {{ $marginNote }} Compare simulacoes antes de enviar ao cliente para manter o orcamento enxuto, coerente e defendido por indicadores claros.
                </p>
            </section>
        </div>
    </section>
@endsection
