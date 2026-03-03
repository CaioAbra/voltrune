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
            <p class="lead">Unimos estrategia, design e execucao tecnica para transformar presenca digital em demanda qualificada.</p>
            <div class="hero-actions">
                <a class="btn" href="{{ env('WHATSAPP_URL', 'https://wa.me/5511998479359') }}" target="_blank" rel="noopener">Iniciar no WhatsApp</a>
                <a class="btn btn-ghost" href="{{ route('servicos') }}">Explorar servicos</a>
            </div>
            <div class="badge-row">
                <span>Agenda ativa</span>
                <span>Resposta em curto prazo</span>
            </div>
        </div>

        <aside class="mission-card">
            <h2>Carta de Missao</h2>
            <ul>
                <li>Performance tecnica e SEO desde o primeiro deploy.</li>
                <li>Percepcao premium para sustentar ticket e autoridade.</li>
                <li>Escopo claro, execucao objetiva e leitura de resultado.</li>
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
                <p>Mapeamos contexto, meta comercial e os gargalos que hoje travam sua aquisicao.</p>
            </article>
            <article>
                <span>02</span>
                <h3>Execucao</h3>
                <p>Executamos as entregas com padrao tecnico, coerencia visual e foco real em conversao.</p>
            </article>
            <article>
                <span>03</span>
                <h3>Entrega e evolucao</h3>
                <p>Publicamos com rastreamento ativo e uma trilha clara de iteracao e melhoria.</p>
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
                excerpt="Paginas orientadas a conversao, com SEO, performance e mensuracao de ponta a ponta."
                modal="modal-websites"
            />
            <x-service-card
                title="Apps e Dashboards"
                icon="dashboard"
                excerpt="Sistemas, paineis e automacoes para reduzir atrito e ampliar capacidade operacional."
                modal="modal-apps"
            />
            <x-service-card
                title="Trafego Pago e Midia"
                icon="ads_click"
                excerpt="Planejamento, criativos e otimizacao continua para melhorar retorno com previsibilidade."
                modal="modal-trafego"
            />
            <x-service-card
                title="Marca, Banner e Logo"
                icon="palette"
                excerpt="Direcao visual para elevar percepcao de valor e consolidar confianca imediata."
                modal="modal-marca"
            />
        </div>
    </div>
</section>

<section class="section">
    <div class="container hosting-box">
        <div>
            <h2>Hospedagem e gestao</h2>
            <p>Assumimos a camada tecnica para seu projeto operar com estabilidade, seguranca e velocidade sem virar gargalo interno.</p>
        </div>
        <a class="btn" href="{{ route('portal') }}">Ver plano de hospedagem</a>
    </div>
</section>

<section class="section section-alt">
    <div class="container quest-board">
        <div class="section-head">
            <h2>Missoes em destaque</h2>
            <a class="text-link" href="{{ route('portfolio') }}">Ver todas as missoes</a>
        </div>
        <div class="portfolio-grid quest-grid">
            <x-portfolio-card title="Landing para consultoria B2B" description="Reposicionamento de oferta e funil de captacao." :tags="['site','seo','b2b']" result="+48% em leads qualificados" :image="asset('images/folio-1.svg')" seal="Aprovado" />
            <x-portfolio-card title="Dashboard de operacoes" description="Painel com automacoes para equipe comercial." :tags="['dashboard','app','automacao']" result="-32% tempo operacional" :image="asset('images/folio-2.svg')" seal="Concluida" />
            <x-portfolio-card title="Campanha de lancamento" description="Criativos e paginas para nova linha de produto." :tags="['midia','campanha','tracking']" result="ROI 4.1 em 45 dias" :image="asset('images/folio-3.svg')" />
            <x-portfolio-card title="Rebranding premium" description="Novo sistema visual para escritorio juridico." :tags="['marca','design']" result="Aumento de percepcao de valor" :image="asset('images/folio-4.svg')" />
        </div>
    </div>
</section>

<section class="section final-cta">
    <div class="container">
        <h2>Pronto para iniciar a proxima missao da sua marca?</h2>
        <p>Converse com a Voltrune e receba uma rota clara para vender mais com consistencia.</p>
        <div class="hero-actions">
            <a class="btn" href="{{ env('WHATSAPP_URL', 'https://wa.me/5511998479359') }}" target="_blank" rel="noopener">Falar com a Voltrune</a>
            <a class="btn btn-ghost" href="{{ route('contato') }}">Enviar briefing</a>
            <a class="btn btn-ghost" href="{{ route('portal') }}">Ver hospedagem</a>
        </div>
    </div>
</section>

<x-modal id="modal-websites" title="Websites e Landings">
    <p><strong>O que inclui:</strong> UX orientada a conversao, SEO tecnico, performance, copy comercial e tracking com GA4/Pixel/eventos.</p>
    <p><strong>Para quem e:</strong> empresas, profissionais e equipes comerciais que precisam gerar demanda constante.</p>
    <p><strong>Prazo estimado:</strong> 15 a 35 dias.</p>
    <a class="btn" href="{{ route('contato') }}">Solicitar proposta</a>
</x-modal>

<x-modal id="modal-apps" title="Apps e Dashboards">
    <p><strong>O que inclui:</strong> mapeamento de fluxo, desenvolvimento sob medida, painel administrativo e automacoes.</p>
    <p><strong>Para quem e:</strong> operacoes que sofrem com retrabalho, falta de visibilidade ou processos manuais.</p>
    <p><strong>Prazo estimado:</strong> 30 a 90 dias.</p>
    <a class="btn" href="{{ route('contato') }}">Solicitar proposta</a>
</x-modal>

<x-modal id="modal-trafego" title="Trafego Pago e Midia">
    <p><strong>O que inclui:</strong> estrategia de canais, configuracao de contas, criativos, testes e otimizacao por ROI.</p>
    <p><strong>Para quem e:</strong> negocios que ja validaram oferta e querem escala com previsibilidade.</p>
    <p><strong>Prazo estimado:</strong> setup em 7 a 12 dias e gestao mensal.</p>
    <a class="btn" href="{{ route('contato') }}">Solicitar proposta</a>
</x-modal>

<x-modal id="modal-marca" title="Marca, Banner e Logo">
    <p><strong>O que inclui:</strong> posicionamento visual, logotipo, paleta, tipografia, kit de pecas e manual rapido.</p>
    <p><strong>Para quem e:</strong> marcas novas ou reposicionamentos que precisam parecer premium e coerentes.</p>
    <p><strong>Prazo estimado:</strong> 10 a 25 dias.</p>
    <a class="btn" href="{{ route('contato') }}">Solicitar proposta</a>
</x-modal>
@endsection
