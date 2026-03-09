@extends('solar.layout')

@section('title', 'Solar | Voltrune')

@section('solar-content')
    <section class="hub-grid">
        <article class="hub-card">
            <h2>Clientes</h2>
            <p>Base inicial para cadastro e gestao comercial dos clientes do Solar.</p>
            <a href="{{ route('solar.customers.index') }}" class="hub-btn">Abrir clientes</a>
        </article>

        <article class="hub-card">
            <h2>Simulacoes</h2>
            <p>Espaco reservado para simulacoes tecnicas e comerciais da operacao solar.</p>
            <a href="{{ route('solar.simulations.index') }}" class="hub-btn">Abrir simulacoes</a>
        </article>

        <article class="hub-card">
            <h2>Orcamentos</h2>
            <p>Area inicial para estruturar a geracao e acompanhamento de orcamentos.</p>
            <a href="{{ route('solar.quotes.index') }}" class="hub-btn">Abrir orcamentos</a>
        </article>
    </section>
@endsection
