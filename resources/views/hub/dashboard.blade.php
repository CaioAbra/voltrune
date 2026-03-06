@extends('hub.layout')

@section('title', 'Dashboard | Voltrune Hub')

@section('content')
    <h1>Área do Cliente Voltrune</h1>
    <p>
        Este hub centraliza o acesso aos sistemas internos da Voltrune para clientes com compra ativa
        ou assinatura mensal vigente.
    </p>

    <section>
        <h2 class="hub-section-title">Aplicativos da sua assinatura</h2>

        <div class="hub-grid">
            <article class="hub-card">
                <h3>Solar</h3>
                <p>Simulação e orçamento para operações de energia solar.</p>
                <span class="hub-badge">Em breve</span>
            </article>

            <article class="hub-card">
                <h3>Vigilante</h3>
                <p>Automação de fluxos para escritórios jurídicos.</p>
                <span class="hub-badge">Em breve</span>
            </article>

            <article class="hub-card">
                <h3>Agro</h3>
                <p>Análise técnica e recomendação orientada a cultivo.</p>
                <span class="hub-badge">Em breve</span>
            </article>
        </div>
    </section>
@endsection
