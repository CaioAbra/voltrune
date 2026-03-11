@php
    $statusLabels = [
        'draft' => 'Rascunho',
        'qualified' => 'Qualificado',
        'proposal' => 'Proposta',
        'won' => 'Fechado',
    ];
    $sizingService = app(\App\Modules\Solar\Services\SolarSizingService::class);

    $selectedCustomerId = old('solar_customer_id', $project->solar_customer_id);
    $selectedCustomer = $customers->firstWhere('id', (int) $selectedCustomerId);
    $currentStatus = old('status', $project->status ?: 'draft');
    $defaultModulePower = old('module_power', $project->module_power ?: ($companySetting?->default_module_power ?: 550));
    $defaultInverterModel = old('inverter_model', $project->inverter_model ?: ($companySetting?->default_inverter_model ?: ''));
    $locationSummary = collect([
        old('city', $project->city),
        old('state', $project->state),
    ])->filter()->implode(' / ');
    $initialSuggestedPrice = old('suggested_price', $project->suggested_price);
    $initialEnergyBillValue = old('energy_bill_value', $project->energy_bill_value);
    $residualMinimumCost = (float) ($residualMinimumCost ?? 70);
    $resolvedSolarFactor = (float) old('solar_factor_used', $project->solar_factor_used ?: ($solarFactorData['factor'] ?? \App\Modules\Solar\Services\SolarSizingService::DEFAULT_SOLAR_FACTOR));
    $resolvedSolarFactorSource = old('solar_factor_source', $project->solar_factor_source ?: ($solarFactorData['source'] ?? 'fallback'));
    $geocodingPrecision = old('geocoding_precision', $project->geocoding_precision ?: 'fallback');
    $geocodingPrecisionLabel = match ($geocodingPrecision) {
        'address' => 'Endereco refinado',
        'city' => 'Cidade aproximada',
        default => 'Fallback padrao',
    };
    $geocodingStatusLabel = match (old('geocoding_status', $project->geocoding_status ?: 'pending')) {
        'ready' => 'Localizacao pronta',
        'not_found' => 'Localizacao nao encontrada',
        'address_loaded' => 'Endereco parcial carregado',
        'not_requested' => 'Aguardando CEP',
        default => 'Buscando melhor localizacao',
    };
    $solarFactorContextLabel = match (true) {
        $resolvedSolarFactorSource === 'pvgis' && $geocodingPrecision === 'address' => 'Origem PVGIS com endereco refinado.',
        $resolvedSolarFactorSource === 'pvgis' && $geocodingPrecision === 'city' => 'Origem PVGIS com aproximacao por cidade.',
        default => 'Fallback padrao ativo.',
    };
    $initialMonthlySavings = $initialEnergyBillValue !== null && $initialEnergyBillValue !== '' && (float) $initialEnergyBillValue > 0
        ? max((float) $initialEnergyBillValue - $residualMinimumCost, 0)
        : null;
    $initialAnnualSavings = $initialMonthlySavings !== null ? $initialMonthlySavings * 12 : null;
    $initialLifetimeSavings = $initialAnnualSavings !== null ? $initialAnnualSavings * 25 : null;
    $initialSystemPower = old('system_power_kwp', $project->system_power_kwp);
    $initialGeneration = old('estimated_generation_kwh', $project->estimated_generation_kwh);
    $initialModuleQuantity = old('module_quantity', $project->module_quantity);
    $initialAreaSquareMeters = $sizingService->estimateAreaFromModules($initialModuleQuantity)
        ?? ($initialSystemPower ? round((float) $initialSystemPower * \App\Modules\Solar\Services\SolarSizingService::ESTIMATED_AREA_PER_KWP, 2) : null);
    $initialPaybackMonths = ($initialSuggestedPrice && $initialMonthlySavings !== null && (float) $initialMonthlySavings > 0)
        ? (int) ceil((float) $initialSuggestedPrice / (float) $initialMonthlySavings)
        : null;
    $initialRoiPercentage = $sizingService->estimateRoiPercentage($initialSuggestedPrice, $initialEnergyBillValue);
    $initialKitCost = $sizingService->estimateKitCost($initialSuggestedPrice, $companySetting?->margin_percent);
    $initialGrossProfit = $sizingService->estimateGrossProfit($initialSuggestedPrice, $initialKitCost);
    $initialKitBreakdown = $sizingService->estimateKitCostBreakdown($initialKitCost);
    $initialComposition = $sizingService->resolveSystemComposition(
        $initialModuleQuantity,
        $defaultModulePower,
        $defaultInverterModel,
        $initialSystemPower,
    );
@endphp

<div
    class="solar-project-flow"
    data-solar-project-form
    data-pricing-per-kwp="{{ old('company_price_per_kwp', $effectivePricePerKwp) }}"
    data-pricing-source="{{ $pricingReferenceSource ?? ($usesMarketPriceFallback ? 'fallback' : 'company') }}"
    data-regional-price-lookup='@json($regionalPriceLookup ?? [], JSON_UNESCAPED_UNICODE)'
    data-margin-percent="{{ old('company_margin_percent', $companySetting?->margin_percent) }}"
    data-default-inverter-model="{{ $companySetting?->default_inverter_model }}"
    data-residual-minimum-cost="{{ $residualMinimumCost }}"
    data-solar-factor-used="{{ $resolvedSolarFactor }}"
    data-solar-factor-source="{{ $resolvedSolarFactorSource }}"
>
    <section class="hub-card hub-card--subtle solar-project-command">
        <div class="solar-project-command__header">
            <div>
                <p class="solar-section-eyebrow">Resumo comercial</p>
                <h3 data-project-summary-name>{{ old('name', $project->name ?: 'Projeto solar em preparacao') }}</h3>
                <p class="hub-note">Leitura rapida do projeto para validar cliente, local e resultado comercial sem reler o cadastro inteiro.</p>
            </div>

            <div class="solar-project-command__status {{ $usesMarketPriceFallback ? 'is-market' : 'is-ready' }}">
                <span class="solar-project-command__status-label">Automacao comercial</span>
                <strong>{{ $usesMarketPriceFallback ? 'Referencia de mercado ativa' : 'Preco proprio ativo' }}</strong>
                <p>
                    @if ($usesMarketPriceFallback)
                        O Solar esta usando {{ 'R$ ' . number_format((float) $effectivePricePerKwp, 2, ',', '.') }}/kWp como base automatica.
                    @else
                        O Solar esta usando o preco por kWp da empresa para gerar a previa automatica.
                    @endif
                </p>
                <a href="{{ route('solar.settings.edit') }}" class="hub-btn hub-btn--subtle">Configuracoes comerciais</a>
            </div>
        </div>

        <div class="solar-project-command__summary-board solar-project-command__summary-board--compact">
            <article class="solar-summary-metric">
                <span class="solar-summary-metric__label">Cliente</span>
                <strong class="solar-summary-metric__value" data-project-summary="customer">{{ $selectedCustomer?->name ?: 'Cliente pendente' }}</strong>
            </article>

            <article class="solar-summary-metric">
                <span class="solar-summary-metric__label">Cidade / UF</span>
                <strong class="solar-summary-metric__value" data-project-summary="location">{{ $locationSummary !== '' ? $locationSummary : 'Local pendente' }}</strong>
            </article>

            <article class="solar-summary-metric solar-summary-metric--commercial">
                <span class="solar-summary-metric__label">Potencia sugerida</span>
                <strong class="solar-summary-metric__value" data-project-summary="power">
                    {{ $initialSystemPower ? number_format((float) $initialSystemPower, 2, ',', '.') . ' kWp' : 'Aguardando consumo' }}
                </strong>
            </article>

            <article class="solar-summary-metric solar-summary-metric--commercial">
                <span class="solar-summary-metric__label">Geracao estimada</span>
                <strong class="solar-summary-metric__value" data-project-summary="generation">
                    {{ $initialGeneration ? number_format((float) $initialGeneration, 2, ',', '.') . ' kWh' : 'Aguardando sistema' }}
                </strong>
            </article>

            <article class="solar-summary-metric solar-summary-metric--hero">
                <span class="solar-summary-metric__label">Preco sugerido</span>
                <strong class="solar-summary-metric__value" data-project-summary="price">
                    {{ $initialSuggestedPrice ? 'R$ ' . number_format((float) $initialSuggestedPrice, 2, ',', '.') : 'Aguardando pre-orcamento' }}
                </strong>
                <span class="solar-summary-metric__meta">Previa automatica para acelerar a conversa comercial.</span>
            </article>

            <article class="solar-summary-metric solar-summary-metric--hero">
                <span class="solar-summary-metric__label">Economia mensal</span>
                <strong class="solar-summary-metric__value" data-project-summary="savings">
                    {{ $initialMonthlySavings !== null ? 'R$ ' . number_format((float) $initialMonthlySavings, 2, ',', '.') : 'Aguardando conta' }}
                </strong>
                <span class="solar-summary-metric__meta">Mensagem principal de valor para apresentar ao cliente.</span>
            </article>
        </div>

        <div class="solar-project-command__meta">
            <span class="solar-project-command__signal">
                <strong>Fator solar</strong>
                <span data-solar-factor-display>{{ number_format($resolvedSolarFactor, 2, ',', '.') }} kWh/kWp/mes</span>
            </span>
            <span class="solar-project-command__signal">
                <strong>Origem</strong>
                <span data-solar-factor-source-display>{{ strtoupper($resolvedSolarFactorSource === 'pvgis' ? 'PVGIS' : 'padrao') }}</span>
            </span>
            <span class="solar-project-command__signal">
                <strong>Precisao</strong>
                <span>{{ $geocodingPrecisionLabel }}</span>
            </span>
                <span class="solar-project-command__signal">
                <strong>Margem</strong>
                <span data-pricing-preview="margin">{{ $companySetting?->margin_percent ? number_format((float) $companySetting->margin_percent, 2, ',', '.') . '%' : 'Nao configurada' }}</span>
            </span>
            <span class="solar-project-command__signal">
                <strong>Preco por kWp</strong>
                <span data-pricing-preview="rate">{{ 'R$ ' . number_format((float) $effectivePricePerKwp, 2, ',', '.') }}</span>
            </span>
            @if (($solarFactorData['message'] ?? null) !== null)
                <span class="solar-project-command__fallback solar-project-command__signal">{{ $solarFactorData['message'] }}</span>
            @endif
        </div>
    </section>

    <section class="hub-card hub-card--subtle solar-flow-section" data-cep-lookup>
        <div class="solar-flow-section__header">
            <div>
                <p class="solar-section-eyebrow">1. Cliente e local</p>
                <h3>Quem e o cliente e onde sera a instalacao?</h3>
            </div>
            <p class="hub-note">Organize o projeto desde o primeiro contato e deixe o Solar preencher o contexto do local sempre que possivel.</p>
        </div>

        <div class="hub-grid hub-grid--billing">
            <div>
                <label for="solar_customer_id" class="hub-auth-label">Cliente contratante *</label>
                <select id="solar_customer_id" name="solar_customer_id" class="hub-auth-input" required data-project-customer-select>
                    <option value="">Selecione um cliente</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" @selected((string) old('solar_customer_id', $project->solar_customer_id) === (string) $customer->id)>
                            {{ $customer->name }}
                        </option>
                    @endforeach
                </select>
                @error('solar_customer_id')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="name" class="hub-auth-label">Nome do projeto *</label>
                <input id="name" name="name" type="text" class="hub-auth-input" value="{{ old('name', $project->name) }}" required data-project-name>
                @error('name')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
                <p class="solar-field-note">Use um nome curto para localizar o projeto rapidamente no funil.</p>
            </div>
        </div>

        <div class="solar-flow-section__subhead">
            <div>
                <h4>Local da instalacao</h4>
                <p>CEP, cidade, UF e concessionaria ajudam o Solar a montar a previa com menos digitacao.</p>
            </div>
            <div class="solar-flow-section__inline-tags">
                <span class="solar-mini-badge solar-mini-badge--automatic">Automatico via CEP</span>
                <span class="solar-mini-badge solar-mini-badge--editable">Pode editar</span>
            </div>
        </div>

        <div class="hub-grid hub-grid--billing">
            <div>
                <label for="zip_code" class="hub-auth-label">CEP</label>
                <input
                    id="zip_code"
                    name="zip_code"
                    type="text"
                    class="hub-auth-input"
                    value="{{ old('zip_code', $project->zip_code) }}"
                    inputmode="numeric"
                    maxlength="9"
                    data-cep-input
                >
                @error('zip_code')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
                <p class="hub-note solar-cep-feedback" data-cep-feedback>Digite um CEP valido para preencher rua, bairro, cidade e UF automaticamente.</p>
            </div>

            <div>
                <label for="number" class="hub-auth-label">Numero</label>
                <input id="number" name="number" type="text" class="hub-auth-input" value="{{ old('number', $project->number) }}">
                @error('number')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="hub-grid hub-grid--billing">
            <div>
                <label for="street" class="hub-auth-label">Rua</label>
                <input id="street" name="street" type="text" class="hub-auth-input" value="{{ old('street', $project->street) }}" data-cep-street>
                @error('street')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="complement" class="hub-auth-label">Complemento</label>
                <input id="complement" name="complement" type="text" class="hub-auth-input" value="{{ old('complement', $project->complement) }}">
                @error('complement')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="hub-grid hub-grid--billing">
            <div>
                <label for="district" class="hub-auth-label">Bairro</label>
                <input id="district" name="district" type="text" class="hub-auth-input" value="{{ old('district', $project->district) }}" data-cep-district>
                @error('district')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="property_type" class="hub-auth-label">Tipo de imovel</label>
                <input id="property_type" name="property_type" type="text" class="hub-auth-input" value="{{ old('property_type', $project->property_type) }}">
                @error('property_type')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="hub-grid hub-grid--billing">
            <div>
                <label for="city" class="hub-auth-label">Cidade</label>
                <input id="city" name="city" type="text" class="hub-auth-input" value="{{ old('city', $project->city) }}" data-cep-city data-project-city>
                @error('city')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="state" class="hub-auth-label">UF</label>
                <input id="state" name="state" type="text" class="hub-auth-input" value="{{ old('state', $project->state) }}" maxlength="2" data-cep-state data-project-state>
                @error('state')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="hub-grid hub-grid--billing">
            <div>
                <label for="energy_utility_id" class="hub-auth-label">Concessionaria</label>
                <select
                    id="energy_utility_id"
                    name="energy_utility_id"
                    class="hub-auth-input"
                    data-utility-select
                    data-utility-lookup='@json($utilityLookup, JSON_UNESCAPED_UNICODE)'
                >
                    <option value="">Selecionar automaticamente</option>
                    @foreach ($utilities as $utility)
                        <option value="{{ $utility->id }}" @selected((string) old('energy_utility_id', $project->energy_utility_id) === (string) $utility->id)>
                            {{ $utility->name }} ({{ $utility->state }})
                        </option>
                    @endforeach
                </select>
                @error('energy_utility_id')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
                <p class="hub-note solar-utility-feedback" data-utility-feedback>A concessionaria sera sugerida automaticamente a partir da cidade e da UF.</p>
            </div>

            <div>
                <label for="connection_type" class="hub-auth-label">Tipo de conexao</label>
                <select id="connection_type" name="connection_type" class="hub-auth-input">
                    <option value="">Selecione</option>
                    <option value="mono" @selected(old('connection_type', $project->connection_type) === 'mono')>Monofasico</option>
                    <option value="bi" @selected(old('connection_type', $project->connection_type) === 'bi')>Bifasico</option>
                    <option value="tri" @selected(old('connection_type', $project->connection_type) === 'tri')>Trifasico</option>
                </select>
                @error('connection_type')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="solar-flow-section__footnote">
            <strong>Localizacao automatica:</strong>
            <span data-geocoding-status>{{ $geocodingStatusLabel }}</span>
            <span class="solar-project-command__signal">{{ $geocodingPrecisionLabel }}</span>
        </div>
    </section>

    <section class="hub-card hub-card--subtle solar-flow-section">
        <div class="solar-flow-section__header">
            <div>
                <p class="solar-section-eyebrow">2. Consumo energetico</p>
                <h3>Qual consumo vamos usar como base?</h3>
            </div>
            <p class="hub-note">Com consumo mensal e valor da conta, o Solar monta a sugestao comercial em tempo real.</p>
        </div>

        <div class="hub-grid hub-grid--billing">
            <div>
                <label for="monthly_consumption_kwh" class="hub-auth-label">Consumo mensal (kWh)</label>
                <input id="monthly_consumption_kwh" name="monthly_consumption_kwh" type="number" step="0.01" min="0" class="hub-auth-input" value="{{ old('monthly_consumption_kwh', $project->monthly_consumption_kwh) }}" data-sizing-monthly>
                @error('monthly_consumption_kwh')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
                <p class="solar-field-note">Esse numero aciona o pre-dimensionamento automatico.</p>
            </div>

            <div>
                <label for="energy_bill_value" class="hub-auth-label">Valor da conta de energia</label>
                <input id="energy_bill_value" name="energy_bill_value" type="text" inputmode="decimal" class="hub-auth-input" value="{{ old('energy_bill_value', $project->energy_bill_value) }}" data-pricing-energy-bill data-currency-brl>
                @error('energy_bill_value')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
                <p class="solar-field-note">Esse valor alimenta a economia estimada da previa comercial.</p>
            </div>
        </div>
    </section>

    <section
        class="hub-card hub-card--subtle solar-flow-section solar-sizing-panel"
        data-sizing-form
        data-pricing-per-kwp="{{ old('company_price_per_kwp', $effectivePricePerKwp) }}"
    >
        <div class="solar-flow-section__header">
            <div>
                <p class="solar-section-eyebrow">3. Sistema sugerido</p>
                <h3>Veja a solucao sugerida e ajuste se precisar</h3>
            </div>
            <p class="hub-note">Os campos principais sao sugeridos automaticamente com base no consumo e na potencia do modulo, sem bloquear ajuste manual.</p>
        </div>

        <div class="solar-flow-section__inline-tags">
            <span class="solar-mini-badge solar-mini-badge--automatic">Sugerido automaticamente</span>
            <span class="solar-mini-badge solar-mini-badge--editable">Pode editar manualmente</span>
        </div>

        <div class="solar-sizing-panel__highlights">
            <article class="solar-sizing-chip solar-sizing-chip--featured">
                <span class="solar-sizing-chip__label">Potencia sugerida</span>
                <strong class="solar-sizing-chip__value" data-sizing-preview="system-power">
                    {{ $initialSystemPower ? number_format((float) $initialSystemPower, 2, ',', '.') . ' kWp' : 'Aguardando consumo' }}
                </strong>
                <span class="solar-sizing-chip__meta">Base inicial para a oferta comercial.</span>
            </article>

            <article class="solar-sizing-chip solar-sizing-chip--featured">
                <span class="solar-sizing-chip__label">Geracao estimada</span>
                <strong class="solar-sizing-chip__value" data-sizing-preview="generation">
                    {{ $initialGeneration ? number_format((float) $initialGeneration, 2, ',', '.') . ' kWh' : 'Aguardando sistema' }}
                </strong>
                <span class="solar-sizing-chip__meta">Indicador comercial para reforcar o ganho esperado.</span>
            </article>

            <article class="solar-sizing-chip">
                <span class="solar-sizing-chip__label">Fator usado</span>
                <strong class="solar-sizing-chip__value" data-sizing-preview="factor">{{ number_format($resolvedSolarFactor, 2, ',', '.') }} kWh/kWp/mes</strong>
                <span class="solar-sizing-chip__meta">{{ $solarFactorContextLabel }}</span>
            </article>

            <article class="solar-sizing-chip">
                <span class="solar-sizing-chip__label">Potencia do modulo</span>
                <strong class="solar-sizing-chip__value" data-sizing-preview="module-power">{{ $defaultModulePower ?: 550 }} W</strong>
                <span class="solar-sizing-chip__meta">Mudou aqui, modulos e geracao sao recalculados.</span>
            </article>

            <article class="solar-sizing-chip">
                <span class="solar-sizing-chip__label">Area estimada</span>
                <strong class="solar-sizing-chip__value" data-sizing-preview="area">
                    {{ $initialAreaSquareMeters !== null ? number_format((float) $initialAreaSquareMeters, 2, ',', '.') . ' m2' : 'Aguardando sistema' }}
                </strong>
                <span class="solar-sizing-chip__meta">Estimativa rapida para o vendedor validar espaco necessario.</span>
            </article>
        </div>

        <div class="solar-composition-list">
            @foreach ($initialComposition as $item)
                <article class="solar-composition-item">
                    <span class="solar-composition-item__label">{{ $item['label'] }}</span>
                    <strong class="solar-composition-item__value">{{ $item['detail'] }}</strong>
                </article>
            @endforeach
        </div>

        <p class="solar-sizing-panel__note" data-sizing-note>Preencha o consumo mensal para gerar a sugestao automatica usando o fator regional salvo no projeto.</p>

        <div class="hub-grid hub-grid--billing">
            <div>
                <label for="system_power_kwp" class="hub-auth-label">Potencia do sistema (kWp)</label>
                <input id="system_power_kwp" name="system_power_kwp" type="number" step="0.01" min="0" class="hub-auth-input" value="{{ old('system_power_kwp', $project->system_power_kwp) }}" data-sizing-system-power>
                @error('system_power_kwp')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
                <p class="solar-field-note solar-field-note--automatic"><span class="solar-mini-badge solar-mini-badge--automatic">Automatico</span> O Solar sugere este valor a partir do consumo.</p>
            </div>

            <div>
                <label for="module_power" class="hub-auth-label">Potencia do modulo (W)</label>
                <input id="module_power" name="module_power" type="number" step="1" min="1" class="hub-auth-input" value="{{ old('module_power', $project->module_power ?: ($companySetting?->default_module_power ?: 550)) }}" data-sizing-module-power>
                @error('module_power')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
                <p class="solar-field-note"><span class="solar-mini-badge solar-mini-badge--editable">Editavel</span> Ao alterar este campo, modulos e geracao sao recalculados.</p>
            </div>
        </div>

        <div class="hub-grid hub-grid--billing">
            <div>
                <label for="module_quantity" class="hub-auth-label">Quantidade de modulos</label>
                <input id="module_quantity" name="module_quantity" type="number" step="1" min="1" class="hub-auth-input" value="{{ old('module_quantity', $project->module_quantity) }}" data-sizing-module-quantity>
                @error('module_quantity')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
                <p class="solar-field-note solar-field-note--automatic"><span class="solar-mini-badge solar-mini-badge--automatic">Automatico</span> Sugestao inicial com edicao manual liberada.</p>
            </div>

            <div>
                <label for="estimated_generation_kwh" class="hub-auth-label">Geracao estimada (kWh)</label>
                <input id="estimated_generation_kwh" name="estimated_generation_kwh" type="number" step="0.01" min="0" class="hub-auth-input" value="{{ old('estimated_generation_kwh', $project->estimated_generation_kwh) }}" data-sizing-generation>
                @error('estimated_generation_kwh')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
                <p class="solar-field-note solar-field-note--automatic"><span class="solar-mini-badge solar-mini-badge--automatic">Automatico</span> Estimativa inicial para apoiar a conversa comercial.</p>
            </div>
        </div>

        <div>
            <label for="inverter_model" class="hub-auth-label">Modelo do inversor</label>
            <input id="inverter_model" name="inverter_model" type="text" class="hub-auth-input" value="{{ $defaultInverterModel }}" data-sizing-inverter>
            @error('inverter_model')
                <p class="hub-note">{{ $message }}</p>
            @enderror
            <p class="solar-field-note"><span class="solar-mini-badge solar-mini-badge--automatic">Automatico</span> O modelo padrao entra como ponto de partida e pode ser trocado.</p>
        </div>
    </section>

    <section class="hub-card hub-card--subtle solar-flow-section solar-pricing-panel">
        <div class="solar-flow-section__header">
            <div>
                <p class="solar-section-eyebrow">4. Pre-orcamento</p>
                <h3>Destaque o valor sugerido com leitura comercial</h3>
            </div>
            <p class="hub-note">O Solar recalcula a previa em tempo real conforme potencia, preco por kWp e conta de energia.</p>
        </div>

        <div class="solar-flow-section__inline-tags">
            <span class="solar-mini-badge solar-mini-badge--automatic">Previa automatica</span>
            <span class="solar-mini-badge solar-mini-badge--editable">Ajuste manual permitido</span>
        </div>

        <div class="solar-sizing-panel__highlights solar-pricing-panel__highlights">
            <article class="solar-sizing-chip solar-sizing-chip--featured">
                <span class="solar-sizing-chip__label">Preco sugerido</span>
                <strong class="solar-sizing-chip__value" data-pricing-preview="total">
                    {{ $initialSuggestedPrice ? 'R$ ' . number_format((float) $initialSuggestedPrice, 2, ',', '.') : 'Aguardando dimensionamento' }}
                </strong>
                <span class="solar-sizing-chip__meta">Valor inicial para comecar a negociacao com mais clareza.</span>
            </article>

            <article class="solar-sizing-chip solar-sizing-chip--featured">
                <span class="solar-sizing-chip__label">Economia mensal</span>
                <strong class="solar-sizing-chip__value" data-pricing-preview="savings">
                    {{ $initialMonthlySavings !== null ? 'R$ ' . number_format((float) $initialMonthlySavings, 2, ',', '.') . '/mes' : 'Informe a conta de energia' }}
                </strong>
                <span class="solar-sizing-chip__meta">Argumento comercial principal no primeiro atendimento.</span>
            </article>

            <article class="solar-sizing-chip">
                <span class="solar-sizing-chip__label">Preco por kWp</span>
                <strong class="solar-sizing-chip__value" data-pricing-preview="rate">
                    {{ 'R$ ' . number_format((float) $effectivePricePerKwp, 2, ',', '.') }}
                </strong>
                <span class="solar-sizing-chip__meta">Referencia usada para compor o valor sugerido.</span>
            </article>

            <article class="solar-sizing-chip">
                <span class="solar-sizing-chip__label">Margem de referencia</span>
                <strong class="solar-sizing-chip__value" data-pricing-preview="margin">
                    {{ $companySetting?->margin_percent ? number_format((float) $companySetting->margin_percent, 2, ',', '.') . '%' : 'Nao configurada' }}
                </strong>
                <span class="solar-sizing-chip__meta">Indicador interno com menor peso visual.</span>
            </article>

            <article class="solar-sizing-chip">
                <span class="solar-sizing-chip__label">Origem do preco</span>
                <strong class="solar-sizing-chip__value" data-pricing-preview="source">
                    {{ match ($pricingReferenceSource ?? null) {
                        'company' => 'Preco da empresa',
                        'regional' => 'Media regional',
                        default => 'Fallback padrao',
                    } }}
                </strong>
                <span class="solar-sizing-chip__meta">Prioridade: empresa, media regional e depois fallback padrao.</span>
            </article>

            <article class="solar-sizing-chip">
                <span class="solar-sizing-chip__label">Custo estimado do kit</span>
                <strong class="solar-sizing-chip__value" data-pricing-preview="kit-cost">
                    {{ $initialKitCost !== null ? 'R$ ' . number_format((float) $initialKitCost, 2, ',', '.') : 'Aguardando sistema' }}
                </strong>
                <span class="solar-sizing-chip__meta">Estimativa comercial baseada em modulo, inversor, estrutura e instalacao.</span>
            </article>

            <article class="solar-sizing-chip">
                <span class="solar-sizing-chip__label">Lucro bruto estimado</span>
                <strong class="solar-sizing-chip__value" data-pricing-preview="gross-profit">
                    {{ $initialGrossProfit !== null ? 'R$ ' . number_format((float) $initialGrossProfit, 2, ',', '.') : 'Aguardando sistema' }}
                </strong>
                <span class="solar-sizing-chip__meta">Leitura rapida da diferenca entre custo estimado e preco sugerido.</span>
            </article>

            <article class="solar-sizing-chip">
                <span class="solar-sizing-chip__label">ROI aproximado ao ano</span>
                <strong class="solar-sizing-chip__value" data-pricing-preview="roi">
                    {{ $initialRoiPercentage !== null ? number_format((float) $initialRoiPercentage, 1, ',', '.') . '%' : 'Aguardando simulacao' }}
                </strong>
                <span class="solar-sizing-chip__meta">Leitura simples de retorno anual para reforcar a proposta.</span>
            </article>
        </div>

        <div class="solar-composition-list solar-composition-list--costs">
            <article class="solar-composition-item">
                <span class="solar-composition-item__label">Modulos</span>
                <strong class="solar-composition-item__value">{{ $initialKitBreakdown['modules'] !== null ? 'R$ ' . number_format((float) $initialKitBreakdown['modules'], 2, ',', '.') : '-' }}</strong>
            </article>
            <article class="solar-composition-item">
                <span class="solar-composition-item__label">Inversor</span>
                <strong class="solar-composition-item__value">{{ $initialKitBreakdown['inverter'] !== null ? 'R$ ' . number_format((float) $initialKitBreakdown['inverter'], 2, ',', '.') : '-' }}</strong>
            </article>
            <article class="solar-composition-item">
                <span class="solar-composition-item__label">Estrutura</span>
                <strong class="solar-composition-item__value">{{ $initialKitBreakdown['structure'] !== null ? 'R$ ' . number_format((float) $initialKitBreakdown['structure'], 2, ',', '.') : '-' }}</strong>
            </article>
            <article class="solar-composition-item">
                <span class="solar-composition-item__label">Instalacao</span>
                <strong class="solar-composition-item__value">{{ $initialKitBreakdown['installation'] !== null ? 'R$ ' . number_format((float) $initialKitBreakdown['installation'], 2, ',', '.') : '-' }}</strong>
            </article>
        </div>

        <p class="solar-sizing-panel__note solar-pricing-panel__note" data-pricing-note>
            @if ($usesMarketPriceFallback)
                Sem preco proprio configurado, o Solar esta usando {{ 'R$ ' . number_format((float) $effectivePricePerKwp, 2, ',', '.') }}/kWp como referencia automatica.
            @else
                O preco sugerido sera recalculado automaticamente quando a potencia do sistema mudar. Voce ainda pode ajustar manualmente antes de salvar.
            @endif
        </p>

        <div class="solar-inline-tip">
            <strong>Leitura comercial:</strong>
            <span>o Solar sugere um valor inicial e mantem o campo liberado para ajuste fino na negociacao.</span>
        </div>

        <div class="hub-grid hub-grid--billing">
            <div>
                <label for="suggested_price" class="hub-auth-label">Preco sugerido (R$)</label>
                <input id="suggested_price" name="suggested_price" type="text" inputmode="decimal" class="hub-auth-input" value="{{ old('suggested_price', $project->suggested_price) }}" data-pricing-suggested-price data-currency-brl>
                @error('suggested_price')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
                <p class="solar-field-note solar-field-note--automatic"><span class="solar-mini-badge solar-mini-badge--automatic">Automatico</span> Preenchido pelo Solar e liberado para ajuste manual.</p>
            </div>

            <div>
                <label for="pricing_notes" class="hub-auth-label">Observacoes comerciais</label>
                <input id="pricing_notes" name="pricing_notes" type="text" class="hub-auth-input" value="{{ old('pricing_notes', $project->pricing_notes) }}">
                @error('pricing_notes')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
                <p class="solar-field-note">Registre condicao comercial, prazo ou ressalva importante para a proposta.</p>
            </div>
        </div>
    </section>

    <section class="hub-card hub-card--subtle solar-flow-section solar-financial-panel">
        <div class="solar-flow-section__header">
            <div>
                <p class="solar-section-eyebrow">5. Simulacao financeira</p>
                <h3>Transforme a simulacao em argumento comercial</h3>
            </div>
            <p class="hub-note">A economia mensal e o destaque principal. Os horizontes anual e de 25 anos entram como reforco.</p>
        </div>

        <div class="solar-sizing-panel__highlights solar-financial-panel__highlights">
            <article class="solar-sizing-chip solar-sizing-chip--featured solar-sizing-chip--commercial">
                <span class="solar-sizing-chip__label">Economia mensal estimada</span>
                <strong class="solar-sizing-chip__value" data-financial-preview="monthly">
                    {{ $initialMonthlySavings !== null ? 'R$ ' . number_format((float) $initialMonthlySavings, 2, ',', '.') : 'Informe a conta de energia' }}
                </strong>
                <span class="solar-sizing-chip__meta">Considerando custo minimo residual de {{ 'R$ ' . number_format($residualMinimumCost, 2, ',', '.') }} por mes.</span>
            </article>

            <article class="solar-sizing-chip">
                <span class="solar-sizing-chip__label">Economia anual</span>
                <strong class="solar-sizing-chip__value" data-financial-preview="annual">
                    {{ $initialAnnualSavings !== null ? 'R$ ' . number_format((float) $initialAnnualSavings, 2, ',', '.') : 'Aguardando simulacao' }}
                </strong>
            </article>

            <article class="solar-sizing-chip">
                <span class="solar-sizing-chip__label">Economia em 25 anos</span>
                <strong class="solar-sizing-chip__value" data-financial-preview="lifetime">
                    {{ $initialLifetimeSavings !== null ? 'R$ ' . number_format((float) $initialLifetimeSavings, 2, ',', '.') : 'Aguardando simulacao' }}
                </strong>
            </article>

            <article class="solar-sizing-chip">
                <span class="solar-sizing-chip__label">Retorno estimado</span>
                <strong class="solar-sizing-chip__value" data-financial-preview="payback">
                    {{ $initialPaybackMonths !== null ? $initialPaybackMonths . ' meses' : 'Aguardando simulacao' }}
                </strong>
            </article>

            <article class="solar-sizing-chip">
                <span class="solar-sizing-chip__label">ROI aproximado</span>
                <strong class="solar-sizing-chip__value" data-financial-preview="roi">
                    {{ $initialRoiPercentage !== null ? number_format((float) $initialRoiPercentage, 1, ',', '.') . '%' : 'Aguardando simulacao' }}
                </strong>
            </article>
        </div>

        <p class="solar-sizing-panel__note solar-financial-panel__note" data-financial-note>
            @if ($initialMonthlySavings !== null)
                Simulacao ativa com base na conta atual de energia. Use os valores como reforco comercial da proposta.
            @else
                Informe o valor da conta de energia para gerar a simulacao financeira automatica.
            @endif
        </p>
    </section>

    <section class="hub-card hub-card--subtle solar-flow-section">
        <div class="solar-flow-section__header">
            <div>
                <p class="solar-section-eyebrow">6. Observacoes e status</p>
                <h3>Feche o contexto comercial do projeto</h3>
            </div>
            <p class="hub-note">Defina a etapa do funil e registre qualquer detalhe util para a proxima conversa com o cliente.</p>
        </div>

        <div class="hub-grid hub-grid--billing">
            <div>
                <label for="status" class="hub-auth-label">Status comercial *</label>
                <select id="status" name="status" class="hub-auth-input" required data-project-status>
                    @foreach ($statusLabels as $statusValue => $statusLabel)
                        <option value="{{ $statusValue }}" @selected($currentStatus === $statusValue)>
                            {{ $statusLabel }}
                        </option>
                    @endforeach
                </select>
                @error('status')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
                <p class="solar-field-note">Marque em que etapa comercial este projeto esta agora.</p>
            </div>

            <div>
                <label for="notes" class="hub-auth-label">Observacoes gerais</label>
                <textarea id="notes" name="notes" class="hub-auth-input" placeholder="Ex.: condicao do telhado, expectativa do cliente, prazo de retorno, contexto da simulacao...">{{ old('notes', $project->notes) }}</textarea>
                @error('notes')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
                <p class="solar-field-note">Use este espaco para contexto de negociacao, objecoes, prazo ou proximos passos.</p>
            </div>
        </div>
    </section>

    <div class="hub-actions">
        <button type="submit" class="hub-btn">{{ $submitLabel }}</button>
        <a href="{{ route('solar.projects.index') }}" class="hub-btn hub-btn--subtle">Cancelar</a>
    </div>
</div>
