@extends('layouts.app')

@section('title', 'Missoes Voltrune')
@section('meta_description', 'Grimorio de missoes da Voltrune com projetos para empresas, profissionais liberais, startups e e-commerce com foco em resultado.')
@section('canonical', route('portfolio'))

@section('content')
<section class="section page-hero portfolio-hero">
    <div class="container narrow">
        <p class="eyebrow">Grimorio de missoes</p>
        <h1>Projetos pensados para converter, escalar e valorizar marcas.</h1>
        <p class="lead">A Voltrune atende negocios de diferentes portes: empresas estabelecidas, profissionais liberais, startups e e-commerce.</p>
    </div>
</section>

<section class="section">
    <div class="container quest-board">
        <div class="filter-row grimoire-filters" role="group" aria-label="Filtros de portfolio">
            <button class="filter-btn is-active" type="button" data-filter="all" aria-pressed="true">Todos</button>
            <button class="filter-btn" type="button" data-filter="site" aria-pressed="false">Sites</button>
            <button class="filter-btn" type="button" data-filter="app" aria-pressed="false">Apps</button>
            <button class="filter-btn" type="button" data-filter="midia" aria-pressed="false">Midia</button>
            <button class="filter-btn" type="button" data-filter="marca" aria-pressed="false">Marca</button>
        </div>

        <div class="portfolio-grid quest-grid" data-portfolio-grid>
            <x-portfolio-card title="Institucional para industria" description="Nova plataforma para captacao B2B com SEO." :tags="['site','seo','empresa']" result="+62% formularios validos" :image="asset('images/folio-1.svg')" seal="Aprovado" />
            <x-portfolio-card title="Aplicativo de atendimento" description="Fluxo digital para profissionais liberais." :tags="['app','automacao']" result="-41% tempo de resposta" :image="asset('images/folio-2.svg')" seal="Concluida" />
            <x-portfolio-card title="Escala de e-commerce" description="Campanhas de performance com tracking completo." :tags="['midia','tracking','ecommerce']" result="ROAS 5.3 em 60 dias" :image="asset('images/folio-3.svg')" />
            <x-portfolio-card title="Identidade para startup" description="Sistema visual premium para rodada de apresentacao." :tags="['marca','startup']" result="Mais clareza de proposta" :image="asset('images/folio-4.svg')" />
            <x-portfolio-card title="Landing para lancamento" description="Pagina orientada a vendas para infoproduto." :tags="['site','midia']" result="Conversao +37%" :image="asset('images/folio-5.svg')" />
            <x-portfolio-card title="Dashboard de operacao" description="Visualizacao de KPI em tempo real para gestao." :tags="['app','dashboard']" result="Decisao mais rapida" :image="asset('images/folio-6.svg')" />
        </div>
    </div>
</section>
@endsection
