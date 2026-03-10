<div>
    <label for="solar_customer_id" class="hub-auth-label">Cliente contratante *</label>
    <select id="solar_customer_id" name="solar_customer_id" class="hub-auth-input" required>
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
    <input id="name" name="name" type="text" class="hub-auth-input" value="{{ old('name', $project->name) }}" required>
    @error('name')
        <p class="hub-note">{{ $message }}</p>
    @enderror
</div>

<div class="hub-card hub-card--subtle" data-cep-lookup>
    <h3>Local da instalacao</h3>
    <p class="hub-note">Informe o CEP para tentar preencher o endereco automaticamente. Se a busca falhar, voce pode completar os campos manualmente.</p>
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
        <input id="city" name="city" type="text" class="hub-auth-input" value="{{ old('city', $project->city) }}" data-cep-city>
        @error('city')
            <p class="hub-note">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="state" class="hub-auth-label">UF</label>
        <input id="state" name="state" type="text" class="hub-auth-input" value="{{ old('state', $project->state) }}" maxlength="2" data-cep-state>
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

<div class="hub-grid hub-grid--billing">
    <div>
        <label for="status" class="hub-auth-label">Status *</label>
        <select id="status" name="status" class="hub-auth-input" required>
            @foreach (['draft' => 'Draft', 'qualified' => 'Qualificado', 'proposal' => 'Proposta', 'won' => 'Fechado'] as $statusValue => $statusLabel)
                <option value="{{ $statusValue }}" @selected(old('status', $project->status ?: 'draft') === $statusValue)>
                    {{ $statusLabel }}
                </option>
            @endforeach
        </select>
        @error('status')
            <p class="hub-note">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="hub-card hub-card--subtle">
    <h3>Consumo energetico</h3>
    <p class="hub-note">Esses dados alimentam a futura simulacao solar e uma estimativa inicial de potencia necessaria.</p>
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
        <input id="energy_bill_value" name="energy_bill_value" type="number" step="0.01" min="0" class="hub-auth-input" value="{{ old('energy_bill_value', $project->energy_bill_value) }}">
        @error('energy_bill_value')
            <p class="hub-note">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="hub-card hub-card--subtle solar-sizing-panel" data-sizing-form>
    <h3>Sistema sugerido</h3>
    <p class="hub-note">O sistema aplica um pre-dimensionamento automatico com base no consumo mensal. O instalador pode ajustar tudo manualmente antes de salvar.</p>

    <div class="solar-sizing-panel__highlights">
        <article class="solar-sizing-chip">
            <span class="solar-sizing-chip__label">Regra base</span>
            <strong class="solar-sizing-chip__value" data-sizing-preview="formula">Consumo / 130</strong>
        </article>

        <article class="solar-sizing-chip">
            <span class="solar-sizing-chip__label">Modulo padrao</span>
            <strong class="solar-sizing-chip__value" data-sizing-preview="module-power">550 W</strong>
        </article>

        <article class="solar-sizing-chip">
            <span class="solar-sizing-chip__label">Potencia sugerida</span>
            <strong class="solar-sizing-chip__value" data-sizing-preview="system-power">Aguardando consumo</strong>
        </article>
    </div>

    <p class="solar-sizing-panel__note" data-sizing-note>Preencha o consumo mensal para gerar a sugestao automatica.</p>
</div>

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
        <input id="module_power" name="module_power" type="number" step="1" min="1" class="hub-auth-input" value="{{ old('module_power', $project->module_power ?: 550) }}" data-sizing-module-power>
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
    <input id="inverter_model" name="inverter_model" type="text" class="hub-auth-input" value="{{ old('inverter_model', $project->inverter_model) }}">
    @error('inverter_model')
        <p class="hub-note">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="notes" class="hub-auth-label">Observacoes</label>
    <textarea id="notes" name="notes" class="hub-auth-input">{{ old('notes', $project->notes) }}</textarea>
    @error('notes')
        <p class="hub-note">{{ $message }}</p>
    @enderror
</div>

<div class="hub-card hub-card--subtle">
    <h3>Localizacao interna</h3>
    <p class="hub-note">
        Coordenadas, rua, bairro, cidade e UF serao preenchidos internamente pelo sistema a partir do CEP e do endereco completo.
        Status atual: <strong data-geocoding-status>{{ strtoupper($project->geocoding_status ?? 'pending') }}</strong>
    </p>
</div>

<div class="hub-actions">
    <button type="submit" class="hub-btn">{{ $submitLabel }}</button>
    <a href="{{ route('solar.projects.index') }}" class="hub-btn hub-btn--subtle">Cancelar</a>
</div>
