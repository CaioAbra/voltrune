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
        <h1>Uma estrutura completa para construir, vender e escalar com criterio.</h1>
        <p class="lead">Cada frente nasce com rastreamento confiavel para que decisao, verba e evolucao sejam guiadas por dado real.</p>
    </div>
</section>

<section class="section">
    <div class="container narrow service-longform">
        <article>
            <h2>Websites e Landings</h2>
            <h3>SEO, performance e design orientado a conversao</h3>
            <p>Criamos paginas rapidas, com estrutura semantica, narrativa comercial e funis pensados para gerar contato qualificado.</p>
        </article>

        <article>
            <h2>Apps e Dashboards</h2>
            <h3>Processos internos e automacoes sob medida</h3>
            <p>Desenvolvemos sistemas web para centralizar informacao, reduzir retrabalho e dar escala a operacoes criticas.</p>
        </article>

        <article>
            <h2>Trafego Pago e Midia</h2>
            <h3>Setup, criativos e otimizacao para ROI</h3>
            <p>Estruturamos campanhas em Meta e Google com segmentacao, teste e leitura de desempenho baseada em rastreamento confiavel.</p>
        </article>

        <article>
            <h2>Marca, banner e logo</h2>
            <h3>Identidade premium para gerar confianca imediata</h3>
            <p>Construimos sistemas visuais com coerencia estrategica para sustentar posicionamento e elevar percepcao de valor.</p>
        </article>

        <article>
            <h2>Hospedagem e manutencao</h2>
            <h3>Infraestrutura e continuidade sem gargalos</h3>
            <p>Cuidamos de hospedagem, monitoramento, atualizacoes e ajustes tecnicos recorrentes para manter sua operacao estavel.</p>
            <a class="btn" href="{{ route('portal') }}">Ver hospedagem</a>
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
                <div class="faq-answer-inner">
                    <p>Sim. Ajustamos a execucao para empresas, especialistas, startups e operacoes com maturidade comercial distinta.</p>
                </div>
            </div>
        </details>
        <details class="faq-item">
            <summary>
                <span>Como funciona o tracking?</span>
                <span class="material-symbols-rounded faq-icon" aria-hidden="true">expand_more</span>
            </summary>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Configuramos GA4, Pixel, eventos, tags e metas para acompanhar o funil do clique ate a conversao.</p>
                </div>
            </div>
        </details>
        <details class="faq-item">
            <summary>
                <span>O projeto ja inclui SEO?</span>
                <span class="material-symbols-rounded faq-icon" aria-hidden="true">expand_more</span>
            </summary>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Sim. SEO tecnico e on-page entram no escopo base de websites e landings.</p>
                </div>
            </div>
        </details>
        <details class="faq-item">
            <summary>
                <span>Qual o prazo medio?</span>
                <span class="material-symbols-rounded faq-icon" aria-hidden="true">expand_more</span>
            </summary>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Depende do escopo. Em geral, de 2 a 12 semanas.</p>
                </div>
            </div>
        </details>
        <details class="faq-item">
            <summary>
                <span>Posso contratar por etapas?</span>
                <span class="material-symbols-rounded faq-icon" aria-hidden="true">expand_more</span>
            </summary>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Sim. Podemos fatiar a execucao em etapas, priorizando o que gera impacto mais cedo.</p>
                </div>
            </div>
        </details>
    </div>
</section>

<div class="mobile-sticky-cta" data-sticky-cta>
    <a href="{{ env('WHATSAPP_URL', 'https://wa.me/5511998479359') }}" target="_blank" rel="noopener">WhatsApp</a>
    <a href="{{ route('contato') }}">Briefing</a>
</div>
@endsection
