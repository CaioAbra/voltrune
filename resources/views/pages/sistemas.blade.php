@extends('layouts.app')

@section('title', 'Sistemas Voltrune para operações e gestão')
@section('meta_description', 'Conheça os sistemas da Voltrune para operações especializadas. Solar para energia solar e Vigilante para fluxos jurídicos.')
@section('canonical', route('sistemas'))

@section('content')
<section class="section page-hero">
    <div class="container narrow">
        <p class="eyebrow">Ecossistema da ordem Voltrune</p>
        <h1>Sistemas para escalar operações com controle real.</h1>
        <p class="lead">Aqui ficam os produtos proprietários da Voltrune. Hoje, a linha já contempla Solar para operações de energia solar e Vigilante para o contexto jurídico.</p>
    </div>
</section>

<section class="section section-alt">
    <div class="container systems-grid">
        <article class="system-card is-live">
            <span class="system-badge">Ativo no ecossistema</span>
            <h2>Solar</h2>
            <p>Gestão comercial e operacional para empresas de energia solar, com estrutura para clientes, projetos, simulações e orçamento.</p>
            <div class="system-card__actions">
                <form method="POST" action="{{ route('contato.prefill') }}">
                    @csrf
                    <input type="hidden" name="subject" value="Voltrune Solar">
                    <button class="btn" type="submit">Falar sobre o Solar</button>
                </form>
                <a class="btn btn-ghost" href="{{ route('contato') }}">Enviar briefing</a>
            </div>
        </article>

        <article class="system-card is-live">
            <span class="system-badge">Em desenvolvimento</span>
            <h2>Vigilante</h2>
            <p>Sistema pensado para rotina jurídica, controle de prazos e organização operacional com menos atrito.</p>
            <div class="system-card__actions">
                <a class="btn" href="{{ route('vigilante') }}">Conhecer o Vigilante</a>
                <a class="btn btn-ghost" href="{{ route('sistemas.vigilante') }}">Abrir página do módulo</a>
            </div>
        </article>
    </div>
</section>

<section class="section">
    <div class="container narrow launch-queue-panel">
        <div>
            <p class="eyebrow">Fila de novos lançamentos</p>
            <h2>Quer acompanhar os próximos produtos da Voltrune antes da abertura pública?</h2>
            <p>Entrando na fila, você sinaliza interesse antecipado e ajuda a priorizar novas frentes do ecossistema.</p>
        </div>
        <div class="launch-queue-panel__actions">
            <form method="POST" action="{{ route('contato.prefill') }}">
                @csrf
                <input type="hidden" name="subject" value="Fila de Novos Lançamentos">
                <button class="btn" type="submit">Entrar na fila</button>
            </form>
            <a class="btn btn-ghost" href="{{ route('contato') }}">Falar com a Voltrune</a>
        </div>
    </div>
</section>
@endsection
