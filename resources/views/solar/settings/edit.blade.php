@extends('solar.layout')

@section('title', 'Configuracoes comerciais | Voltrune Solar')

@section('solar-content')
    <section class="hub-card solar-settings">
        <h2>Configuracao comercial da empresa</h2>
        <p class="hub-note">Defina os padroes usados pela sua operacao para acelerar o pre-dimensionamento e preparar o calculo comercial do Solar.</p>

        @if (session('solar_status'))
            <div class="hub-alert hub-alert--success solar-flash-alert" role="status" data-flash-alert data-flash-timeout="5000">
                <div class="solar-flash-alert__content">{{ session('solar_status') }}</div>
                <button type="button" class="solar-flash-alert__close" aria-label="Fechar aviso" data-flash-close>&times;</button>
            </div>
        @endif

        @if ($errors->any())
            <div class="hub-alert hub-alert--danger">
                <strong>Revise os campos do formulario.</strong>
            </div>
        @endif

        <form action="{{ route('solar.settings.update') }}" method="post" class="hub-auth-form">
            @csrf
            @method('PUT')

            <div class="hub-grid hub-grid--billing">
                <div>
                    <label for="default_module_power" class="hub-auth-label">Potencia padrao do modulo (W)</label>
                    <input id="default_module_power" name="default_module_power" type="number" min="1" step="1" class="hub-auth-input" value="{{ old('default_module_power', $setting->default_module_power) }}">
                    @error('default_module_power')
                        <p class="hub-note">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="default_inverter_model" class="hub-auth-label">Modelo padrao de inversor</label>
                    <input id="default_inverter_model" name="default_inverter_model" type="text" class="hub-auth-input" value="{{ old('default_inverter_model', $setting->default_inverter_model) }}">
                    @error('default_inverter_model')
                        <p class="hub-note">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="hub-grid hub-grid--billing">
                <div>
                    <label for="price_per_kwp" class="hub-auth-label">Preco por kWp</label>
                    <input id="price_per_kwp" name="price_per_kwp" type="number" min="0" step="0.01" class="hub-auth-input" value="{{ old('price_per_kwp', $setting->price_per_kwp) }}">
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
                <p class="hub-note">Quando esta empresa tiver configuracoes salvas, novos projetos passam a herdar automaticamente a potencia padrao do modulo e o modelo padrao de inversor. O instalador ainda pode ajustar esses campos manualmente em cada projeto.</p>
            </div>

            <div class="hub-actions">
                <button type="submit" class="hub-btn">Salvar configuracoes</button>
                <a href="{{ route('solar.dashboard') }}" class="hub-btn hub-btn--subtle">Voltar ao dashboard</a>
            </div>
        </form>
    </section>
@endsection
