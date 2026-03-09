@extends('hub.layout')

@section('title', 'Dashboard | Voltrune Hub')

@section('content')
    <h1>Área do Cliente Voltrune</h1>
    <p>
        Este hub centraliza o acesso aos sistemas internos da Voltrune para clientes com compra ativa
        ou assinatura mensal vigente.
    </p>

    @if ($companyStatus !== 'active')
        <section class="hub-card hub-alert hub-alert--warning">
            @if ($companyStatus === 'suspended')
                <h2 class="hub-section-title">Conta suspensa</h2>
                <p>Sua conta está suspensa. Entre em contato com a equipe Voltrune.</p>
            @else
                <h2 class="hub-section-title">Conta aguardando ativação</h2>
                <p>Sua conta foi criada e está aguardando ativação pela equipe Voltrune.</p>
            @endif
            <div class="hub-actions">
                <a href="{{ route('contato') }}" class="hub-btn">Falar com a Voltrune</a>
                <a href="{{ route('hub.activation-pending') }}" class="hub-btn">Ver detalhes</a>
            </div>
        </section>
    @endif

    @if ($financialStatus === 'overdue')
        <section class="hub-card hub-alert hub-alert--danger">
            <h2 class="hub-section-title">Aviso financeiro</h2>
            <p>Identificamos pendência financeira na sua conta. Entre em contato com a equipe Voltrune.</p>
        </section>
    @endif

    <section>
        <h2 class="hub-section-title">Aplicativos da sua assinatura</h2>

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
                <article class="hub-card">
                    <h3>{{ $productLabel }}</h3>
                    <p>{{ $description }}</p>
                    @if ($isAccessible)
                        <span class="hub-badge">Acesso liberado</span>
                        @if ($productKey === 'solar')
                            <div class="hub-actions">
                                <a href="{{ route('solar.dashboard') }}" class="hub-btn">Acessar Solar</a>
                            </div>
                        @endif
                    @else
                        <span class="hub-badge">{{ $companyStatus === 'suspended' ? 'Suspenso' : 'Bloqueado/Indisponível' }}</span>
                        <button type="button" disabled class="hub-btn-disabled">{{ $companyStatus === 'suspended' ? 'Conta suspensa' : 'Aguardando liberação manual' }}</button>
                    @endif
                </article>
            @endforeach
        </div>
    </section>

    <section>
        <h2 class="hub-section-title">Assinatura e acesso</h2>

        <div class="hub-grid hub-grid--billing">
            <article class="hub-card">
                <h3>Plano atual</h3>
                <p>Founder / Beta</p>
            </article>

            <article class="hub-card">
                <h3>Status da conta</h3>
                <p>{{ strtoupper($companyStatus) }}</p>
            </article>

            <article class="hub-card">
                <h3>Forma de contratação</h3>
                <p>Manual</p>
            </article>

            <article class="hub-card">
                <h3>Renovação</h3>
                <p>Gerenciada pela equipe Voltrune</p>
            </article>
        </div>

        <p class="hub-note">
            Neste momento, alguns acessos são liberados manualmente pela equipe Voltrune. Em breve,
            alguns sistemas também poderão ser contratados diretamente pela plataforma.
        </p>
    </section>
@endsection
