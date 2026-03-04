@extends('layouts.app')

@section('title', 'Serviços de sites, apps, SEO e mídia')
@section('meta_description', 'Conheça os serviços da Voltrune: sites, landing pages, apps, tráfego pago, branding, tracking, hospedagem e manutenção com foco comercial.')
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
            'name' => 'Tráfego Pago e Mídia',
            'provider' => ['@type' => 'Organization', 'name' => 'Voltrune'],
            'areaServed' => 'BR',
            'serviceType' => 'Gestão de mídia digital',
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
        <p class="eyebrow">Serviços da ordem Voltrune</p>
        <h1>Uma estrutura completa para construir, vender e escalar com critério.</h1>
        <p class="lead">Cada frente nasce com rastreamento confiável para que decisões, verba e evolução sejam guiadas por dados reais.</p>
    </div>
</section>

<section class="section">
    <div class="container narrow service-longform">
        <article>
            <h2>Websites e Landings</h2>
            <h3>SEO, performance e design orientado à conversão</h3>
            <p>Criamos páginas rápidas, com estrutura semântica, narrativa comercial e funis pensados para gerar demanda qualificada.</p>
        </article>

        <article>
            <h2>Apps e Dashboards</h2>
            <h3>Processos internos e automações sob medida</h3>
            <p>Desenvolvemos sistemas web para centralizar informação, reduzir retrabalho e dar escala a operações críticas.</p>
        </article>

        <article>
            <h2>Tráfego Pago e Mídia</h2>
            <h3>Setup, criativos e otimização para ROI</h3>
            <p>Estruturamos campanhas em Meta e Google com segmentação, testes e leitura de desempenho baseada em rastreamento confiável.</p>
        </article>

        <article>
            <h2>Marca, banner e logo</h2>
            <h3>Identidade premium para gerar confiança imediata</h3>
            <p>Construímos sistemas visuais com coerência estratégica para sustentar posicionamento e elevar percepção de valor.</p>
        </article>

        <article>
            <h2>Hospedagem e manutenção</h2>
            <h3>Infraestrutura e continuidade sem gargalos</h3>
            <p>Cuidamos de hospedagem, monitoramento, atualizações e ajustes técnicos recorrentes para manter sua operação estável.</p>
            <a class="btn" href="{{ route('portal') }}">Ver hospedagem</a>
        </article>
    </div>
</section>

<section class="section section-alt">
    <div class="container narrow faq-list" data-faq-group>
        <h2>Perguntas frequentes</h2>
        <details class="faq-item" open>
            <summary>
                <span>A Voltrune atende qualquer nicho?</span>
                <span class="material-symbols-rounded faq-icon" aria-hidden="true">expand_more</span>
            </summary>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Sim. Ajustamos a execução para empresas, especialistas, startups e operações em diferentes níveis de maturidade comercial.</p>
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
                    <p>Configuramos GA4, Pixel, eventos, tags e metas para acompanhar o funil completo, do clique até a conversão.</p>
                </div>
            </div>
        </details>
        <details class="faq-item">
            <summary>
                <span>O projeto já inclui SEO?</span>
                <span class="material-symbols-rounded faq-icon" aria-hidden="true">expand_more</span>
            </summary>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Sim. SEO técnico e on-page entram no escopo base de websites e landings.</p>
                </div>
            </div>
        </details>
        <details class="faq-item">
            <summary>
                <span>Qual é o prazo médio?</span>
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
                    <p>Sim. Podemos fatiar a execução em etapas, priorizando o que gera impacto mais cedo.</p>
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
