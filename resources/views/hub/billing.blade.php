@extends('hub.layout')

@section('title', 'Billing e Assinatura | Voltrune Hub')

@section('content')
    <h1>Billing / Assinatura</h1>
    <p>Visão inicial de cobrança e acesso para preparar os modelos manual e self-service.</p>

    <section class="hub-card">
        <h2>Seu plano</h2>
        <p><strong>Plano:</strong> Founder / Beta</p>
        <p><strong>Status:</strong> Ativa</p>
        <p>Seu acesso atual é liberado e gerenciado pela equipe Voltrune, com acompanhamento comercial.</p>
    </section>

    <section class="hub-card">
        <h2>Formas de contratação</h2>
        <div class="hub-grid hub-grid--billing-options">
            <article class="hub-card">
                <h3>Contratação via contato</h3>
                <p>Ideal para planos personalizados, implantação assistida ou negociação comercial.</p>
            </article>

            <article class="hub-card">
                <h3>Contratação pela plataforma</h3>
                <p>Em breve, alguns sistemas poderão ser assinados diretamente pelo Hub com pagamento online.</p>
            </article>
        </div>
    </section>

    <section class="hub-card">
        <h2>Próximos passos</h2>
        <div class="hub-actions">
            <a href="{{ route('contato') }}" class="hub-btn">Falar com a Voltrune</a>
            <a href="{{ route('hub.products') }}" class="hub-btn">Ver sistemas</a>
            <button type="button" disabled class="hub-btn-disabled" title="Em breve">Upgrade (Em breve)</button>
        </div>
    </section>
@endsection

