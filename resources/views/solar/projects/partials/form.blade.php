@php
    $statusLabels = [
        'draft' => 'Rascunho',
        'qualified' => 'Qualificado',
        'proposal' => 'Proposta',
        'won' => 'Fechado',
    ];

    $selectedCustomerId = old('solar_customer_id', $project->solar_customer_id);
    $selectedCustomer = $customers->firstWhere('id', (int) $selectedCustomerId);
    $currentStatus = old('status', $project->status ?: 'draft');
    $defaultModulePower = old('module_power', $project->module_power ?: ($companySetting?->default_module_power ?: 550));
    $defaultInverterModel = old('inverter_model', $project->inverter_model ?: ($companySetting?->default_inverter_model ?: ''));
    $commercialReady = true;
    $locationSummary = collect([
        old('city', $project->city),
        old('state', $project->state),
    ])->filter()->implode(' / ');
    $initialSuggestedPrice = old('suggested_price', $project->suggested_price);
    $initialEnergyBillValue = old('energy_bill_value', $project->energy_bill_value);
    $residualMinimumCost = (float) ($residualMinimumCost ?? 70);
    $resolvedSolarFactor = (float) old('solar_factor_used', $project->solar_factor_used ?: ($solarFactorData['factor'] ?? \App\Modules\Solar\Services\SolarSizingService::DEFAULT_SOLAR_FACTOR));
    $resolvedSolarFactorSource = old('solar_factor_source', $project->solar_factor_source ?: ($solarFactorData['source'] ?? 'default'));
    $initialMonthlySavings = $initialEnergyBillValue !== null && $initialEnergyBillValue !== '' && (float) $initialEnergyBillValue > 0
        ? max((float) $initialEnergyBillValue - $residualMinimumCost, 0)
        : null;
    $initialAnnualSavings = $initialMonthlySavings !== null ? $initialMonthlySavings * 12 : null;
    $initialLifetimeSavings = $initialAnnualSavings !== null ? $initialAnnualSavings * 25 : null;
    $initialModules = old('module_quantity', $project->module_quantity);
    $initialGeneration = old('estimated_generation_kwh', $project->estimated_generation_kwh);
@endphp

<div
    class="solar-project-flow"
    data-solar-project-form
    data-pricing-per-kwp="{{ old('company_price_per_kwp', $effectivePricePerKwp) }}"
    data-margin-percent="{{ old('company_margin_percent', $companySetting?->margin_percent) }}"
    data-default-inverter-model="{{ $companySetting?->default_inverter_model }}"
    data-pricing-source="{{ $usesMarketPriceFallback ? 'market' : 'custom' }}"
    data-residual-minimum-cost="{{ $residualMinimumCost }}"
    data-solar-factor-used="{{ $resolvedSolarFactor }}"
    data-solar-factor-source="{{ $resolvedSolarFactorSource }}"
>
    <section class="hub-card hub-card--subtle solar-project-command">
        <div class="solar-project-command__header">
            <div>
                <p class="solar-section-eyebrow">Pré-orçamento comercial</p>
                <h3 data-project-summary-name>{{ old('name', $project->name ?: 'Projeto solar em preparação') }}</h3>
                <p class="hub-note">Use este fluxo para transformar dados técnicos em uma leitura comercial rápida, com potência sugerida, valor inicial e economia estimada.</p>
            </div>

            <div class="solar-project-command__status {{ $usesMarketPriceFallback ? 'is-market' : 'is-ready' }}">
                <span class="solar-project-command__status-label">Configuração comercial</span>
                <strong>{{ $usesMarketPriceFallback ? 'Usando média de mercado' : 'Preço próprio configurado' }}</strong>
                <p>
                    @if ($usesMarketPriceFallback)
                        O Solar vai usar {{ 'R$ ' . number_format((float) $effectivePricePerKwp, 2, ',', '.') }}/kWp até a empresa definir um valor próprio.
                    @else
                        O preço por kWp da empresa já está pronto para gerar o valor sugerido automaticamente.
                    @endif
                </p>
                <a href="{{ route('solar.settings.edit') }}" class="hub-btn hub-btn--subtle">Abrir configurações</a>
            </div>
        </div>

        <div class="solar-project-command__meta">
            <span class="solar-project-command__signal">
                <strong>Fator solar regional</strong>
                <span data-solar-factor-display>{{ number_format($resolvedSolarFactor, 2, ',', '.') }} kWh/kWp/mês</span>
            </span>
            <span class="solar-project-command__signal">
                <strong>Origem do cálculo</strong>
                <span data-solar-factor-source-display>{{ strtoupper($resolvedSolarFactorSource === 'pvgis' ? 'PVGIS' : 'padrão') }}</span>
            </span>
            @if (($solarFactorData['message'] ?? null) !== null)
                <span class="solar-project-command__fallback solar-project-command__signal">{{ $solarFactorData['message'] }}</span>
            @endif
        </div>

        <div class="solar-project-command__summary-board">
            <article class="solar-summary-metric solar-summary-metric--hero">
                <span class="solar-summary-metric__label">Preço sugerido</span>
                <strong class="solar-summary-metric__value" data-project-summary="price">
                    {{ $initialSuggestedPrice ? 'R$ ' . number_format((float) $initialSuggestedPrice, 2, ',', '.') : 'Aguardando pré-orçamento' }}
                </strong>
                <span class="solar-summary-metric__meta">Prévia comercial automática para acelerar a proposta.</span>
            </article>

            <article class="solar-summary-metric solar-summary-metric--hero">
                <span class="solar-summary-metric__label">Economia mensal</span>
                <strong class="solar-summary-metric__value" data-project-summary="savings">
                    {{ $initialMonthlySavings !== null ? 'R$ ' . number_format((float) $initialMonthlySavings, 2, ',', '.') : 'Aguardando conta' }}
                </strong>
                <span class="solar-summary-metric__meta">Leitura inicial de valor para a conversa comercial.</span>
            </article>

            <article class="solar-summary-metric">
                <span class="solar-summary-metric__label">Potência sugerida</span>
                <strong class="solar-summary-metric__value" data-project-summary="power">
                    {{ old('system_power_kwp', $project->system_power_kwp) ? number_format((float) old('system_power_kwp', $project->system_power_kwp), 2, ',', '.') . ' kWp' : 'Aguardando consumo' }}
                </strong>
            </article>

            <article class="solar-summary-metric">
                <span class="solar-summary-metric__label">Módulos sugeridos</span>
                <strong class="solar-summary-metric__value" data-project-summary="modules">
                    {{ $initialModules ?: 'Aguardando sistema' }}
                </strong>
            </article>

            <article class="solar-summary-metric">
                <span class="solar-summary-metric__label">Geração estimada</span>
                <strong class="solar-summary-metric__value" data-project-summary="generation">
                    {{ $initialGeneration ? number_format((float) $initialGeneration, 2, ',', '.') . ' kWh' : 'Aguardando sistema' }}
                </strong>
            </article>

            <article class="solar-summary-metric">
                <span class="solar-summary-metric__label">Economia anual</span>
                <strong class="solar-summary-metric__value" data-project-summary="annual-savings">
                    {{ $initialAnnualSavings !== null ? 'R$ ' . number_format((float) $initialAnnualSavings, 2, ',', '.') : 'Aguardando simulação' }}
                </strong>
            </article>

            <article class="solar-summary-metric">
                <span class="solar-summary-metric__label">Economia em 25 anos</span>
                <strong class="solar-summary-metric__value" data-project-summary="lifetime-savings">
                    {{ $initialLifetimeSavings !== null ? 'R$ ' . number_format((float) $initialLifetimeSavings, 2, ',', '.') : 'Aguardando simulação' }}
                </strong>
            </article>

            <article class="solar-summary-metric">
                <span class="solar-summary-metric__label">Status comercial</span>
                <strong class="solar-summary-metric__value" data-project-summary="status">{{ $statusLabels[$currentStatus] ?? strtoupper((string) $currentStatus) }}</strong>
            </article>
        </div>

        <div class="solar-project-command__highlights">
            <article class="solar-sizing-chip">
                <span class="solar-sizing-chip__label">Cliente</span>
                <strong class="solar-sizing-chip__value" data-project-summary="customer">{{ $selectedCustomer?->name ?: 'Cliente pendente' }}</strong>
            </article>

            <article class="solar-sizing-chip">
                <span class="solar-sizing-chip__label">Cidade / UF</span>
                <strong class="solar-sizing-chip__value" data-project-summary="location">{{ $locationSummary !== '' ? $locationSummary : 'Local pendente' }}</strong>
            </article>

            <article class="solar-sizing-chip">
                <span class="solar-sizing-chip__label">Origem do fator</span>
                <strong class="solar-sizing-chip__value">{{ strtoupper($resolvedSolarFactorSource === 'pvgis' ? 'PVGIS' : 'padrão') }}</strong>
            </article>

            <article class="solar-sizing-chip">
                <span class="solar-sizing-chip__label">Consumo mensal</span>
                <strong class="solar-sizing-chip__value" data-project-summary="consumption">
                    {{ old('monthly_consumption_kwh', $project->monthly_consumption_kwh) ? number_format((float) old('monthly_consumption_kwh', $project->monthly_consumption_kwh), 2, ',', '.') . ' kWh' : 'Aguardando consumo' }}
                </strong>
            </article>

            <article class="solar-sizing-chip">
                <span class="solar-sizing-chip__label">Pronto para proposta</span>
                <strong class="solar-sizing-chip__value">{{ $initialSuggestedPrice && $initialMonthlySavings !== null ? 'Sim' : 'Em preparação' }}</strong>
            </article>
        </div>
    </section>

    <section class="hub-card hub-card--subtle solar-flow-section">
        <div class="solar-flow-section__header">
            <div>
                <p class="solar-section-eyebrow">1. Cliente e local</p>
                <h3>Quem está comprando e onde será instalada a usina?</h3>
            </div>
            <p class="hub-note">Comece pelo cliente e pelo nome interno do projeto para manter o fluxo comercial organizado desde o primeiro contato.</p>
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
                <p class="solar-field-note">Use este campo para marcar em qual etapa comercial o projeto está dentro do funil.</p>
            </div>
        </div>

        <div>
            <label for="name" class="hub-auth-label">Nome do projeto *</label>
            <input id="name" name="name" type="text" class="hub-auth-input" value="{{ old('name', $project->name) }}" required data-project-name>
            @error('name')
                <p class="hub-note">{{ $message }}</p>
            @enderror
        </div>
    </section>

    <section class="hub-card hub-card--subtle solar-flow-section" data-cep-lookup>
        <div class="solar-flow-section__header">
            <div>
                <p class="solar-section-eyebrow">2. Local da instalação</p>
                <h3>Onde o sistema será instalado?</h3>
            </div>
            <p class="hub-note">O CEP ajuda a preencher o endereço e agiliza o contexto comercial do projeto sem exigir digitação manual de tudo.</p>
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
                <p class="hub-note solar-cep-feedback" data-cep-feedback>Digite um CEP válido para preencher rua, bairro, cidade e UF automaticamente.</p>
            </div>

            <div>
                <label for="number" class="hub-auth-label">Número</label>
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
                <label for="property_type" class="hub-auth-label">Tipo de imóvel</label>
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
                <label for="energy_utility_id" class="hub-auth-label">Concessionária</label>
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
                <p class="hub-note solar-utility-feedback" data-utility-feedback>
                    A concessionária será sugerida automaticamente a partir da cidade e da UF, quando disponível.
                </p>
            </div>

            <div>
                <label for="connection_type" class="hub-auth-label">Tipo de conexão</label>
                <select id="connection_type" name="connection_type" class="hub-auth-input">
                    <option value="">Selecione</option>
                    <option value="mono" @selected(old('connection_type', $project->connection_type) === 'mono')>Monofásico</option>
                    <option value="bi" @selected(old('connection_type', $project->connection_type) === 'bi')>Bifásico</option>
                    <option value="tri" @selected(old('connection_type', $project->connection_type) === 'tri')>Trifásico</option>
                </select>
                @error('connection_type')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="solar-flow-section__footnote">
            <strong>Status de localização interna:</strong>
            <span data-geocoding-status>{{ strtoupper($project->geocoding_status ?? 'pending') }}</span>
        </div>
    </section>

    <section class="hub-card hub-card--subtle solar-flow-section">
        <div class="solar-flow-section__header">
            <div>
                <p class="solar-section-eyebrow">3. Consumo energético</p>
                <h3>Qual é a base para o pré-orçamento?</h3>
            </div>
            <p class="hub-note">Com o consumo mensal e o valor da conta, o Solar monta uma leitura inicial de sistema, preço e economia percebida.</p>
        </div>

        <div class="hub-grid hub-grid--billing">
            <div>
                <label for="monthly_consumption_kwh" class="hub-auth-label">Consumo mensal (kWh)</label>
                <input id="monthly_consumption_kwh" name="monthly_consumption_kwh" type="number" step="0.01" min="0" class="hub-auth-input" value="{{ old('monthly_consumption_kwh', $project->monthly_consumption_kwh) }}" data-sizing-monthly>
                @error('monthly_consumption_kwh')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="energy_bill_value" class="hub-auth-label">Valor da conta de energia</label>
                <input id="energy_bill_value" name="energy_bill_value" type="text" inputmode="decimal" class="hub-auth-input" value="{{ old('energy_bill_value', $project->energy_bill_value) }}" data-pricing-energy-bill data-currency-brl>
                @error('energy_bill_value')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
                <p class="solar-field-note">Esse valor alimenta a economia estimada exibida no pré-orçamento.</p>
            </div>
        </div>
    </section>

    <section class="hub-card hub-card--subtle solar-flow-section solar-sizing-panel" data-sizing-form>
        <div class="solar-flow-section__header">
            <div>
                <p class="solar-section-eyebrow">4. Sistema sugerido</p>
                <h3>Pré-dimensionamento rápido para o instalador</h3>
            </div>
            <p class="hub-note">O sistema sugere automaticamente os números principais a partir do consumo e da potência do módulo, mas tudo continua editável manualmente.</p>
        </div>

        <div class="solar-sizing-panel__highlights">
            <article class="solar-sizing-chip">
                <span class="solar-sizing-chip__label">Regra base</span>
                <strong class="solar-sizing-chip__value" data-sizing-preview="formula">Consumo / {{ number_format($resolvedSolarFactor, 2, ',', '.') }}</strong>
            </article>

            <article class="solar-sizing-chip">
                <span class="solar-sizing-chip__label">Módulo padrão</span>
                <strong class="solar-sizing-chip__value" data-sizing-preview="module-power">{{ $defaultModulePower ?: 550 }} W</strong>
            </article>

            <article class="solar-sizing-chip">
                <span class="solar-sizing-chip__label">Fator regional</span>
                <strong class="solar-sizing-chip__value" data-sizing-preview="factor">{{ number_format($resolvedSolarFactor, 2, ',', '.') }} kWh/kWp/mês</strong>
                <span class="solar-sizing-chip__meta">{{ $resolvedSolarFactorSource === 'pvgis' ? 'Fonte PVGIS reutilizada no projeto.' : 'Fallback padrão ativo no projeto.' }}</span>
            </article>

            <article class="solar-sizing-chip solar-sizing-chip--featured">
                <span class="solar-sizing-chip__label">Potência sugerida</span>
                <strong class="solar-sizing-chip__value" data-sizing-preview="system-power">
                    {{ old('system_power_kwp', $project->system_power_kwp) ? number_format((float) old('system_power_kwp', $project->system_power_kwp), 2, ',', '.') . ' kWp' : 'Aguardando consumo' }}
                </strong>
                <span class="solar-sizing-chip__meta">Sugestão automática inicial para acelerar a simulação comercial.</span>
            </article>
        </div>

        <p class="solar-sizing-panel__note" data-sizing-note>Preencha o consumo mensal para gerar a sugestão automática usando o fator regional salvo no projeto. Se necessário, ajuste os valores manualmente antes de salvar.</p>

        <div class="hub-grid hub-grid--billing">
            <div>
                <label for="system_power_kwp" class="hub-auth-label">Potência do sistema (kWp)</label>
                <input id="system_power_kwp" name="system_power_kwp" type="number" step="0.01" min="0" class="hub-auth-input" value="{{ old('system_power_kwp', $project->system_power_kwp) }}" data-sizing-system-power>
                @error('system_power_kwp')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
                <p class="solar-field-note solar-field-note--automatic">O Solar preenche este campo automaticamente com base no consumo, mas você pode ajustar manualmente.</p>
            </div>

            <div>
                <label for="module_power" class="hub-auth-label">Potência do módulo (W)</label>
                <input id="module_power" name="module_power" type="number" step="1" min="1" class="hub-auth-input" value="{{ old('module_power', $project->module_power ?: ($companySetting?->default_module_power ?: 550)) }}" data-sizing-module-power>
                @error('module_power')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
                <p class="solar-field-note">Ao alterar este campo, o Solar recalcula a quantidade de módulos e a geração estimada.</p>
            </div>
        </div>

        <div class="hub-grid hub-grid--billing">
            <div>
                <label for="module_quantity" class="hub-auth-label">Quantidade de módulos</label>
                <input id="module_quantity" name="module_quantity" type="number" step="1" min="1" class="hub-auth-input" value="{{ old('module_quantity', $project->module_quantity) }}" data-sizing-module-quantity>
                @error('module_quantity')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
                <p class="solar-field-note solar-field-note--automatic">Quantidade sugerida automaticamente para apoiar o pré-orçamento, com edição manual liberada.</p>
            </div>

            <div>
                <label for="estimated_generation_kwh" class="hub-auth-label">Geração estimada (kWh)</label>
                <input id="estimated_generation_kwh" name="estimated_generation_kwh" type="number" step="0.01" min="0" class="hub-auth-input" value="{{ old('estimated_generation_kwh', $project->estimated_generation_kwh) }}" data-sizing-generation>
                @error('estimated_generation_kwh')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
                <p class="solar-field-note solar-field-note--automatic">Estimativa inicial para reforçar a leitura comercial do projeto. Pode ser ajustada manualmente.</p>
            </div>
        </div>

        <div>
            <label for="inverter_model" class="hub-auth-label">Modelo do inversor</label>
            <input id="inverter_model" name="inverter_model" type="text" class="hub-auth-input" value="{{ $defaultInverterModel }}" data-sizing-inverter>
            @error('inverter_model')
                <p class="hub-note">{{ $message }}</p>
            @enderror
            <p class="solar-field-note">O modelo padrão da configuração comercial entra automaticamente como ponto de partida.</p>
        </div>
    </section>

    <section class="hub-card hub-card--subtle solar-flow-section solar-pricing-panel">
        <div class="solar-flow-section__header">
            <div>
                <p class="solar-section-eyebrow">5. Pré-orçamento comercial</p>
                <h3>Mostre valor automático antes da proposta final</h3>
            </div>
            <p class="hub-note">O preço sugerido usa a configuração comercial da empresa e pode ser ajustado manualmente antes de salvar.</p>
        </div>

        <div class="solar-sizing-panel__highlights solar-pricing-panel__highlights">
            <article class="solar-sizing-chip">
                <span class="solar-sizing-chip__label">Preço por kWp</span>
                <strong class="solar-sizing-chip__value" data-pricing-preview="rate">
                    {{ 'R$ ' . number_format((float) $effectivePricePerKwp, 2, ',', '.') }}
                </strong>
            </article>

            <article class="solar-sizing-chip solar-sizing-chip--featured">
                <span class="solar-sizing-chip__label">Preço sugerido</span>
                <strong class="solar-sizing-chip__value" data-pricing-preview="total">
                    {{ $initialSuggestedPrice ? 'R$ ' . number_format((float) $initialSuggestedPrice, 2, ',', '.') : 'Aguardando dimensionamento' }}
                </strong>
                <span class="solar-sizing-chip__meta">Valor inicial calculado para acelerar a negociação com o cliente.</span>
            </article>

            <article class="solar-sizing-chip solar-sizing-chip--featured">
                <span class="solar-sizing-chip__label">Economia estimada</span>
                <strong class="solar-sizing-chip__value" data-pricing-preview="savings">
                    {{ $initialMonthlySavings !== null ? 'R$ ' . number_format((float) $initialMonthlySavings, 2, ',', '.') . '/mês' : 'Informe a conta de energia' }}
                </strong>
                <span class="solar-sizing-chip__meta">Leitura comercial simples para mostrar valor percebido logo no primeiro atendimento.</span>
            </article>

            <article class="solar-sizing-chip">
                <span class="solar-sizing-chip__label">Margem de referência</span>
                <strong class="solar-sizing-chip__value" data-pricing-preview="margin">
                    {{ $companySetting?->margin_percent ? number_format((float) $companySetting->margin_percent, 2, ',', '.') . '%' : 'Não configurada' }}
                </strong>
            </article>
        </div>

        <p class="solar-sizing-panel__note solar-pricing-panel__note" data-pricing-note>
            @if ($usesMarketPriceFallback)
                Sem preço próprio configurado, o Solar está usando {{ 'R$ ' . number_format((float) $effectivePricePerKwp, 2, ',', '.') }}/kWp como referência média de mercado.
            @else
                O preço sugerido será recalculado automaticamente quando a potência do sistema mudar. Você ainda pode fazer um ajuste manual antes de salvar.
            @endif
        </p>

        <div class="solar-inline-tip">
            <strong>Leitura comercial rápida:</strong>
            <span> o Solar usa a configuração da empresa para sugerir valor e mantém o campo liberado para ajuste manual antes da proposta.</span>
        </div>

        <div class="hub-grid hub-grid--billing">
            <div>
                <label for="suggested_price" class="hub-auth-label">Preço sugerido (R$)</label>
                <input id="suggested_price" name="suggested_price" type="text" inputmode="decimal" class="hub-auth-input" value="{{ old('suggested_price', $project->suggested_price) }}" data-pricing-suggested-price data-currency-brl>
                @error('suggested_price')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
                <p class="solar-field-note solar-field-note--automatic">Campo preenchido automaticamente pelo Solar, com liberdade para ajuste manual conforme a negociação.</p>
            </div>

            <div>
                <label for="pricing_notes" class="hub-auth-label">Observações comerciais</label>
                <input id="pricing_notes" name="pricing_notes" type="text" class="hub-auth-input" value="{{ old('pricing_notes', $project->pricing_notes) }}">
                @error('pricing_notes')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
                <p class="solar-field-note">Use este campo para registrar contexto de negociação, prazo, condição ou ressalvas do cliente.</p>
            </div>
        </div>
    </section>

    <section class="hub-card hub-card--subtle solar-flow-section solar-financial-panel">
        <div class="solar-flow-section__header">
            <div>
                <p class="solar-section-eyebrow">6. Simulação financeira</p>
                <h3>Mostre a economia estimada de forma imediata</h3>
            </div>
            <p class="hub-note">Regra inicial simples: valor da conta de energia menos custo mínimo residual de {{ 'R$ ' . number_format($residualMinimumCost, 2, ',', '.') }}.</p>
        </div>

        <div class="solar-sizing-panel__highlights solar-financial-panel__highlights">
            <article class="solar-sizing-chip solar-sizing-chip--featured">
                <span class="solar-sizing-chip__label">Economia mensal estimada</span>
                <strong class="solar-sizing-chip__value" data-financial-preview="monthly">
                    {{ $initialMonthlySavings !== null ? 'R$ ' . number_format((float) $initialMonthlySavings, 2, ',', '.') : 'Informe a conta de energia' }}
                </strong>
                <span class="solar-sizing-chip__meta">Considerando um custo mínimo residual de {{ 'R$ ' . number_format($residualMinimumCost, 2, ',', '.') }} por mês.</span>
            </article>

            <article class="solar-sizing-chip">
                <span class="solar-sizing-chip__label">Economia anual</span>
                <strong class="solar-sizing-chip__value" data-financial-preview="annual">
                    {{ $initialAnnualSavings !== null ? 'R$ ' . number_format((float) $initialAnnualSavings, 2, ',', '.') : 'Aguardando simulação' }}
                </strong>
            </article>

            <article class="solar-sizing-chip">
                <span class="solar-sizing-chip__label">Economia em 25 anos</span>
                <strong class="solar-sizing-chip__value" data-financial-preview="lifetime">
                    {{ $initialLifetimeSavings !== null ? 'R$ ' . number_format((float) $initialLifetimeSavings, 2, ',', '.') : 'Aguardando simulação' }}
                </strong>
            </article>
        </div>

        <p class="solar-sizing-panel__note solar-financial-panel__note" data-financial-note>
            @if ($initialMonthlySavings !== null)
                Simulação ativa com base na conta atual de energia. Os valores servem como leitura comercial inicial para o cliente final.
            @else
                Informe o valor da conta de energia para gerar a simulação financeira automática.
            @endif
        </p>
    </section>

    <section class="hub-card hub-card--subtle solar-flow-section">
        <div class="solar-flow-section__header">
            <div>
                <p class="solar-section-eyebrow">7. Observações</p>
                <h3>Registre informações úteis para a próxima etapa</h3>
            </div>
            <p class="hub-note">Use este espaço para anotar detalhes que ajudam o time comercial ou técnico a dar continuidade ao atendimento.</p>
        </div>

        <div>
            <label for="notes" class="hub-auth-label">Observações gerais</label>
            <textarea id="notes" name="notes" class="hub-auth-input" placeholder="Ex.: condição do telhado, expectativa do cliente, prazo de retorno, contexto da simulação...">{{ old('notes', $project->notes) }}</textarea>
            @error('notes')
                <p class="hub-note">{{ $message }}</p>
            @enderror
        </div>
    </section>

    <div class="hub-actions">
        <button type="submit" class="hub-btn">{{ $submitLabel }}</button>
        <a href="{{ route('solar.projects.index') }}" class="hub-btn hub-btn--subtle">Cancelar</a>
    </div>
</div>
