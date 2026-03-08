@extends('hub.admin.layout')

@section('title', 'Painel Interno Voltrune')

@section('content')
    <h1>Painel Interno Voltrune</h1>
    <p>Operação de clientes SaaS e controle de contratação e cobrança manual.</p>

    <div class="hub-grid hub-grid--billing">
        <article class="hub-card">
            <h2>Empresas pendentes</h2>
            <p><strong>{{ $companyMetrics['pending'] }}</strong></p>
        </article>

        <article class="hub-card">
            <h2>Empresas ativas</h2>
            <p><strong>{{ $companyMetrics['active'] }}</strong></p>
        </article>

        <article class="hub-card">
            <h2>Empresas suspensas</h2>
            <p><strong>{{ $companyMetrics['suspended'] }}</strong></p>
        </article>

        <article class="hub-card">
            <h2>Financeiro pendente</h2>
            <p><strong>{{ $financialMetrics['pending'] }}</strong></p>
        </article>

        <article class="hub-card">
            <h2>Financeiro em atraso</h2>
            <p><strong>{{ $financialMetrics['overdue'] }}</strong></p>
        </article>
    </div>

    <div class="hub-card">
        <h2>Atalhos operacionais</h2>
        <div class="hub-actions">
            <a href="{{ route('hub.admin.companies.index') }}" class="hub-btn">Carteira de clientes</a>
            <a href="{{ route('hub.admin.contracts.index') }}" class="hub-btn">Contratações</a>
            <a href="{{ route('hub.admin.billing.index') }}" class="hub-btn">Cobranças</a>
            <a href="{{ route('hub.admin.access.index') }}" class="hub-btn">Acessos</a>
        </div>
    </div>
@endsection
