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
        <label for="utility_company" class="hub-auth-label">Concessionaria</label>
        <input id="utility_company" name="utility_company" type="text" class="hub-auth-input" value="{{ old('utility_company', $project->utility_company) }}">
        @error('utility_company')
            <p class="hub-note">{{ $message }}</p>
        @enderror
    </div>

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

<div class="hub-grid hub-grid--billing">
    <div>
        <label for="monthly_consumption_kwh" class="hub-auth-label">Consumo mensal (kWh)</label>
        <input id="monthly_consumption_kwh" name="monthly_consumption_kwh" type="number" step="0.01" min="0" class="hub-auth-input" value="{{ old('monthly_consumption_kwh', $project->monthly_consumption_kwh) }}">
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
