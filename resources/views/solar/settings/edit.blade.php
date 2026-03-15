@extends('solar.layout')

@section('title', 'Configuracoes comerciais | Voltrune Solar')

@php
    $marginMode = old('margin_mode', $setting->margin_mode ?: \App\Modules\Solar\Models\SolarCompanySetting::MARGIN_MODE_FIXED);
    $marginRanges = old('margin_ranges');

    if ($marginRanges === null) {
        $marginRanges = $setting->marginRanges
            ->map(fn ($range) => [
                'min_kwp' => $range->min_kwp,
                'max_kwp' => $range->max_kwp,
                'margin_percent' => $range->margin_percent,
                'requires_negotiation' => (bool) $range->requires_negotiation,
            ])
            ->values()
            ->all();
    }

    if ($marginRanges === [] && $marginMode === \App\Modules\Solar\Models\SolarCompanySetting::MARGIN_MODE_RANGE) {
        $marginRanges = [[
            'min_kwp' => null,
            'max_kwp' => null,
            'margin_percent' => null,
            'requires_negotiation' => false,
        ]];
    }
@endphp

@section('solar-content')
    <section class="solar-page-shell">
        <section class="hub-card hub-card--subtle solar-page-intro">
            <div class="solar-page-intro__header">
                <div class="solar-page-intro__copy">
                    <p class="solar-section-eyebrow">Configuracoes</p>
                    <h2>Base comercial usada nos orcamentos</h2>
                    <p class="hub-note">Defina os padroes da sua operacao para acelerar a leitura inicial do orcamento sem perder flexibilidade na negociacao.</p>
                </div>

                <div class="solar-page-intro__meta">
                    <span class="solar-project-showcase__status-label">Impacto</span>
                    <strong>Esses dados afetam novos projetos</strong>
                    <p>Potencia padrao, inversor base, preco por kWp e a regra de margem alimentam a leitura inicial dos proximos orcamentos.</p>
                </div>
            </div>
        </section>

        <section class="hub-card solar-page-panel solar-settings">
            <div class="solar-page-panel__header">
                <h2>Configuracao comercial da empresa</h2>
                <p class="hub-note">Ajuste somente o que sua operacao realmente usa no dia a dia para que o vendedor ganhe velocidade sem perder controle.</p>
            </div>

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
                        <label for="price_per_kwp" class="hub-auth-label">Preco medio por kWp (opcional)</label>
                        <input id="price_per_kwp" name="price_per_kwp" type="text" inputmode="decimal" class="hub-auth-input" value="{{ old('price_per_kwp', $setting->price_per_kwp) }}" data-market-price-input data-currency-brl>
                        <p class="hub-note">Se nao informado, o Solar usa primeiro a media regional por UF e, se necessario, o fallback nacional para gerar o preco sugerido.</p>
                        <div class="solar-settings-field-actions">
                            <button type="button" class="hub-btn hub-btn--subtle" data-market-price-fill="{{ $marketPricePerKwp }}">Usar fallback nacional</button>
                        </div>
                        @error('price_per_kwp')
                            <p class="hub-note">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="solar-settings-mode-card" data-solar-margin-settings>
                        <span class="hub-auth-label">Modo de margem</span>
                        <div class="solar-settings-mode-options">
                            <label class="solar-settings-mode-option" data-margin-mode-option>
                                <input type="radio" name="margin_mode" value="fixed" @checked($marginMode === \App\Modules\Solar\Models\SolarCompanySetting::MARGIN_MODE_FIXED)>
                                <span class="solar-settings-mode-option__content">
                                    <strong>Fixa</strong>
                                    <small>Uma unica margem para qualquer potencia.</small>
                                </span>
                            </label>

                            <label class="solar-settings-mode-option" data-margin-mode-option>
                                <input type="radio" name="margin_mode" value="range" @checked($marginMode === \App\Modules\Solar\Models\SolarCompanySetting::MARGIN_MODE_RANGE)>
                                <span class="solar-settings-mode-option__content">
                                    <strong>Por faixa</strong>
                                    <small>Margem muda conforme o sistema em kWp.</small>
                                </span>
                            </label>
                        </div>
                        <p class="hub-note">Escolha entre margem unica ou leitura comercial por faixa de potencia.</p>
                        @error('margin_mode')
                            <p class="hub-note">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="solar-settings-margin-panel" data-margin-mode-panel="fixed" @if ($marginMode !== \App\Modules\Solar\Models\SolarCompanySetting::MARGIN_MODE_FIXED) hidden @endif>
                    <div>
                        <label for="margin_percent" class="hub-auth-label">Margem desejada (%)</label>
                        <input id="margin_percent" name="margin_percent" type="number" min="0" step="0.01" class="hub-auth-input" value="{{ old('margin_percent', $setting->margin_percent) }}">
                        <p class="hub-note">Use este modo quando a operacao trabalha a mesma margem para qualquer sistema.</p>
                        @error('margin_percent')
                            <p class="hub-note">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="solar-settings-margin-panel solar-settings-margin-panel--ranges" data-margin-mode-panel="range" @if ($marginMode !== \App\Modules\Solar\Models\SolarCompanySetting::MARGIN_MODE_RANGE) hidden @endif>
                    <div class="solar-page-panel__header">
                        <h2>Faixas por potencia</h2>
                        <p class="hub-note">Defina a margem conforme a potencia do sistema. Se deixar o kWp final vazio, a faixa passa a valer para qualquer potencia acima do minimo informado.</p>
                    </div>

                    @error('margin_ranges')
                        <p class="hub-note">{{ $message }}</p>
                    @enderror

                    <div class="solar-settings-range-list" data-margin-ranges-list>
                        @foreach ($marginRanges as $index => $range)
                            <div class="solar-settings-range-row" data-margin-range-item>
                                <div>
                                    <label class="hub-auth-label" for="margin_ranges_{{ $index }}_min_kwp">kWp inicial</label>
                                    <input
                                        id="margin_ranges_{{ $index }}_min_kwp"
                                        name="margin_ranges[{{ $index }}][min_kwp]"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="hub-auth-input"
                                        value="{{ $range['min_kwp'] }}"
                                    >
                                    @error("margin_ranges.$index.min_kwp")
                                        <p class="hub-note">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="hub-auth-label" for="margin_ranges_{{ $index }}_max_kwp">kWp final</label>
                                    <input
                                        id="margin_ranges_{{ $index }}_max_kwp"
                                        name="margin_ranges[{{ $index }}][max_kwp]"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="hub-auth-input"
                                        value="{{ $range['max_kwp'] }}"
                                    >
                                    @error("margin_ranges.$index.max_kwp")
                                        <p class="hub-note">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="hub-auth-label" for="margin_ranges_{{ $index }}_margin_percent">Margem (%)</label>
                                    <input
                                        id="margin_ranges_{{ $index }}_margin_percent"
                                        name="margin_ranges[{{ $index }}][margin_percent]"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="hub-auth-input"
                                        value="{{ $range['margin_percent'] }}"
                                        data-margin-range-margin-input
                                        @disabled(! empty($range['requires_negotiation']))
                                    >
                                    @error("margin_ranges.$index.margin_percent")
                                        <p class="hub-note">{{ $message }}</p>
                                    @enderror
                                </div>

                                <label class="solar-settings-range-check">
                                    <input
                                        type="checkbox"
                                        name="margin_ranges[{{ $index }}][requires_negotiation]"
                                        value="1"
                                        data-margin-range-negotiation
                                        @checked(! empty($range['requires_negotiation']))
                                    >
                                    <span>Obrigar negociacao manual nesta faixa</span>
                                </label>

                                <div class="solar-settings-range-actions">
                                    <button type="button" class="hub-btn hub-btn--subtle" data-margin-range-remove>Remover faixa</button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <template data-margin-range-template>
                        <div class="solar-settings-range-row" data-margin-range-item>
                            <div>
                                <label class="hub-auth-label" for="margin_ranges___INDEX___min_kwp">kWp inicial</label>
                                <input id="margin_ranges___INDEX___min_kwp" name="margin_ranges[__INDEX__][min_kwp]" type="number" min="0" step="0.01" class="hub-auth-input">
                            </div>

                            <div>
                                <label class="hub-auth-label" for="margin_ranges___INDEX___max_kwp">kWp final</label>
                                <input id="margin_ranges___INDEX___max_kwp" name="margin_ranges[__INDEX__][max_kwp]" type="number" min="0" step="0.01" class="hub-auth-input">
                            </div>

                            <div>
                                <label class="hub-auth-label" for="margin_ranges___INDEX___margin_percent">Margem (%)</label>
                                <input id="margin_ranges___INDEX___margin_percent" name="margin_ranges[__INDEX__][margin_percent]" type="number" min="0" step="0.01" class="hub-auth-input" data-margin-range-margin-input>
                            </div>

                            <label class="solar-settings-range-check">
                                <input type="checkbox" name="margin_ranges[__INDEX__][requires_negotiation]" value="1" data-margin-range-negotiation>
                                <span>Obrigar negociacao manual nesta faixa</span>
                            </label>

                            <div class="solar-settings-range-actions">
                                <button type="button" class="hub-btn hub-btn--subtle" data-margin-range-remove>Remover faixa</button>
                            </div>
                        </div>
                    </template>

                    <div class="solar-settings-field-actions">
                        <button type="button" class="hub-btn hub-btn--subtle" data-margin-range-add>Adicionar faixa</button>
                    </div>
                </div>

                <div class="hub-card hub-card--subtle">
                    <div class="solar-page-panel__header">
                        <h2>Como o Solar usa esses dados</h2>
                        <p class="hub-note">Novos projetos herdam automaticamente a potencia padrao do modulo e o modelo base de inversor. Para preco por kWp, a prioridade e: valor da empresa, media regional por UF e depois fallback nacional de {{ 'R$ ' . number_format((float) $marketPricePerKwp, 2, ',', '.') }}/kWp. A margem pode ser fixa ou resolvida por faixa de potencia conforme a configuracao escolhida.</p>
                    </div>
                </div>

                <div class="hub-actions">
                    <button type="submit" class="hub-btn">Salvar configuracoes</button>
                    <a href="{{ route('solar.dashboard') }}" class="hub-btn hub-btn--subtle">Voltar ao dashboard</a>
                </div>
            </form>
        </section>
    </section>
@endsection
