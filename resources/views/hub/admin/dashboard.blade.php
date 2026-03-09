@extends('hub.admin.layout')

@section('title', 'Painel Interno Voltrune | Dashboard')

@section('content')
    <h1>Painel Interno Voltrune</h1>
    <p>Operação de clientes SaaS com foco em contratações, cobrança manual e liberação de acessos.</p>

    <section class="hub-admin-kpis">
        <article class="hub-card hub-kpi-card">
            <p class="hub-kpi-card__label">Empresas pendentes</p>
            <p class="hub-kpi-card__value">{{ $companyMetrics['pending'] }}</p>
            <a href="{{ route('hub.admin.companies.index', ['company_status' => 'pending']) }}" class="hub-btn hub-btn--subtle">Ver pendentes</a>
        </article>

        <article class="hub-card hub-kpi-card">
            <p class="hub-kpi-card__label">Empresas ativas</p>
            <p class="hub-kpi-card__value">{{ $companyMetrics['active'] }}</p>
            <a href="{{ route('hub.admin.companies.index', ['company_status' => 'active']) }}" class="hub-btn hub-btn--subtle">Ver ativas</a>
        </article>

        <article class="hub-card hub-kpi-card">
            <p class="hub-kpi-card__label">Empresas suspensas</p>
            <p class="hub-kpi-card__value">{{ $companyMetrics['suspended'] }}</p>
            <a href="{{ route('hub.admin.companies.index', ['company_status' => 'suspended']) }}" class="hub-btn hub-btn--subtle">Ver suspensas</a>
        </article>

        <article class="hub-card hub-kpi-card">
            <p class="hub-kpi-card__label">Cobrança pendente</p>
            <p class="hub-kpi-card__value">{{ $financialMetrics['pending'] }}</p>
            <a href="{{ route('hub.admin.billing.index', ['financial_status' => 'pending']) }}" class="hub-btn hub-btn--subtle">Ver cobranças pendentes</a>
        </article>

        <article class="hub-card hub-kpi-card">
            <p class="hub-kpi-card__label">Cobrança em atraso</p>
            <p class="hub-kpi-card__value">{{ $financialMetrics['overdue'] }}</p>
            <a href="{{ route('hub.admin.billing.index', ['financial_status' => 'overdue']) }}" class="hub-btn hub-btn--subtle">Ver cobranças em atraso</a>
        </article>
    </section>

    <section class="hub-card">
        <h2>Atalhos operacionais</h2>
        <div class="hub-actions">
            <a href="{{ route('hub.admin.companies.index', ['company_status' => 'pending']) }}" class="hub-btn">Ver clientes pendentes</a>
            <a href="{{ route('hub.admin.billing.index', ['financial_status' => 'pending']) }}" class="hub-btn">Ver cobranças pendentes</a>
            <a href="{{ route('hub.admin.access.index', ['access_state' => 'active']) }}" class="hub-btn">Ver clientes com acesso liberado</a>
            <a href="{{ route('hub.admin.companies.index') }}" class="hub-btn hub-btn--subtle">Carteira completa</a>
        </div>
    </section>
@endsection