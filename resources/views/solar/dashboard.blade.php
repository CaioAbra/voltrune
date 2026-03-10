@extends('solar.layout')

@section('title', 'Solar | Voltrune')

@section('solar-content')
    <section class="hub-grid">
        <article class="hub-card">
            <h2>Projetos</h2>
            <p>Organize os locais de instalação por cliente, endereço, consumo e contexto técnico da operação.</p>
            <a href="{{ route('solar.projects.index') }}" class="hub-btn">Abrir projetos</a>
        </article>

        <article class="hub-card">
            <h2>Clientes</h2>
            <p>Base comercial dos clientes do Solar para iniciar propostas com mais agilidade.</p>
            <a href="{{ route('solar.customers.index') }}" class="hub-btn">Abrir clientes</a>
        </article>

        <article class="hub-card">
            <h2>Simulações</h2>
            <p>Espaço reservado para evoluir simulações técnicas e comerciais da operação solar.</p>
            <a href="{{ route('solar.simulations.index') }}" class="hub-btn">Abrir simulações</a>
        </article>

        <article class="hub-card">
            <h2>Orçamentos</h2>
            <p>Área inicial para estruturar geração, acompanhamento e fechamento comercial dos orçamentos.</p>
            <a href="{{ route('solar.quotes.index') }}" class="hub-btn">Abrir orçamentos</a>
        </article>
    </section>
@endsection
