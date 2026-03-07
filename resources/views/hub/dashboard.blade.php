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

    <section>
        <h2 class="hub-section-title">Assinatura e acesso</h2>

        <div class="hub-grid hub-grid--billing">
            <article class="hub-card">
                <h3>Plano atual</h3>
                <p>Founder / Beta</p>
            </article>

            <article class="hub-card">
                <h3>Status da conta</h3>
                <p>Ativa</p>
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
