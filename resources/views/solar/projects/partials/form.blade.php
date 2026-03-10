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
    $initialSavings = old('energy_bill_value', $project->energy_bill_value);
@endphp

<div
    class="solar-project-flow"
    data-solar-project-form
    data-pricing-per-kwp="{{ old('company_price_per_kwp', $effectivePricePerKwp) }}"
    data-margin-percent="{{ old('company_margin_percent', $companySetting?->margin_percent) }}"
    data-default-inverter-model="{{ $companySetting?->default_inverter_model }}"
    data-pricing-source="{{ $usesMarketPriceFallback ? 'market' : 'custom' }}"
>
    <section class="hub-card hub-card--subtle solar-project-command">
        <div class="solar-project-command__header">
            <div>
                <p class="solar-section-eyebrow">Pre-orcamento comercial</p>
                <h3 data-project-summary-name>{{ old('name', $project->name ?: 'Projeto solar em preparacao') }}</h3>
                <p class="hub-note">Use este fluxo para sair do cadastro tecnico e chegar rapido a uma leitura comercial de potencia, preco e economia estimada.</p>
            </div>

            <div class="solar-project-command__status {{ $usesMarketPriceFallback ? 'is-market' : 'is-ready' }}">
                <span class="solar-project-command__status-label">Configuracao comercial</span>
                <strong>{{ $usesMarketPriceFallback ? 'Usando media de mercado' : 'Preco proprio configurado' }}</strong>
                <p>
                    @if ($usesMarketPriceFallback)
                        O Solar vai usar {{ 'R$ ' . number_format((float) $effectivePricePerKwp, 2, ',', '.') }}/kWp ate a empresa definir um valor proprio.
                    @else
                        Preco por kWp da empresa disponivel para gerar valor sugerido automaticamente.
                    @endif
                </p>
                <a href="{{ route('solar.settings.edit') }}" class="hub-btn hub-btn--subtle">Abrir configuracoes</a>
            </div>
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
                <span class="solar-sizing-chip__label">Status</span>
                <strong class="solar-sizing-chip__value" data-project-summary="status">{{ $statusLabels[$currentStatus] ?? strtoupper((string) $currentStatus) }}</strong>
            </article>

            <article class="solar-sizing-chip">
                <span class="solar-sizing-chip__label">Consumo mensal</span>
                <strong class="solar-sizing-chip__value" data-project-summary="consumption">
                    {{ old('monthly_consumption_kwh', $project->monthly_consumption_kwh) ? number_format((float) old('monthly_consumption_kwh', $project->monthly_consumption_kwh), 2, ',', '.') . ' kWh' : 'Aguardando consumo' }}
                </strong>
            </article>

            <article class="solar-sizing-chip">
                <span class="solar-sizing-chip__label">Potencia sugerida</span>
                <strong class="solar-sizing-chip__value" data-project-summary="power">
                    {{ old('system_power_kwp', $project->system_power_kwp) ? number_format((float) old('system_power_kwp', $project->system_power_kwp), 2, ',', '.') . ' kWp' : 'Aguardando consumo' }}
                </strong>
            </article>

            <article class="solar-sizing-chip">
                <span class="solar-sizing-chip__label">Preco sugerido</span>
                <strong class="solar-sizing-chip__value" data-project-summary="price">
                    {{ $initialSuggestedPrice ? 'R$ ' . number_format((float) $initialSuggestedPrice, 2, ',', '.') : 'Aguardando pre-orcamento' }}
                </strong>
            </article>
        </div>
    </section>

    <section class="hub-card hub-card--subtle solar-flow-section">
        <div class="solar-flow-section__header">
            <div>
                <p class="solar-section-eyebrow">1. Cliente</p>
                <h3>Quem esta comprando este sistema?</h3>
            </div>
            <p class="hub-note">Defina o cliente e o nome interno do projeto para manter o funil comercial organizado.</p>
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
                <label for="status" class="hub-auth-label">Status *</label>
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
                <p class="solar-section-eyebrow">2. Local da instalacao</p>
                <h3>Onde o sistema sera instalado?</h3>
            </div>
            <p class="hub-note">O CEP ajuda a preencher o endereco e prepara o projeto para a proxima camada de geolocalizacao interna.</p>
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
                <p class="hub-note solar-cep-feedback" data-cep-feedback>Digite um CEP valido para preencher rua, bairro, cidade e UF.</p>
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
                <p class="hub-note solar-utility-feedback" data-utility-feedback>
                    A concessionaria sera sugerida automaticamente a partir da cidade e UF quando disponivel.
                </p>
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
            <strong>Status de localizacao interna:</strong>
            <span data-geocoding-status>{{ strtoupper($project->geocoding_status ?? 'pending') }}</span>
        </div>
    </section>

    <section class="hub-card hub-card--subtle solar-flow-section">
        <div class="solar-flow-section__header">
            <div>
                <p class="solar-section-eyebrow">3. Consumo energetico</p>
                <h3>Qual e a base para o pre-orcamento?</h3>
            </div>
            <p class="hub-note">Com consumo mensal e valor de conta, o Solar monta uma leitura inicial de sistema, preco e economia percebida.</p>
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
            </div>
        </div>
    </section>

    <section class="hub-card hub-card--subtle solar-flow-section solar-sizing-panel" data-sizing-form>
        <div class="solar-flow-section__header">
            <div>
                <p class="solar-section-eyebrow">4. Sistema sugerido</p>
                <h3>Pre-dimensionamento rapido para o instalador</h3>
            </div>
            <p class="hub-note">A automacao parte do consumo mensal e do modulo selecionado, mas todos os campos continuam editaveis manualmente.</p>
        </div>

        <div class="solar-sizing-panel__highlights">
            <article class="solar-sizing-chip">
                <span class="solar-sizing-chip__label">Regra base</span>
                <strong class="solar-sizing-chip__value" data-sizing-preview="formula">Consumo / 130</strong>
            </article>

            <article class="solar-sizing-chip">
                <span class="solar-sizing-chip__label">Modulo padrao</span>
                <strong class="solar-sizing-chip__value" data-sizing-preview="module-power">{{ $defaultModulePower ?: 550 }} W</strong>
            </article>

            <article class="solar-sizing-chip">
                <span class="solar-sizing-chip__label">Potencia sugerida</span>
                <strong class="solar-sizing-chip__value" data-sizing-preview="system-power">
                    {{ old('system_power_kwp', $project->system_power_kwp) ? number_format((float) old('system_power_kwp', $project->system_power_kwp), 2, ',', '.') . ' kWp' : 'Aguardando consumo' }}
                </strong>
            </article>
        </div>

        <p class="solar-sizing-panel__note" data-sizing-note>Preencha o consumo mensal para gerar a sugestao automatica.</p>

        <div class="hub-grid hub-grid--billing">
            <div>
                <label for="system_power_kwp" class="hub-auth-label">Potencia do sistema (kWp)</label>
                <input id="system_power_kwp" name="system_power_kwp" type="number" step="0.01" min="0" class="hub-auth-input" value="{{ old('system_power_kwp', $project->system_power_kwp) }}" data-sizing-system-power>
                @error('system_power_kwp')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="module_power" class="hub-auth-label">Potencia do modulo (W)</label>
                <input id="module_power" name="module_power" type="number" step="1" min="1" class="hub-auth-input" value="{{ old('module_power', $project->module_power ?: ($companySetting?->default_module_power ?: 550)) }}" data-sizing-module-power>
                @error('module_power')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="hub-grid hub-grid--billing">
            <div>
                <label for="module_quantity" class="hub-auth-label">Quantidade de modulos</label>
                <input id="module_quantity" name="module_quantity" type="number" step="1" min="1" class="hub-auth-input" value="{{ old('module_quantity', $project->module_quantity) }}" data-sizing-module-quantity>
                @error('module_quantity')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="estimated_generation_kwh" class="hub-auth-label">Geracao estimada (kWh)</label>
                <input id="estimated_generation_kwh" name="estimated_generation_kwh" type="number" step="0.01" min="0" class="hub-auth-input" value="{{ old('estimated_generation_kwh', $project->estimated_generation_kwh) }}" data-sizing-generation>
                @error('estimated_generation_kwh')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="inverter_model" class="hub-auth-label">Modelo do inversor</label>
            <input id="inverter_model" name="inverter_model" type="text" class="hub-auth-input" value="{{ $defaultInverterModel }}" data-sizing-inverter>
            @error('inverter_model')
                <p class="hub-note">{{ $message }}</p>
            @enderror
        </div>
    </section>

    <section class="hub-card hub-card--subtle solar-flow-section solar-pricing-panel">
        <div class="solar-flow-section__header">
            <div>
                <p class="solar-section-eyebrow">5. Pre-orcamento comercial</p>
                <h3>Mostre valor automatico antes da proposta final</h3>
            </div>
            <p class="hub-note">O preco sugerido usa a configuracao comercial da empresa e pode ser ajustado manualmente antes de salvar.</p>
        </div>

        <div class="solar-sizing-panel__highlights solar-pricing-panel__highlights">
            <article class="solar-sizing-chip">
                <span class="solar-sizing-chip__label">Preco por kWp</span>
                <strong class="solar-sizing-chip__value" data-pricing-preview="rate">
                    {{ 'R$ ' . number_format((float) $effectivePricePerKwp, 2, ',', '.') }}
                </strong>
            </article>

            <article class="solar-sizing-chip">
                <span class="solar-sizing-chip__label">Preco sugerido</span>
                <strong class="solar-sizing-chip__value" data-pricing-preview="total">
                    {{ $initialSuggestedPrice ? 'R$ ' . number_format((float) $initialSuggestedPrice, 2, ',', '.') : 'Aguardando dimensionamento' }}
                </strong>
            </article>

            <article class="solar-sizing-chip">
                <span class="solar-sizing-chip__label">Economia estimada</span>
                <strong class="solar-sizing-chip__value" data-pricing-preview="savings">
                    {{ $initialSavings ? 'R$ ' . number_format((float) $initialSavings, 2, ',', '.') . '/mes' : 'Informe a conta de energia' }}
                </strong>
            </article>

            <article class="solar-sizing-chip">
                <span class="solar-sizing-chip__label">Margem de referencia</span>
                <strong class="solar-sizing-chip__value" data-pricing-preview="margin">
                    {{ $companySetting?->margin_percent ? number_format((float) $companySetting->margin_percent, 2, ',', '.') . '%' : 'Nao configurada' }}
                </strong>
            </article>
        </div>

        <p class="solar-sizing-panel__note solar-pricing-panel__note" data-pricing-note>
            @if ($usesMarketPriceFallback)
                Sem preco proprio configurado, o Solar esta usando {{ 'R$ ' . number_format((float) $effectivePricePerKwp, 2, ',', '.') }}/kWp como referencia media de mercado.
            @else
                O preco sugerido sera recalculado automaticamente quando a potencia do sistema mudar.
            @endif
        </p>

        <div class="hub-grid hub-grid--billing">
            <div>
                <label for="suggested_price" class="hub-auth-label">Preco sugerido (R$)</label>
                <input id="suggested_price" name="suggested_price" type="text" inputmode="decimal" class="hub-auth-input" value="{{ old('suggested_price', $project->suggested_price) }}" data-pricing-suggested-price data-currency-brl>
                @error('suggested_price')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="pricing_notes" class="hub-auth-label">Observacoes comerciais</label>
                <input id="pricing_notes" name="pricing_notes" type="text" class="hub-auth-input" value="{{ old('pricing_notes', $project->pricing_notes) }}">
                @error('pricing_notes')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="notes" class="hub-auth-label">Observacoes</label>
            <textarea id="notes" name="notes" class="hub-auth-input">{{ old('notes', $project->notes) }}</textarea>
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
