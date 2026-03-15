@extends('hub.layout')

@section('title', 'Sistemas | Voltrune Hub')

@section('content')
    <h1>Sistemas</h1>
    <p>Ative e acompanhe os módulos incluídos no seu plano SaaS.</p>

    @if ($companyStatus !== 'active')
        <div class="hub-card hub-alert hub-alert--warning">
            @if ($companyStatus === 'suspended')
                <p><strong>Sua conta está suspensa. Entre em contato com a equipe Voltrune.</strong></p>
                <p>Os acessos aos sistemas permanecem bloqueados enquanto o status estiver suspenso.</p>
            @else
                <p><strong>Sua conta foi criada e está aguardando ativação pela equipe Voltrune.</strong></p>
                <p>Os acessos aos sistemas permanecem bloqueados até o status da empresa ficar como <code>active</code>.</p>
            @endif
            <a href="{{ route('contato') }}" class="hub-btn">Falar com a Voltrune</a>
        </div>
    @endif

    @if ($financialStatus === 'overdue')
        <div class="hub-card hub-alert hub-alert--danger">
            <p><strong>Status financeiro em atraso.</strong> Regularize com a equipe Voltrune para evitar bloqueios adicionais.</p>
            <a href="{{ route('contato') }}" class="hub-btn">Falar com a Voltrune</a>
        </div>
    @endif

    @php
        $catalog = [
            'solar' => 'Simulação e orçamento para operações de energia solar.',
            'vigilante' => 'Automação de fluxos para escritórios jurídicos.',
            'agro' => 'Análise técnica e recomendação orientada a cultivo.',
        ];
    @endphp

    <div class="hub-grid">
        @foreach ($catalog as $productKey => $description)
            @php
                $isAccessible = (bool) ($productAccess[$productKey] ?? false);
                $productLabel = $productLabels[$productKey] ?? strtoupper($productKey);
            @endphp
            <article class="hub-card hub-product-card">
                <h2>{{ $productLabel }}</h2>
                <p>{{ $description }}</p>
                <p class="hub-note">Disponibilidade gerenciada manualmente pela equipe Voltrune</p>
                @if ($isAccessible)
                    <span class="hub-badge">Acesso liberado</span>
                    @if ($productKey === 'solar')
                        <a href="{{ route('solar.dashboard') }}" class="hub-btn" title="Liberado">Acessar Solar</a>
                    @else
                        <button type="button" class="hub-btn" title="Liberado">Acessível</button>
                    @endif
                @else
                    <button type="button" disabled class="hub-btn-disabled" title="Bloqueado">
                        {{ $companyStatus === 'suspended' ? 'Conta suspensa' : 'Bloqueado ou indisponível' }}
                    </button>
                @endif
            </article>
        @endforeach
    </div>
@endsection
