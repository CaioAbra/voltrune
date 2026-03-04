@extends('layouts.app')

@section('title', 'Sistemas Voltrune para operações e gestão')
@section('meta_description', 'Conheça os sistemas da Voltrune. Hoje: Vigilante para operações jurídicas. Em breve: novos produtos para nichos específicos.')
@section('canonical', route('sistemas'))

@section('content')
<section class="section page-hero">
    <div class="container narrow">
        <p class="eyebrow">Ecossistema da ordem Voltrune</p>
        <h1>Sistemas para escalar operações com controle real.</h1>
        <p class="lead">Aqui ficam os produtos proprietários da Voltrune. O Vigilante inaugura essa linha com foco em operações jurídicas.</p>
    </div>
</section>

<section class="section section-alt">
    <div class="container systems-grid">
        <article class="system-card is-live">
            <span class="system-badge">Em desenvolvimento</span>
            <h2>Vigilante</h2>
            <p>Sistema pensado para rotina jurídica, controle de prazos e organização operacional com menos atrito.</p>
            <div class="hero-actions">
                <a class="btn" href="{{ route('vigilante') }}">Conhecer o Vigilante</a>
                <a class="btn btn-ghost" href="{{ route('sistemas.vigilante') }}">Abrir página do módulo</a>
            </div>
        </article>

        <article class="system-card">
            <span class="system-badge">Próximo slot</span>
            <h2>Novo sistema (em breve)</h2>
            <p>A estrutura já está pronta para novos produtos por nicho, sem depender de rework na base do site.</p>
            <a class="btn btn-ghost" href="{{ route('contato') }}">Entrar na lista</a>
        </article>
    </div>
</section>
@endsection
