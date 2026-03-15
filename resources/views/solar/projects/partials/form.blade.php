@php
    $statusLabels = [
        'draft' => 'Base em montagem',
        'qualified' => 'Em revisao',
        'proposal' => 'Pronto para orcamento',
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
    $equivalentSolarRadiationDaily = $sizingService->estimateEquivalentSolarRadiationDaily($resolvedSolarFactor);
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
    $initialMarginContext = $sizingService->resolveMarginContext($companySetting, $initialSystemPower);
    $initialKitCost = $sizingService->estimateKitCostForMarginContext($initialSuggestedPrice, $initialMarginContext);
    $initialGrossProfit = $sizingService->estimateGrossProfit($initialSuggestedPrice, $initialKitCost);
    $initialKitBreakdown = $sizingService->estimateKitCostBreakdown($initialKitCost);
    $initialComposition = $sizingService->resolveSystemComposition(
        $initialModuleQuantity,
        $defaultModulePower,
        $defaultInverterModel,
        $initialSystemPower,
    );
    $pricingSource = $pricingReferenceSource ?? ($usesMarketPriceFallback ? 'fallback' : 'company');
    $pricingSourceLabel = match ($pricingSource) {
        'company' => 'Preco da empresa',
        'regional' => 'Media regional',
        default => 'Fallback padrao nacional',
    };
    $marginRangesPayload = $companySetting?->marginRanges
        ? $companySetting->marginRanges
            ->map(fn ($range) => [
                'min_kwp' => (float) $range->min_kwp,
                'max_kwp' => $range->max_kwp !== null ? (float) $range->max_kwp : null,
                'margin_percent' => $range->margin_percent !== null ? (float) $range->margin_percent : null,
                'requires_negotiation' => (bool) $range->requires_negotiation,
            ])
            ->values()
            ->all()
        : [];
    $initialMarginLabel = match ($initialMarginContext['source']) {
        'range' => $initialMarginContext['requires_negotiation']
            ? 'Negociacao manual'
            : number_format((float) $initialMarginContext['margin_percent'], 2, ',', '.') . '%',
        'unmatched' => 'Sem faixa',
        'pending' => 'Aguardando potencia',
        'default' => number_format((float) \App\Modules\Solar\Services\SolarSizingService::DEFAULT_GROSS_MARGIN_PERCENT, 2, ',', '.') . '%',
        default => $initialMarginContext['margin_percent'] !== null
            ? number_format((float) $initialMarginContext['margin_percent'], 2, ',', '.') . '%'
            : 'Nao configurada',
    };
    $initialMarginDetail = match ($initialMarginContext['source']) {
        'range' => $initialMarginContext['requires_negotiation']
            ? 'Faixa configurada para negociacao manual.'
            : 'Faixa de potencia aplicada automaticamente.',
        'unmatched' => 'Nenhuma faixa cobre a potencia atual do sistema.',
        'pending' => 'A margem por faixa aparece depois que a potencia for calculada.',
        'default' => 'Sem margem fixa cadastrada. O Solar usa o padrao interno como referencia.',
        default => 'Indicador interno com menor peso visual.',
    };
    $currentMonthlyConsumption = old('monthly_consumption_kwh', $project->monthly_consumption_kwh);
    $defaultModulePowerValue = $companySetting?->default_module_power ?: 550;
    $autoSystemPower = $currentMonthlyConsumption !== null && $currentMonthlyConsumption !== ''
        ? $sizingService->estimateRequiredPowerKwp((float) $currentMonthlyConsumption, $resolvedSolarFactor)
        : null;
    $powerReferenceForAutomation = $initialSystemPower ?: $autoSystemPower;
    $autoModuleQuantity = $powerReferenceForAutomation !== null
        ? $sizingService->estimateModuleQuantity((float) $powerReferenceForAutomation, (int) $defaultModulePower)
        : null;
    $autoGeneration = $powerReferenceForAutomation !== null
        ? $sizingService->estimateGenerationKwh((float) $powerReferenceForAutomation, $resolvedSolarFactor)
        : null;
    $autoSuggestedPrice = $powerReferenceForAutomation !== null
        ? $sizingService->estimateSuggestedPrice((float) $powerReferenceForAutomation, (float) $effectivePricePerKwp)
        : null;
    $defaultInverterReference = $companySetting?->default_inverter_model ?: null;
    $numbersDiffer = static fn ($left, $right, float $precision = 0.01): bool => $left !== null
        && $right !== null
        && abs((float) $left - (float) $right) > $precision;
    $stringsDiffer = static fn ($left, $right): bool => trim((string) $left) !== ''
        && trim((string) $right) !== ''
        && mb_strtolower(trim((string) $left)) !== mb_strtolower(trim((string) $right));
    $fieldOrigins = [
        'system_power' => $autoSystemPower === null
            ? ['label' => 'Potencia do sistema', 'state' => 'pending', 'badge' => 'Aguardando consumo', 'detail' => 'Informe o consumo mensal para liberar a sugestao automatica.']
            : ($numbersDiffer($initialSystemPower, $autoSystemPower)
                ? ['label' => 'Potencia do sistema', 'state' => 'manual', 'badge' => 'Manual agora', 'detail' => 'O valor atual nao segue mais a potencia sugerida pelo consumo.']
                : ['label' => 'Potencia do sistema', 'state' => 'automatic', 'badge' => 'Automatico agora', 'detail' => 'Segue a leitura automatica de consumo e fator solar.']),
        'module_power' => $stringsDiffer((string) $defaultModulePower, (string) $defaultModulePowerValue)
            ? ['label' => 'Potencia do modulo', 'state' => 'manual', 'badge' => 'Manual agora', 'detail' => 'A potencia atual do modulo foi alterada em relacao ao padrao da empresa.']
            : ['label' => 'Potencia do modulo', 'state' => 'base', 'badge' => 'Padrao da empresa', 'detail' => 'Segue o padrao comercial configurado para o modulo.'],
        'module_quantity' => $autoModuleQuantity === null
            ? ['label' => 'Quantidade de modulos', 'state' => 'pending', 'badge' => 'Aguardando sistema', 'detail' => 'A quantidade aparece depois do calculo de potencia.']
            : ($numbersDiffer($initialModuleQuantity, $autoModuleQuantity, 0.5)
                ? ['label' => 'Quantidade de modulos', 'state' => 'manual', 'badge' => 'Manual agora', 'detail' => 'A quantidade atual nao segue mais a composicao automatica.']
                : ['label' => 'Quantidade de modulos', 'state' => 'automatic', 'badge' => 'Automatico agora', 'detail' => 'Segue a composicao automatica derivada da potencia atual.']),
        'generation' => $autoGeneration === null
            ? ['label' => 'Geracao estimada', 'state' => 'pending', 'badge' => 'Aguardando sistema', 'detail' => 'A geracao estimada depende da potencia calculada.']
            : ($numbersDiffer($initialGeneration, $autoGeneration)
                ? ['label' => 'Geracao estimada', 'state' => 'manual', 'badge' => 'Manual agora', 'detail' => 'A geracao atual foi ajustada em relacao a leitura automatica.']
                : ['label' => 'Geracao estimada', 'state' => 'automatic', 'badge' => 'Automatico agora', 'detail' => 'Segue a leitura automatica de potencia e fator solar.']),
        'inverter' => $defaultInverterReference !== null && $stringsDiffer($defaultInverterModel, $defaultInverterReference)
            ? ['label' => 'Modelo do inversor', 'state' => 'manual', 'badge' => 'Manual agora', 'detail' => 'O inversor atual foi trocado em relacao ao padrao da empresa.']
            : ['label' => 'Modelo do inversor', 'state' => 'base', 'badge' => 'Padrao da empresa', 'detail' => 'Segue o modelo de inversor sugerido como ponto de partida.'],
        'suggested_price' => $autoSuggestedPrice === null
            ? ['label' => 'Orcamento inicial', 'state' => 'pending', 'badge' => 'Aguardando sistema', 'detail' => 'O valor sugerido depende da potencia e da referencia por kWp.']
            : ($numbersDiffer($initialSuggestedPrice, $autoSuggestedPrice)
                ? ['label' => 'Orcamento inicial', 'state' => 'manual', 'badge' => 'Manual agora', 'detail' => 'O valor atual nao segue mais a leitura automatica de potencia e preco por kWp.']
                : ['label' => 'Orcamento inicial', 'state' => 'automatic', 'badge' => 'Automatico agora', 'detail' => 'Segue a referencia automatica de potencia, margem e preco por kWp.']),
    ];
    $manualOriginCount = collect($fieldOrigins)->where('state', 'manual')->count();
    $shouldOpenCommercialReview = $project->exists
        || ($initialSuggestedPrice !== null && $initialSuggestedPrice !== '')
        || $initialMonthlySavings !== null
        || old('monthly_consumption_kwh') !== null
        || old('energy_bill_value') !== null;
@endphp

<div
    class="solar-project-flow"
    data-solar-project-form
    data-automation-preview-url="{{ route('solar.projects.automation-preview') }}"
    data-project-id="{{ $project->exists ? $project->id : '' }}"
    data-pricing-effective-per-kwp="{{ old('company_price_per_kwp', $effectivePricePerKwp) }}"
    data-pricing-default-per-kwp="{{ \App\Modules\Solar\Services\SolarSizingService::MARKET_PRICE_PER_KWP }}"
    data-pricing-source="{{ $pricingSource }}"
    data-regional-price-lookup='@json($regionalPriceLookup ?? [], JSON_UNESCAPED_UNICODE)'
    data-margin-percent="{{ old('company_margin_percent', $companySetting?->margin_percent) }}"
    data-margin-mode="{{ $companySetting?->margin_mode ?: \App\Modules\Solar\Models\SolarCompanySetting::MARGIN_MODE_FIXED }}"
    data-margin-ranges='@json($marginRangesPayload, JSON_UNESCAPED_UNICODE)'
    data-margin-default-percent="{{ \App\Modules\Solar\Services\SolarSizingService::DEFAULT_GROSS_MARGIN_PERCENT }}"
    data-default-inverter-model="{{ $companySetting?->default_inverter_model }}"
    data-residual-minimum-cost="{{ $residualMinimumCost }}"
    data-solar-factor-used="{{ $resolvedSolarFactor }}"
    data-solar-factor-source="{{ $resolvedSolarFactorSource }}"
>
    <section class="hub-card hub-card--subtle solar-system-hero">
        <div class="solar-system-hero__header">
            <div>
                <p class="solar-section-eyebrow">Projeto solar</p>
                <h3 data-project-summary-name>{{ old('name', $project->name ?: 'Novo projeto em andamento') }}</h3>
                <p class="hub-note">Preencha o essencial primeiro. Depois revise a sugestao do sistema e a leitura inicial do orcamento.</p>
                <div class="solar-system-hero__context">
                    <span class="solar-system-hero__context-item">
                        <strong>Cliente</strong>
                        <span data-project-summary="customer">{{ $selectedCustomer?->name ?: 'Cliente pendente' }}</span>
                    </span>
                    <span class="solar-system-hero__context-item">
                        <strong>Cidade / UF</strong>
                        <span data-project-summary="location">{{ $locationSummary !== '' ? $locationSummary : 'Local pendente' }}</span>
                    </span>
                </div>
            </div>

            <div class="solar-system-hero__status {{ $usesMarketPriceFallback ? 'is-market' : 'is-ready' }}">
                <span class="solar-system-hero__status-label">Passo a passo</span>
                <strong>Cliente, local e consumo primeiro</strong>
                <p>
                    O Solar atualiza a sugestao de sistema e a leitura inicial do orcamento conforme voce preenche os dados base do projeto.
                </p>
                <a href="{{ route('solar.settings.edit') }}" class="hub-btn hub-btn--subtle">Configuracoes comerciais</a>
            </div>
        </div>

        <div class="solar-system-hero__metrics">
            <article class="solar-system-hero-metric solar-system-hero-metric--energy">
                <span class="solar-system-hero-metric__label">Potencia do sistema</span>
                <strong class="solar-summary-metric__value" data-project-summary="power">
                    {{ $initialSystemPower ? number_format((float) $initialSystemPower, 2, ',', '.') . ' kWp' : 'Aguardando consumo' }}
                </strong>
            </article>

            <article class="solar-system-hero-metric solar-system-hero-metric--energy">
                <span class="solar-summary-metric__label">Geracao estimada</span>
                <strong class="solar-summary-metric__value" data-project-summary="generation">
                    {{ $initialGeneration ? number_format((float) $initialGeneration, 2, ',', '.') . ' kWh/mes' : 'Aguardando sistema' }}
                </strong>
            </article>

            <article class="solar-system-hero-metric solar-system-hero-metric--highlight">
                <span class="solar-summary-metric__label">Orcamento inicial</span>
                <strong class="solar-summary-metric__value" data-project-summary="price">
                    {{ $initialSuggestedPrice ? 'R$ ' . number_format((float) $initialSuggestedPrice, 2, ',', '.') : 'Aguardando consumo' }}
                </strong>
            </article>

            <article class="solar-system-hero-metric solar-system-hero-metric--highlight">
                <span class="solar-summary-metric__label">Economia mensal</span>
                <strong class="solar-summary-metric__value" data-project-summary="savings">
                    {{ $initialMonthlySavings !== null ? 'R$ ' . number_format((float) $initialMonthlySavings, 2, ',', '.') : 'Aguardando conta' }}
                </strong>
            </article>
        </div>
    </section>

    <section class="hub-card hub-card--subtle solar-flow-section">
        <div class="solar-flow-section__header solar-flow-section__header--stacked-md">
            <div>
                <p class="solar-section-eyebrow">Comece pelo essencial</p>
                <h3>Preencha primeiro o que destrava o restante</h3>
                <p class="hub-note">Se esta for a primeira passagem pelo projeto, foque nestes tres pontos. O resto pode ser revisado depois.</p>
            </div>
        </div>

        <div class="solar-composition-list">
            <article class="solar-composition-item">
                <span class="solar-composition-item__label">1. Cliente</span>
                <strong class="solar-composition-item__value">Selecione quem esta sendo atendido.</strong>
            </article>
            <article class="solar-composition-item">
                <span class="solar-composition-item__label">2. Local</span>
                <strong class="solar-composition-item__value">Preencha CEP, cidade, UF e dados basicos da instalacao.</strong>
            </article>
            <article class="solar-composition-item">
                <span class="solar-composition-item__label">3. Consumo</span>
                <strong class="solar-composition-item__value">Informe consumo e conta de energia para destravar a leitura inicial.</strong>
            </article>
        </div>
    </section>

    <details class="solar-flow-disclosure">
        <summary class="solar-flow-disclosure__summary">
            <span>Ver detalhes da automacao</span>
            <small>Fator solar, geolocalizacao, margem e origem do preco</small>
        </summary>

        <section class="hub-card hub-card--subtle solar-technical-panel solar-flow-disclosure__panel">
            <div class="solar-flow-section__header">
                <div>
                    <p class="solar-section-eyebrow">Detalhes da automacao</p>
                    <h3>Contexto tecnico e confianca da leitura inicial</h3>
                </div>
                <p class="hub-note">Use este bloco quando precisar validar origem do preco, fator solar e status da geolocalizacao.</p>
            </div>

            <div class="solar-technical-panel__grid">
                <span class="solar-technical-panel__signal">
                    <strong>Fator solar</strong>
                    <span data-solar-factor-display>{{ number_format($resolvedSolarFactor, 2, ',', '.') }} kWh/kWp/mes</span>
                </span>
                <span class="solar-technical-panel__signal">
                    <strong>Radiacao solar</strong>
                    <span data-solar-radiation-display>{{ number_format($equivalentSolarRadiationDaily, 2, ',', '.') }} kWh/m2/dia</span>
                </span>
                <span class="solar-technical-panel__signal">
                    <strong>Origem</strong>
                    <span data-solar-factor-source-display>{{ strtoupper($resolvedSolarFactorSource === 'pvgis' ? 'PVGIS' : 'padrao') }}</span>
                </span>
                <span class="solar-technical-panel__signal">
                    <strong>Precisao</strong>
                    <span data-geocoding-precision-display>{{ $geocodingPrecisionLabel }}</span>
                </span>
                <span class="solar-technical-panel__signal">
                    <strong>Margem aplicada</strong>
                    <span data-pricing-preview="margin">{{ $initialMarginLabel }}</span>
                </span>
                <span class="solar-technical-panel__signal">
                    <strong>Preco por kWp</strong>
                    <span data-pricing-preview="rate">{{ 'R$ ' . number_format((float) $effectivePricePerKwp, 2, ',', '.') }}</span>
                </span>
                <span class="solar-technical-panel__signal">
                    <strong>Geolocalizacao</strong>
                    <span data-geocoding-status>{{ $geocodingStatusLabel }}</span>
                </span>
                <span class="solar-technical-panel__signal">
                    <strong>Status da automacao</strong>
                    <span class="solar-status-pill" data-automation-sync-status>Pronto</span>
                </span>
                <span class="solar-technical-panel__fallback solar-technical-panel__signal" data-solar-factor-message @if (($solarFactorData['message'] ?? null) === null) hidden @endif>
                    {{ $solarFactorData['message'] }}
                </span>
            </div>
        </section>
    </details>

    <section class="hub-card hub-card--subtle solar-flow-section" data-cep-lookup>
        <div class="solar-flow-section__header">
            <div>
                <p class="solar-section-eyebrow">1. Cliente e local</p>
                <h3>Quem vamos atender e onde fica a instalacao?</h3>
            </div>
            <p class="hub-note">Comece pelo cliente e pelo local. O Solar preenche o restante automaticamente.</p>
        </div>

        <div class="hub-grid hub-grid--billing">
            <div>
                <label for="solar_customer_id" class="hub-auth-label">Cliente *</label>
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
                <p>CEP, cidade, UF e concessionaria ajudam o Solar a montar a leitura inicial com menos digitacao.</p>
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
                    data-utility-lookup="{{ e(json_encode($utilityLookup, JSON_UNESCAPED_UNICODE)) }}"
                >
                    <option value="">Selecionar automaticamente</option>
                    @foreach ($utilities as $utility)
                        <option value="{{ $utility->id }}" @selected((string) old('energy_utility_id', $project->energy_utility_id) === (string) $utility->id)>
                            {{ $utility->name }} ({{ $utility->state }})
                        </option>
                    @endforeach
                </select>
                <script type="application/json" data-utility-lookup-json>@json($utilityLookup, JSON_UNESCAPED_UNICODE)</script>
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
            <span class="solar-technical-panel__signal" data-geocoding-precision-display>{{ $geocodingPrecisionLabel }}</span>
        </div>
    </section>

    <section class="hub-card hub-card--subtle solar-flow-section">
        <div class="solar-flow-section__header">
            <div>
                <p class="solar-section-eyebrow">2. Consumo energetico</p>
                <h3>Qual consumo vamos usar como base?</h3>
            </div>
            <p class="hub-note">Consumo e conta de energia destravam a sugestao do sistema e a leitura inicial do orcamento.</p>
        </div>

        <div class="hub-grid hub-grid--billing">
            <div>
                <label for="monthly_consumption_kwh" class="hub-auth-label">Consumo mensal (kWh)</label>
                <input id="monthly_consumption_kwh" name="monthly_consumption_kwh" type="number" step="0.01" min="0" class="hub-auth-input" value="{{ old('monthly_consumption_kwh', $project->monthly_consumption_kwh) }}" data-sizing-monthly>
                @error('monthly_consumption_kwh')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
                <p class="solar-field-note">Esse numero aciona a sugestao inicial do sistema.</p>
            </div>

            <div>
                <label for="energy_bill_value" class="hub-auth-label">Valor da conta de energia</label>
                <input id="energy_bill_value" name="energy_bill_value" type="text" inputmode="decimal" class="hub-auth-input" value="{{ old('energy_bill_value', $project->energy_bill_value) }}" data-pricing-energy-bill data-currency-brl>
                @error('energy_bill_value')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
                <p class="solar-field-note">Esse valor alimenta a economia estimada da leitura comercial.</p>
            </div>
        </div>
    </section>

    <div class="solar-inline-tip">
        <strong>Proximo passo:</strong>
        <span>com cliente, local e consumo preenchidos, revise a sugestao do sistema abaixo. Os ajustes avancados podem ficar para depois.</span>
    </div>

    <section class="hub-card hub-card--subtle solar-flow-section solar-origin-panel">
        <div class="solar-flow-section__header solar-flow-section__header--stacked-md">
            <div>
                <p class="solar-section-eyebrow">Origem da leitura</p>
                <h3>Veja o que segue automatico e o que ja foi ajustado</h3>
                <p class="hub-note">Essa leitura ajuda a entender, em um relance, quais campos ainda seguem a automacao do Solar e quais ja foram refinados manualmente.</p>
            </div>
            <div class="solar-project-showcase__status {{ $manualOriginCount > 0 ? 'is-market' : 'is-ready' }}">
                <span class="solar-project-showcase__status-label">Leitura atual</span>
                <strong>{{ $manualOriginCount }} {{ $manualOriginCount === 1 ? 'ajuste manual' : 'ajustes manuais' }}</strong>
                <p>{{ $manualOriginCount > 0 ? 'Os campos marcados como manuais deixaram de seguir a sugestao automatica atual.' : 'Os principais campos ainda seguem a automacao ou o padrao comercial da empresa.' }}</p>
            </div>
        </div>

        <div class="solar-origin-grid">
            @foreach ($fieldOrigins as $origin)
                <article class="solar-origin-card solar-origin-card--{{ $origin['state'] }}">
                    <span class="solar-origin-card__label">{{ $origin['label'] }}</span>
                    <strong>{{ $origin['badge'] }}</strong>
                    <p>{{ $origin['detail'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section
        class="hub-card hub-card--subtle solar-flow-section solar-sizing-panel"
        data-sizing-form
        data-pricing-effective-per-kwp="{{ old('company_price_per_kwp', $effectivePricePerKwp) }}"
    >
        <div class="solar-flow-section__header">
            <div>
                <p class="solar-section-eyebrow">3. Sistema sugerido</p>
                <h3>Revise a sugestao e ajuste so o necessario</h3>
            </div>
            <p class="hub-note">Se estiver comecando, valide primeiro potencia, modulos e geracao. O restante pode ser refinado depois.</p>
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
            </article>

            <article class="solar-sizing-chip solar-sizing-chip--featured">
                <span class="solar-sizing-chip__label">Geracao estimada</span>
                <strong class="solar-sizing-chip__value" data-sizing-preview="generation">
                    {{ $initialGeneration ? number_format((float) $initialGeneration, 2, ',', '.') . ' kWh' : 'Aguardando sistema' }}
                </strong>
            </article>

            <article class="solar-sizing-chip">
                <span class="solar-sizing-chip__label">Modulos sugeridos</span>
                <strong class="solar-sizing-chip__value" data-sizing-preview="modules">
                    {{ $initialModuleQuantity ? number_format((float) $initialModuleQuantity, 0, ',', '.') : 'Aguardando sistema' }}
                </strong>
                <span class="solar-sizing-chip__meta">Quantidade inicial para estimar area e custo do kit.</span>
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
                <p class="solar-field-note solar-field-note--automatic"><span class="solar-origin-pill solar-origin-pill--{{ $fieldOrigins['system_power']['state'] }}">{{ $fieldOrigins['system_power']['badge'] }}</span> O Solar sugere este valor a partir do consumo.</p>
            </div>

            <div>
                <label for="module_power" class="hub-auth-label">Potencia do modulo (W)</label>
                <input id="module_power" name="module_power" type="number" step="1" min="1" class="hub-auth-input" value="{{ old('module_power', $project->module_power ?: ($companySetting?->default_module_power ?: 550)) }}" data-sizing-module-power>
                @error('module_power')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
                <p class="solar-field-note"><span class="solar-origin-pill solar-origin-pill--{{ $fieldOrigins['module_power']['state'] }}">{{ $fieldOrigins['module_power']['badge'] }}</span> Ao alterar este campo, modulos e geracao sao recalculados.</p>
            </div>
        </div>

        <div class="hub-grid hub-grid--billing">
            <div>
                <label for="module_quantity" class="hub-auth-label">Quantidade de modulos</label>
                <input id="module_quantity" name="module_quantity" type="number" step="1" min="1" class="hub-auth-input" value="{{ old('module_quantity', $project->module_quantity) }}" data-sizing-module-quantity>
                @error('module_quantity')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
                <p class="solar-field-note solar-field-note--automatic"><span class="solar-origin-pill solar-origin-pill--{{ $fieldOrigins['module_quantity']['state'] }}">{{ $fieldOrigins['module_quantity']['badge'] }}</span> Sugestao inicial com edicao manual liberada.</p>
            </div>

            <div>
                <label for="estimated_generation_kwh" class="hub-auth-label">Geracao estimada (kWh)</label>
                <input id="estimated_generation_kwh" name="estimated_generation_kwh" type="number" step="0.01" min="0" class="hub-auth-input" value="{{ old('estimated_generation_kwh', $project->estimated_generation_kwh) }}" data-sizing-generation>
                @error('estimated_generation_kwh')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
                <p class="solar-field-note solar-field-note--automatic"><span class="solar-origin-pill solar-origin-pill--{{ $fieldOrigins['generation']['state'] }}">{{ $fieldOrigins['generation']['badge'] }}</span> Estimativa inicial para apoiar a conversa comercial.</p>
            </div>
        </div>

        <div>
            <label for="inverter_model" class="hub-auth-label">Modelo do inversor</label>
            <input id="inverter_model" name="inverter_model" type="text" class="hub-auth-input" value="{{ $defaultInverterModel }}" data-sizing-inverter>
            @error('inverter_model')
                <p class="hub-note">{{ $message }}</p>
            @enderror
            <p class="solar-field-note"><span class="solar-origin-pill solar-origin-pill--{{ $fieldOrigins['inverter']['state'] }}">{{ $fieldOrigins['inverter']['badge'] }}</span> O modelo padrao entra como ponto de partida e pode ser trocado.</p>
        </div>
    </section>

    <details class="solar-flow-disclosure" @if ($shouldOpenCommercialReview) open @endif>
        <summary class="solar-flow-disclosure__summary">
            <span>Revisar leitura comercial</span>
            <small>Orcamento inicial, economia estimada e retorno</small>
        </summary>

        <div class="solar-flow-disclosure__body">
            <section class="hub-card hub-card--subtle solar-flow-section solar-pricing-panel">
                <div class="solar-flow-section__header">
                    <div>
                        <p class="solar-section-eyebrow">4. Orcamento inicial</p>
                        <h3>Revise o valor sugerido do atendimento</h3>
                    </div>
                    <p class="hub-note">Atualizacao automatica do valor inicial sem travar sua edicao.</p>
                </div>

                <div class="solar-flow-section__inline-tags">
                    <span class="solar-mini-badge solar-mini-badge--automatic">Leitura automatica</span>
                    <span class="solar-mini-badge solar-mini-badge--editable">Ajuste manual permitido</span>
                </div>

                <div class="solar-sizing-panel__highlights solar-pricing-panel__highlights">
                    <article class="solar-sizing-chip solar-sizing-chip--featured">
                        <span class="solar-sizing-chip__label">Orcamento inicial</span>
                        <strong class="solar-sizing-chip__value" data-pricing-preview="total">
                            {{ $initialSuggestedPrice ? 'R$ ' . number_format((float) $initialSuggestedPrice, 2, ',', '.') : 'Aguardando dimensionamento' }}
                        </strong>
                    </article>

                    <article class="solar-sizing-chip solar-sizing-chip--featured">
                        <span class="solar-sizing-chip__label">Economia mensal</span>
                        <strong class="solar-sizing-chip__value" data-pricing-preview="savings">
                            {{ $initialMonthlySavings !== null ? 'R$ ' . number_format((float) $initialMonthlySavings, 2, ',', '.') . '/mes' : 'Informe a conta de energia' }}
                        </strong>
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
                            {{ $initialMarginLabel }}
                        </strong>
                        <span class="solar-sizing-chip__meta" data-pricing-preview="margin-detail">{{ $initialMarginDetail }}</span>
                    </article>

                    <article class="solar-sizing-chip">
                        <span class="solar-sizing-chip__label">Origem do preco</span>
                        <strong class="solar-sizing-chip__value" data-pricing-preview="source">
                            {{ $pricingSourceLabel }}
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
                        <span class="solar-sizing-chip__meta">Leitura simples de retorno anual para reforcar o atendimento.</span>
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
                    @if ($pricingSource === 'company')
                        O orcamento inicial sera recalculado automaticamente quando a potencia do sistema mudar. Voce ainda pode ajustar manualmente antes de salvar.
                    @elseif ($pricingSource === 'regional')
                        Orcamento inicial baseado em media de mercado. Voce pode ajustar manualmente.
                    @else
                        Orcamento inicial baseado em media de mercado. Voce pode ajustar manualmente.
                    @endif
                </p>

                <div class="solar-inline-tip">
                    <strong>Leitura comercial:</strong>
                    <span>o Solar sugere um valor inicial e mantem o campo liberado para ajuste fino na negociacao.</span>
                </div>

                <div class="hub-grid hub-grid--billing">
                    <div>
                        <label for="suggested_price" class="hub-auth-label">Orcamento inicial (R$)</label>
                        <input id="suggested_price" name="suggested_price" type="text" inputmode="decimal" class="hub-auth-input" value="{{ old('suggested_price', $project->suggested_price) }}" data-pricing-suggested-price data-currency-brl>
                        @error('suggested_price')
                            <p class="hub-note">{{ $message }}</p>
                        @enderror
                        <p class="solar-field-note solar-field-note--automatic">
                            <span class="solar-origin-pill solar-origin-pill--{{ $fieldOrigins['suggested_price']['state'] }}">{{ $fieldOrigins['suggested_price']['badge'] }}</span>
                            {{ $pricingSource === 'company'
                                ? 'Valor inicial com base na configuracao da empresa. Voce pode ajustar manualmente.'
                                : 'Valor inicial baseado em media de mercado. Voce pode ajustar manualmente.' }}
                        </p>
                    </div>

                    <div>
                        <label for="pricing_notes" class="hub-auth-label">Observacoes comerciais</label>
                        <input id="pricing_notes" name="pricing_notes" type="text" class="hub-auth-input" value="{{ old('pricing_notes', $project->pricing_notes) }}">
                        @error('pricing_notes')
                            <p class="hub-note">{{ $message }}</p>
                        @enderror
                        <p class="solar-field-note">Registre condicao comercial, prazo ou ressalva importante para o atendimento.</p>
                    </div>
                </div>
            </section>

            <section class="hub-card hub-card--subtle solar-flow-section solar-financial-panel">
                <div class="solar-flow-section__header">
                    <div>
                        <p class="solar-section-eyebrow">5. Economia estimada</p>
                        <h3>Use os indicadores como apoio comercial</h3>
                    </div>
                    <p class="hub-note">Mostre economia, retorno e ROI de forma objetiva, sem transformar esta tela em um laudo financeiro.</p>
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
                        Simulacao ativa com base na conta atual de energia. Use os valores como reforco comercial do atendimento.
                    @else
                        Informe o valor da conta de energia para gerar a simulacao financeira automatica.
                    @endif
                </p>
            </section>
        </div>
    </details>

    <section class="hub-card hub-card--subtle solar-flow-section">
        <div class="solar-flow-section__header">
            <div>
                <p class="solar-section-eyebrow">6. Observacoes e status</p>
                <h3>Defina status e proximos passos do projeto</h3>
            </div>
            <p class="hub-note">Se ainda estiver revisando a base, mantenha como rascunho e siga para a simulacao depois.</p>
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
                <textarea id="notes" name="notes" class="hub-auth-input" placeholder="Ex.: condicao do telhado, expectativa do cliente, prazo de retorno, contexto do atendimento...">{{ old('notes', $project->notes) }}</textarea>
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
