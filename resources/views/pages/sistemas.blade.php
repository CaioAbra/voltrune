@extends('layouts.app')

@section('title', 'Sistemas Voltrune')
@section('meta_description', 'Conheca os sistemas da Voltrune. Hoje: Vigilante para operacoes juridicas. Em breve: novos produtos para nichos especificos.')
@section('canonical', route('sistemas'))

@section('content')
<section class="section page-hero">
    <div class="container narrow">
        <p class="eyebrow">Ecossistema Voltrune</p>
        <h1>Sistemas para escalar operacoes com controle real.</h1>
        <p class="lead">Esta area concentra os produtos da Voltrune. O Vigilante e o primeiro modulo, focado em apoiar escritorios e equipes juridicas.</p>
    </div>
</section>

<section class="section section-alt">
    <div class="container systems-grid">
        <article class="system-card is-live">
            <span class="system-badge">Em desenvolvimento</span>
            <h2>Vigilante</h2>
            <p>Sistema para rotinas juridicas, acompanhamento de prazos e organizacao operacional para advogados.</p>
            <div class="hero-actions">
                <a class="btn" href="{{ route('vigilante') }}">Ver landing</a>
                <a class="btn btn-ghost" href="{{ route('sistemas.vigilante') }}">Rota de sistema</a>
            </div>
        </article>

        <article class="system-card">
            <span class="system-badge">Proximo slot</span>
            <h2>Novo sistema (em breve)</h2>
            <p>Estrutura pronta para receber novos produtos por nicho sem alterar a arquitetura principal do site.</p>
            <a class="btn btn-ghost" href="{{ route('contato') }}">Quero participar da lista</a>
        </article>
    </div>
</section>
@endsection
