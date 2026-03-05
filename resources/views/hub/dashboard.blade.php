@extends('hub.layout')

@section('title', 'Dashboard | Voltrune Hub')

@section('content')
    <h1>Bem-vindo ao Voltrune Hub</h1>
    <p>
        O Hub é o painel central onde você acessa todos os sistemas da Voltrune.
    </p>

    <section>
        <h2 class="hub-section-title">Seus sistemas</h2>

        <div class="hub-grid">
            <article class="hub-card">
                <h3>Solar</h3>
                <p>Simulação e orçamento de energia solar</p>
                <span class="hub-badge">Em breve</span>
            </article>

            <article class="hub-card">
                <h3>Vigilante</h3>
                <p>Automação para escritórios jurídicos</p>
                <span class="hub-badge">Em breve</span>
            </article>

            <article class="hub-card">
                <h3>Agro</h3>
                <p>Análise e recomendação de cultivo</p>
                <span class="hub-badge">Em breve</span>
            </article>
        </div>
    </section>
@endsection
