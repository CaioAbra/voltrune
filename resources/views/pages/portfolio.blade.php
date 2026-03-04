@extends('layouts.app')

@section('title', 'Portfólio Voltrune | Sites, Apps e Mídia')
@section('meta_description', 'Portfólio da Voltrune com projetos de sites, apps, mídia e branding para empresas, especialistas, startups e e-commerce.')
@section('canonical', route('portfolio'))

@section('content')
<section class="section page-hero portfolio-hero">
    <div class="container narrow">
        <p class="eyebrow">Portfólio | Grimório de missões</p>
        <h1>Projetos pensados para converter, escalar e valorizar marcas.</h1>
        <p class="lead">Cases desenhados para diferentes momentos de negócio, de operações consolidadas a marcas em expansão.</p>
    </div>
</section>

<section class="section">
    <div class="container quest-board">
        <div class="filter-row grimoire-filters" role="group" aria-label="Filtros do portfólio">
            <button class="filter-btn is-active" type="button" data-filter="all" aria-pressed="true">Todos</button>
            <button class="filter-btn" type="button" data-filter="site" aria-pressed="false">Sites</button>
            <button class="filter-btn" type="button" data-filter="app" aria-pressed="false">Apps</button>
            <button class="filter-btn" type="button" data-filter="midia" aria-pressed="false">Mídia</button>
            <button class="filter-btn" type="button" data-filter="marca" aria-pressed="false">Marca</button>
        </div>

        <div class="portfolio-grid quest-grid" data-portfolio-grid>
            <x-portfolio-card title="Institucional para indústria" description="Nova plataforma para captação B2B com SEO." :tags="['site','seo','empresa']" result="+62% formulários válidos" :image="asset('images/folio-1.svg')" seal="Aprovado" />
            <x-portfolio-card title="Aplicativo de atendimento" description="Fluxo digital para profissionais liberais." :tags="['app','automacao']" result="-41% tempo de resposta" :image="asset('images/folio-2.svg')" seal="Concluída" />
            <x-portfolio-card title="Escala de e-commerce" description="Campanhas de performance com tracking completo." :tags="['midia','tracking','ecommerce']" result="ROAS 5.3 em 60 dias" :image="asset('images/folio-3.svg')" />
            <x-portfolio-card title="Identidade para startup" description="Sistema visual premium para rodada de apresentação." :tags="['marca','startup']" result="Mais clareza de proposta" :image="asset('images/folio-4.svg')" />
            <x-portfolio-card title="Landing para lançamento" description="Página orientada a vendas para infoproduto." :tags="['site','midia']" result="Conversão +37%" :image="asset('images/folio-5.svg')" />
            <x-portfolio-card title="Dashboard de operação" description="Visualização de KPI em tempo real para gestão." :tags="['app','dashboard']" result="Decisão mais rápida" :image="asset('images/folio-6.svg')" />
        </div>
    </div>
</section>
@endsection
