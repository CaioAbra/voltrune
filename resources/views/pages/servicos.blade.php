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

    $services = [
        [
            'id' => 'websites',
            'eyebrow' => 'Aquisição e presença digital',
            'title' => 'Websites e Landings',
            'subtitle' => 'SEO, performance e design orientado à conversão',
            'description' => 'Criamos páginas rápidas, com estrutura semântica, narrativa comercial e funis pensados para gerar demanda qualificada.',
            'fit' => 'Ideal para validar oferta, captar leads e melhorar presença comercial.',
            'points' => [
                'Arquitetura com foco em clareza, velocidade e indexação.',
                'Copy e UX desenhadas para reduzir atrito na tomada de decisão.',
                'Tracking com GA4, Pixel e eventos desde a publicação.',
            ],
            'subject' => 'Websites e Landings',
        ],
        [
            'id' => 'apps',
            'eyebrow' => 'Operação e eficiência',
            'title' => 'Apps e Dashboards',
            'subtitle' => 'Processos internos e automações sob medida',
            'description' => 'Desenvolvemos sistemas web para centralizar informação, reduzir retrabalho e dar escala a operações críticas.',
            'fit' => 'Ideal para equipes com gargalos manuais, retrabalho ou pouca visibilidade.',
            'points' => [
                'Mapeamento de fluxo, permissões e regras da operação real.',
                'Painéis e rotinas que simplificam acompanhamento e execução.',
                'Base pronta para evoluções futuras sem depender de remendos.',
            ],
            'subject' => 'Apps e Dashboards',
        ],
        [
            'id' => 'trafego',
            'eyebrow' => 'Escala e previsibilidade',
            'title' => 'Tráfego Pago e Mídia',
            'subtitle' => 'Setup, criativos e otimização para ROI',
            'description' => 'Estruturamos campanhas em Meta e Google com segmentação, testes e leitura de desempenho baseada em rastreamento confiável.',
            'fit' => 'Ideal para operações que já vendem e querem crescer com mais consistência.',
            'points' => [
                'Setup técnico de contas, eventos e públicos prioritários.',
                'Criativos e testes pensados para aprendizado rápido.',
                'Leitura semanal de CAC, qualidade do lead e retorno.',
            ],
            'subject' => 'Tráfego Pago e Mídia',
        ],
        [
            'id' => 'marca',
            'eyebrow' => 'Percepção e autoridade',
            'title' => 'Marca, banner e logo',
            'subtitle' => 'Identidade premium para gerar confiança imediata',
            'description' => 'Construímos sistemas visuais com coerência estratégica para sustentar posicionamento e elevar percepção de valor.',
            'fit' => 'Ideal para reposicionamento, lançamento ou ajuste de percepção de mercado.',
            'points' => [
                'Direção visual alinhada ao perfil de cliente e ticket desejado.',
                'Aplicações que mantêm consistência entre site, mídia e materiais.',
                'Entrega prática para uso comercial sem depender de improviso.',
            ],
            'subject' => 'Marca, Banner e Logo',
        ],
        [
            'id' => 'hospedagem',
            'eyebrow' => 'Continuidade e estabilidade',
            'title' => 'Hospedagem e manutenção',
            'subtitle' => 'Infraestrutura e continuidade sem gargalos',
            'description' => 'Cuidamos de hospedagem, monitoramento, atualizações e ajustes técnicos recorrentes para manter sua operação estável.',
            'fit' => 'Ideal para quem precisa de sustentação técnica sem absorver isso internamente.',
            'points' => [
                'Ambiente preparado para performance, segurança e disponibilidade.',
                'Monitoramento, correções recorrentes e continuidade operacional.',
                'Base organizada para novas campanhas, páginas e integrações.',
            ],
            'subject' => 'Hospedagem e Manutenção',
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
        <div class="hero-actions">
            <a class="btn" href="{{ route('contato') }}">Solicitar briefing</a>
            <a class="btn btn-ghost" href="#mapa-servicos">Ver mapa de serviços</a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container narrow">
        <div class="service-overview" id="mapa-servicos" aria-label="Mapa de serviços">
            @foreach ($services as $service)
                <a class="service-overview-card" href="#{{ $service['id'] }}">
                    <span class="service-overview-card__eyebrow">{{ $service['eyebrow'] }}</span>
                    <strong>{{ $service['title'] }}</strong>
                    <span>{{ $service['subtitle'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
</section>

<section class="section">
    <div class="container narrow service-longform">
        @foreach ($services as $service)
            <article id="{{ $service['id'] }}">
                <div class="service-entry-head">
                    <div>
                        <p class="service-kicker">{{ $service['eyebrow'] }}</p>
                        <h2>{{ $service['title'] }}</h2>
                        <h3>{{ $service['subtitle'] }}</h3>
                    </div>
                    <p class="service-fit">{{ $service['fit'] }}</p>
                </div>

                <p>{{ $service['description'] }}</p>

                <ul class="service-points">
                    @foreach ($service['points'] as $point)
                        <li>{{ $point }}</li>
                    @endforeach
                </ul>

                <div class="service-entry-actions">
                    @if ($service['id'] === 'hospedagem')
                        <a class="btn" href="{{ route('portal') }}">Ver hospedagem</a>
                    @else
                        <form method="POST" action="{{ route('contato.prefill') }}">
                            @csrf
                            <input type="hidden" name="subject" value="{{ $service['subject'] }}">
                            <button class="btn" type="submit">Solicitar proposta</button>
                        </form>
                    @endif

                    <a class="text-link" href="{{ route('contato') }}">Falar com a Voltrune</a>
                </div>
            </article>
        @endforeach
    </div>
</section>

<section class="section section-alt">
    <div class="container narrow service-cta-panel">
        <div>
            <p class="eyebrow">Escolha com clareza</p>
            <h2>Se a dúvida é por onde começar, a Voltrune ajuda a priorizar.</h2>
            <p>Organizamos o escopo por impacto, prazo e dependências para você não contratar frentes desconectadas nem pular etapas críticas.</p>
        </div>
        <div class="service-cta-panel__actions">
            <a class="btn" href="{{ env('WHATSAPP_URL', 'https://wa.me/5511998479359') }}" target="_blank" rel="noopener">Chamar no WhatsApp</a>
            <a class="btn btn-ghost" href="{{ route('contato') }}">Enviar briefing</a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <div>
                <p class="eyebrow">Produtos próprios</p>
                <h2>Além dos serviços, a Voltrune também evolui sistemas com foco setorial.</h2>
            </div>
            <a class="text-link" href="{{ route('sistemas') }}">Ver linha de sistemas</a>
        </div>

        <div class="systems-grid">
            <article class="system-card is-live">
                <span class="system-badge">Solar</span>
                <h3>Voltrune Solar</h3>
                <p>Fluxo SaaS para gestão comercial e operacional de energia solar, com clientes, projetos, simulações e orçamento na mesma base.</p>
                <div class="system-card__actions">
                    <form method="POST" action="{{ route('contato.prefill') }}">
                        @csrf
                        <input type="hidden" name="subject" value="Voltrune Solar">
                        <button class="btn" type="submit">Falar sobre o Solar</button>
                    </form>
                    <a class="btn btn-ghost" href="{{ route('sistemas') }}">Ver sistemas</a>
                </div>
            </article>

            <article class="system-card">
                <span class="system-badge">Vigilante</span>
                <h3>Vigilante Jurídico</h3>
                <p>Sistema em preparação para rotinas jurídicas, com foco em controle, organização operacional e menos atrito no acompanhamento de prazos.</p>
                <div class="system-card__actions">
                    <a class="btn" href="{{ route('vigilante') }}">Conhecer o Vigilante</a>
                    <a class="btn btn-ghost" href="{{ route('sistemas') }}">Ver sistemas</a>
                </div>
            </article>
        </div>

        <div class="launch-queue-inline">
            <p>Também pode entrar na fila de novos lançamentos para acompanhar as próximas frentes da Voltrune.</p>
            <form method="POST" action="{{ route('contato.prefill') }}">
                @csrf
                <input type="hidden" name="subject" value="Fila de Novos Lançamentos">
                <button class="btn btn-ghost" type="submit">Entrar na fila de lançamentos</button>
            </form>
        </div>
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
