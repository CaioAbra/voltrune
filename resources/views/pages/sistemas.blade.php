@extends('layouts.app')

@section('title', 'Sistemas Voltrune')
@section('meta_description', 'Conheca os sistemas da Voltrune. Hoje: Vigilante para operacoes juridicas. Em breve: novos produtos para nichos especificos.')
@section('canonical', route('sistemas'))

@section('content')
<section class="section page-hero">
    <div class="container narrow">
        <p class="eyebrow">Ecossistema Voltrune</p>
        <h1>Sistemas para escalar operacoes com controle real.</h1>
        <p class="lead">Aqui ficam os produtos proprietarios da Voltrune. O Vigilante inaugura essa linha com foco em operacoes juridicas.</p>
    </div>
</section>

<section class="section section-alt">
    <div class="container systems-grid">
        <article class="system-card is-live">
            <span class="system-badge">Em desenvolvimento</span>
            <h2>Vigilante</h2>
            <p>Sistema pensado para rotina juridica, controle de prazos e organizacao operacional com menos friccao.</p>
            <div class="hero-actions">
                <a class="btn" href="{{ route('vigilante') }}">Conhecer o Vigilante</a>
                <a class="btn btn-ghost" href="{{ route('sistemas.vigilante') }}">Abrir pagina do modulo</a>
            </div>
        </article>

        <article class="system-card">
            <span class="system-badge">Proximo slot</span>
            <h2>Novo sistema (em breve)</h2>
            <p>A estrutura ja esta pronta para novos produtos por nicho, sem depender de rework na base do site.</p>
            <a class="btn btn-ghost" href="{{ route('contato') }}">Entrar na lista</a>
        </article>
    </div>
</section>
@endsection
