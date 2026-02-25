@extends('layouts.app')

@section('title', 'Servicos de desenvolvimento, SEO e midia')
@section('meta_description', 'Conheca os servicos da Voltrune: websites, apps, trafego pago, branding, tracking, hospedagem e manutencao com foco comercial.')
@section('canonical', route('servicos'))

@php
    $serviceSchemas = [
        [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => 'Websites e Landings com SEO',
            'provider' => ['@type' => 'Organization', 'name' => 'Voltrune'],
            'areaServed' => 'BR',
            'serviceType' => 'Desenvolvimento web',
        ],
        [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => 'Apps e Dashboards',
            'provider' => ['@type' => 'Organization', 'name' => 'Voltrune'],
            'areaServed' => 'BR',
            'serviceType' => 'Desenvolvimento de sistemas',
        ],
        [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => 'Trafego Pago e Midia',
            'provider' => ['@type' => 'Organization', 'name' => 'Voltrune'],
            'areaServed' => 'BR',
            'serviceType' => 'Gestao de midia digital',
        ],
        [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => 'Marca, Banner e Logo',
            'provider' => ['@type' => 'Organization', 'name' => 'Voltrune'],
            'areaServed' => 'BR',
            'serviceType' => 'Branding e design',
        ],
    ];
@endphp

@push('structured-data')
@foreach ($serviceSchemas as $schema)
<script type="application/ld+json">
@json($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
</script>
@endforeach
@endpush

@section('content')
<section class="section page-hero">
    <div class="container narrow">
        <p class="eyebrow">Servicos Voltrune</p>
        <h1>Um esquadrao completo para construir, vender e escalar no digital.</h1>
        <p class="lead">Cada entrega inclui tracking completo com GA4, Meta Pixel, eventos e tags para tomar decisao com dados reais.</p>
    </div>
</section>

<section class="section">
    <div class="container narrow service-longform">
        <article>
            <h2>Websites e Landings</h2>
            <h3>SEO, performance e design orientado a conversao</h3>
            <p>Criamos paginas rapidas, com estrutura semantica, copy estrategica e funis pensados para gerar contato e venda.</p>
        </article>

        <article>
            <h2>Apps e Dashboards</h2>
            <h3>Processos internos e automacoes sob medida</h3>
            <p>Desenvolvemos sistemas web para centralizar dados, reduzir trabalho manual e acelerar operacoes comerciais.</p>
        </article>

        <article>
            <h2>Trafego Pago e Midia</h2>
            <h3>Setup, criativos e otimizacao para ROI</h3>
            <p>Planejamos campanhas em Meta e Google, com segmentacao, testes e leitura de desempenho com rastreamento confiavel.</p>
        </article>

        <article>
            <h2>Marca, banner e logo</h2>
            <h3>Identidade premium para gerar confianca imediata</h3>
            <p>Construimos sistemas visuais com coerencia estrategica para reforcar posicionamento e aumentar percepcao de valor.</p>
        </article>

        <article>
            <h2>Hospedagem e manutencao</h2>
            <h3>Infraestrutura e continuidade sem gargalos</h3>
            <p>Cuidamos de hospedagem, monitoramento, atualizacoes e ajustes tecnicos recorrentes para manter seu projeto estavel.</p>
            <a class="btn" href="{{ route('portal') }}">Contratar Hospedagem</a>
        </article>
    </div>
</section>

<section class="section section-alt">
    <div class="container narrow faq-list" data-faq-group>
        <h2>Perguntas frequentes</h2>
        <details class="faq-item" open>
            <summary>
                <span>Voce atende qualquer nicho?</span>
                <span class="material-symbols-rounded faq-icon" aria-hidden="true">expand_more</span>
            </summary>
            <div class="faq-answer">
                <p>Sim. Estruturamos estrategia para empresas, profissionais liberais, startups e operacoes de e-commerce.</p>
            </div>
        </details>
        <details class="faq-item">
            <summary>
                <span>Como funciona o tracking?</span>
                <span class="material-symbols-rounded faq-icon" aria-hidden="true">expand_more</span>
            </summary>
            <div class="faq-answer">
                <p>Configuramos GA4, Pixel, eventos, tags e metas para acompanhar funil completo.</p>
            </div>
        </details>
        <details class="faq-item">
            <summary>
                <span>O projeto ja inclui SEO?</span>
                <span class="material-symbols-rounded faq-icon" aria-hidden="true">expand_more</span>
            </summary>
            <div class="faq-answer">
                <p>Sim. SEO tecnico e on-page entram no escopo base de websites e landings.</p>
            </div>
        </details>
        <details class="faq-item">
            <summary>
                <span>Qual o prazo medio?</span>
                <span class="material-symbols-rounded faq-icon" aria-hidden="true">expand_more</span>
            </summary>
            <div class="faq-answer">
                <p>Depende do escopo. Em geral, de 2 a 12 semanas.</p>
            </div>
        </details>
        <details class="faq-item">
            <summary>
                <span>Posso contratar por etapas?</span>
                <span class="material-symbols-rounded faq-icon" aria-hidden="true">expand_more</span>
            </summary>
            <div class="faq-answer">
                <p>Sim. Montamos uma trilha de entregas priorizando impacto comercial.</p>
            </div>
        </details>
    </div>
</section>

<div class="mobile-sticky-cta" data-sticky-cta>
    <a href="{{ env('WHATSAPP_URL', 'https://wa.me/5511998479359') }}" target="_blank" rel="noopener">WhatsApp</a>
    <a href="{{ route('contato') }}">Contato</a>
</div>
@endsection
