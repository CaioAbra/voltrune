@extends('layouts.app')

@section('title', 'Sites, Apps e Midia para crescer')
@section('meta_description', 'A Voltrune desenvolve websites, apps e estrategias de trafego com design premium, performance tecnica e SEO para gerar demanda real.')
@section('canonical', route('home'))

@section('content')
<section class="hero section home-hero">
    <div class="container hero-grid">
        <div>
            <p class="eyebrow">Ordem de artesaos digitais</p>
            <h1>Construa uma presenca que vende: site, app, trafego e marca no mesmo plano.</h1>
            <p class="lead">Executamos projetos com visao estrategica, design premium e entregas objetivas para acelerar sua receita.</p>
            <div class="hero-actions">
                <a class="btn" href="{{ env('WHATSAPP_URL', 'https://wa.me/5511998479359') }}" target="_blank" rel="noopener">Falar no WhatsApp</a>
                <a class="btn btn-ghost" href="{{ route('servicos') }}">Ver servicos</a>
            </div>
            <div class="badge-row">
                <span>Missao disponivel</span>
                <span>Resposta rapida</span>
            </div>
        </div>

        <aside class="mission-card">
            <h2>Carta de Missao</h2>
            <ul>
                <li>Performance tecnica e SEO desde o primeiro deploy.</li>
                <li>Visual premium para posicionamento de valor alto.</li>
                <li>Execucao clara, sem enrolacao e com metas objetivas.</li>
            </ul>
        </aside>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2>Como funciona</h2>
        <div class="steps-grid">
            <article>
                <span>01</span>
                <h3>Diagnostico</h3>
                <p>Mapeamos seu cenario, objetivos comerciais e gargalos de aquisicao.</p>
            </article>
            <article>
                <span>02</span>
                <h3>Execucao</h3>
                <p>Desenvolvemos assets digitais com padrao tecnico e foco em conversao.</p>
            </article>
            <article>
                <span>03</span>
                <h3>Entrega e evolucao</h3>
                <p>Entregamos com rastreamento ativo e plano de melhoria continua.</p>
            </article>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <h2>Servicos da ordem</h2>
        <div class="services-grid">
            <x-service-card
                title="Websites e Landings"
                icon="language"
                excerpt="Paginas de alta conversao com SEO, performance e tracking completo."
                modal="modal-websites"
            />
            <x-service-card
                title="Apps e Dashboards"
                icon="dashboard"
                excerpt="Sistemas internos, paineis e automacoes para escalar operacao."
                modal="modal-apps"
            />
            <x-service-card
                title="Trafego Pago e Midia"
                icon="ads_click"
                excerpt="Estrategia, criativos e otimizacao para melhorar ROI."
                modal="modal-trafego"
            />
            <x-service-card
                title="Marca, Banner e Logo"
                icon="palette"
                excerpt="Identidade visual para elevar percepcao e credibilidade da marca."
                modal="modal-marca"
            />
        </div>
    </div>
</section>

<section class="section">
    <div class="container hosting-box">
        <div>
            <h2>Hospedagem e gestao</h2>
            <p>Cuidamos da infraestrutura para seu projeto manter estabilidade, seguranca e velocidade sem sobrecarregar seu time.</p>
        </div>
        <a class="btn" href="{{ route('portal') }}">Contratar Hospedagem</a>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="section-head">
            <h2>Missoes em destaque</h2>
            <a class="text-link" href="{{ route('portfolio') }}">Abrir grimorio completo</a>
        </div>
        <div class="portfolio-grid">
            <x-portfolio-card title="Landing para consultoria B2B" description="Reposicionamento de oferta e funil de captacao." :tags="['site','seo','b2b']" result="+48% em leads qualificados" :image="asset('images/folio-1.svg')" />
            <x-portfolio-card title="Dashboard de operacoes" description="Painel com automacoes para equipe comercial." :tags="['dashboard','app','automacao']" result="-32% tempo operacional" :image="asset('images/folio-2.svg')" />
            <x-portfolio-card title="Campanha de lancamento" description="Criativos e paginas para nova linha de produto." :tags="['midia','campanha','tracking']" result="ROI 4.1 em 45 dias" :image="asset('images/folio-3.svg')" />
            <x-portfolio-card title="Rebranding premium" description="Novo sistema visual para escritorio juridico." :tags="['marca','design']" result="Aumento de percepcao de valor" :image="asset('images/folio-4.svg')" />
        </div>
    </div>
</section>

<section class="section final-cta">
    <div class="container">
        <h2>Pronto para iniciar a proxima missao da sua marca?</h2>
        <p>Fale com a Voltrune e receba um plano claro para gerar resultado real.</p>
        <div class="hero-actions">
            <a class="btn" href="{{ env('WHATSAPP_URL', 'https://wa.me/5511998479359') }}" target="_blank" rel="noopener">Falar no WhatsApp</a>
            <a class="btn btn-ghost" href="{{ route('contato') }}">Abrir contato</a>
            <a class="btn btn-ghost" href="{{ route('portal') }}">Contratar Hospedagem</a>
        </div>
    </div>
</section>

<x-modal id="modal-websites" title="Websites e Landings">
    <p><strong>O que inclui:</strong> UX orientada a conversao, SEO tecnico, performance, copy comercial e tracking com GA4/Pixel/eventos.</p>
    <p><strong>Para quem e:</strong> empresas, profissionais e equipes comerciais que precisam gerar demanda constante.</p>
    <p><strong>Prazo estimado:</strong> 15 a 35 dias.</p>
    <a class="btn" href="{{ route('contato') }}">Solicitar orcamento</a>
</x-modal>

<x-modal id="modal-apps" title="Apps e Dashboards">
    <p><strong>O que inclui:</strong> mapeamento de fluxo, desenvolvimento sob medida, painel administrativo e automacoes.</p>
    <p><strong>Para quem e:</strong> operacoes que sofrem com retrabalho, falta de visibilidade ou processos manuais.</p>
    <p><strong>Prazo estimado:</strong> 30 a 90 dias.</p>
    <a class="btn" href="{{ route('contato') }}">Solicitar orcamento</a>
</x-modal>

<x-modal id="modal-trafego" title="Trafego Pago e Midia">
    <p><strong>O que inclui:</strong> estrategia de canais, configuracao de contas, criativos, testes e otimizacao por ROI.</p>
    <p><strong>Para quem e:</strong> negocios que ja validaram oferta e querem escala com previsibilidade.</p>
    <p><strong>Prazo estimado:</strong> setup em 7 a 12 dias e gestao mensal.</p>
    <a class="btn" href="{{ route('contato') }}">Solicitar orcamento</a>
</x-modal>

<x-modal id="modal-marca" title="Marca, Banner e Logo">
    <p><strong>O que inclui:</strong> posicionamento visual, logotipo, paleta, tipografia, kit de pecas e manual rapido.</p>
    <p><strong>Para quem e:</strong> marcas novas ou reposicionamentos que precisam parecer premium e coerentes.</p>
    <p><strong>Prazo estimado:</strong> 10 a 25 dias.</p>
    <a class="btn" href="{{ route('contato') }}">Solicitar orcamento</a>
</x-modal>
@endsection
