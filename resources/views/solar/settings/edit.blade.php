@extends('solar.layout')

@section('title', 'Configuracoes comerciais | Voltrune Solar')

@section('solar-content')
    <section class="hub-card solar-settings">
        <h2>Configuração comercial da empresa</h2>
        <p class="hub-note">Defina os padrões usados pela sua operação para acelerar o pré-dimensionamento e preparar o cálculo comercial do Solar.</p>

        @if (session('solar_status'))
            <div class="hub-alert hub-alert--success solar-flash-alert" role="status" data-flash-alert data-flash-timeout="5000">
                <div class="solar-flash-alert__content">{{ session('solar_status') }}</div>
                <button type="button" class="solar-flash-alert__close" aria-label="Fechar aviso" data-flash-close>&times;</button>
            </div>
        @endif

        @if ($errors->any())
            <div class="hub-alert hub-alert--danger">
                <strong>Revise os campos do formulário.</strong>
            </div>
        @endif

        <form action="{{ route('solar.settings.update') }}" method="post" class="hub-auth-form">
            @csrf
            @method('PUT')

            <div class="hub-grid hub-grid--billing">
                <div>
                    <label for="default_module_power" class="hub-auth-label">Potência padrão do módulo (W)</label>
                    <input id="default_module_power" name="default_module_power" type="number" min="1" step="1" class="hub-auth-input" value="{{ old('default_module_power', $setting->default_module_power) }}">
                    @error('default_module_power')
                        <p class="hub-note">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="default_inverter_model" class="hub-auth-label">Modelo padrão de inversor</label>
                    <input id="default_inverter_model" name="default_inverter_model" type="text" class="hub-auth-input" value="{{ old('default_inverter_model', $setting->default_inverter_model) }}">
                    @error('default_inverter_model')
                        <p class="hub-note">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="hub-grid hub-grid--billing">
                <div>
                    <label for="price_per_kwp" class="hub-auth-label">Preço médio por kWp (opcional)</label>
                    <input id="price_per_kwp" name="price_per_kwp" type="text" inputmode="decimal" class="hub-auth-input" value="{{ old('price_per_kwp', $setting->price_per_kwp) }}" data-market-price-input data-currency-brl>
                    <p class="hub-note">Se não informado, o Solar usará um valor médio de mercado para gerar pré-orçamentos.</p>
                    <div class="solar-settings-field-actions">
                        <button type="button" class="hub-btn hub-btn--subtle" data-market-price-fill="{{ $marketPricePerKwp }}">Usar valor médio de mercado</button>
                    </div>
                    @error('price_per_kwp')
                        <p class="hub-note">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="margin_percent" class="hub-auth-label">Margem desejada (%)</label>
                    <input id="margin_percent" name="margin_percent" type="number" min="0" step="0.01" class="hub-auth-input" value="{{ old('margin_percent', $setting->margin_percent) }}">
                    @error('margin_percent')
                        <p class="hub-note">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="hub-card hub-card--subtle">
                <h3>Como o Solar usa esses dados</h3>
                <p class="hub-note">Quando esta empresa tiver configurações salvas, novos projetos passam a herdar automaticamente a potência padrão do módulo e o modelo padrão de inversor. Se o preço por kWp não estiver preenchido, o Solar usa {{ 'R$ ' . number_format((float) $marketPricePerKwp, 2, ',', '.') }}/kWp como referência inicial. O instalador ainda pode ajustar esses campos manualmente em cada projeto.</p>
            </div>

            <div class="hub-actions">
                <button type="submit" class="hub-btn">Salvar configurações</button>
                <a href="{{ route('solar.dashboard') }}" class="hub-btn hub-btn--subtle">Voltar ao dashboard</a>
            </div>
        </form>
    </section>
@endsection
