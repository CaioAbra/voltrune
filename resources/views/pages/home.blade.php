@extends('layouts.app')

@section('title', 'Sites, Apps e Mídia para crescer com consistência')
@section('meta_description', 'A Voltrune desenvolve sites, landing pages, apps e estratégias de mídia com foco em conversão, performance técnica e SEO para gerar demanda qualificada.')
@section('canonical', route('home'))

@section('content')
<section class="hero section home-hero">
    <div class="container hero-grid">
        <div>
            <p class="eyebrow">Ordem Voltrune</p>
            <h1>Construa uma presença digital que vende: site, app, mídia e marca no mesmo plano.</h1>
            <p class="lead">Unimos estratégia, design e execução técnica para transformar presença digital em demanda real e previsível.</p>
            <div class="hero-actions">
                <a class="btn" href="{{ env('WHATSAPP_URL', 'https://wa.me/5511998479359') }}" target="_blank" rel="noopener">Iniciar no WhatsApp</a>
                <a class="btn btn-ghost" href="{{ route('servicos') }}">Explorar serviços</a>
            </div>
            <div class="badge-row">
                <span>Agenda ativa</span>
                <span>Resposta em curto prazo</span>
            </div>
        </div>

        <aside class="mission-card">
            <h2>Carta de missão</h2>
            <ul>
                <li>Performance técnica e SEO desde a primeira publicação.</li>
                <li>Percepção premium para sustentar ticket e autoridade.</li>
                <li>Escopo claro, execução objetiva e leitura de resultado.</li>
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
                <h3>Diagnóstico</h3>
                <p>Mapeamos contexto, meta comercial e os gargalos que hoje travam sua aquisição.</p>
            </article>
            <article>
                <span>02</span>
                <h3>Execução</h3>
                <p>Executamos as entregas com padrão técnico, coerência visual e foco real em conversão.</p>
            </article>
            <article>
                <span>03</span>
                <h3>Entrega e evolução</h3>
                <p>Publicamos com rastreamento ativo e uma trilha clara de iteração e melhoria.</p>
            </article>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <h2>Serviços da ordem Voltrune</h2>
        <div class="services-grid">
            <x-service-card
                title="Websites e Landings"
                icon="language"
                excerpt="Páginas orientadas à conversão, com SEO, performance e mensuração de ponta a ponta."
                modal="modal-websites"
            />
            <x-service-card
                title="Apps e Dashboards"
                icon="dashboard"
                excerpt="Sistemas, painéis e automações para reduzir atrito e ampliar capacidade operacional."
                modal="modal-apps"
            />
            <x-service-card
                title="Tráfego Pago e Mídia"
                icon="ads_click"
                excerpt="Planejamento, criativos e otimização contínua para melhorar retorno com previsibilidade."
                modal="modal-trafego"
            />
            <x-service-card
                title="Marca, Banner e Logo"
                icon="palette"
                excerpt="Direção visual para elevar percepção de valor e consolidar confiança imediata."
                modal="modal-marca"
            />
        </div>
    </div>
</section>

<section class="section">
    <div class="container hosting-box">
        <div>
            <h2>Hospedagem e gestão</h2>
            <p>Assumimos a camada técnica para seu projeto operar com estabilidade, segurança e velocidade sem virar gargalo interno.</p>
        </div>
        <a class="btn" href="{{ route('portal') }}">Ver plano de hospedagem</a>
    </div>
</section>

<section class="section section-alt">
    <div class="container quest-board">
        <div class="section-head">
            <h2>Missões em destaque</h2>
            <a class="text-link" href="{{ route('portfolio') }}">Ver todo o portfólio</a>
        </div>
        <div class="portfolio-grid quest-grid">
            <x-portfolio-card title="Landing para consultoria B2B" description="Reposicionamento de oferta e funil de captação." :tags="['site','seo','b2b']" result="+48% em leads qualificados" :image="asset('images/folio-1.svg')" seal="Aprovado" />
            <x-portfolio-card title="Dashboard de operações" description="Painel com automações para equipe comercial." :tags="['dashboard','app','automacao']" result="-32% tempo operacional" :image="asset('images/folio-2.svg')" seal="Concluída" />
            <x-portfolio-card title="Campanha de lançamento" description="Criativos e páginas para nova linha de produto." :tags="['midia','campanha','tracking']" result="ROI 4.1 em 45 dias" :image="asset('images/folio-3.svg')" />
            <x-portfolio-card title="Rebranding premium" description="Novo sistema visual para escritório jurídico." :tags="['marca','design']" result="Aumento de percepção de valor" :image="asset('images/folio-4.svg')" />
        </div>
    </div>
</section>

<section class="section final-cta">
    <div class="container">
        <h2>Pronto para iniciar a próxima missão da sua marca?</h2>
        <p>Converse com a Voltrune e receba um plano claro para vender mais com consistência.</p>
        <div class="hero-actions">
            <a class="btn" href="{{ env('WHATSAPP_URL', 'https://wa.me/5511998479359') }}" target="_blank" rel="noopener">Falar com a Voltrune</a>
            <a class="btn btn-ghost" href="{{ route('contato') }}">Enviar briefing</a>
            <a class="btn btn-ghost" href="{{ route('portal') }}">Ver hospedagem</a>
        </div>
    </div>
</section>

<x-modal id="modal-websites" title="Websites e Landings">
    <p><strong>O que inclui:</strong> UX orientada à conversão, SEO técnico, performance, copy comercial e tracking com GA4, Pixel e eventos.</p>
    <p><strong>Para quem é:</strong> empresas, profissionais e equipes comerciais que precisam gerar demanda constante.</p>
    <p><strong>Prazo estimado:</strong> 15 a 35 dias.</p>
    <form method="POST" action="{{ route('contato.prefill') }}">
        @csrf
        <input type="hidden" name="subject" value="Websites e Landings">
        <button class="btn" type="submit">Solicitar proposta</button>
    </form>
</x-modal>

<x-modal id="modal-apps" title="Apps e Dashboards">
    <p><strong>O que inclui:</strong> mapeamento de fluxo, desenvolvimento sob medida, painel administrativo e automações.</p>
    <p><strong>Para quem é:</strong> operações que sofrem com retrabalho, falta de visibilidade ou processos manuais.</p>
    <p><strong>Prazo estimado:</strong> 30 a 90 dias.</p>
    <form method="POST" action="{{ route('contato.prefill') }}">
        @csrf
        <input type="hidden" name="subject" value="Apps e Dashboards">
        <button class="btn" type="submit">Solicitar proposta</button>
    </form>
</x-modal>

<x-modal id="modal-trafego" title="Tráfego Pago e Mídia">
    <p><strong>O que inclui:</strong> estratégia de canais, configuração de contas, criativos, testes e otimização orientada a ROI.</p>
    <p><strong>Para quem é:</strong> negócios que já validaram oferta e querem escala com previsibilidade.</p>
    <p><strong>Prazo estimado:</strong> setup em 7 a 12 dias e gestão mensal.</p>
    <form method="POST" action="{{ route('contato.prefill') }}">
        @csrf
        <input type="hidden" name="subject" value="Tráfego Pago e Mídia">
        <button class="btn" type="submit">Solicitar proposta</button>
    </form>
</x-modal>

<x-modal id="modal-marca" title="Marca, Banner e Logo">
    <p><strong>O que inclui:</strong> posicionamento visual, logotipo, paleta, tipografia, kit de peças e manual rápido.</p>
    <p><strong>Para quem é:</strong> marcas novas ou reposicionamentos que precisam parecer premium e coerentes.</p>
    <p><strong>Prazo estimado:</strong> 10 a 25 dias.</p>
    <form method="POST" action="{{ route('contato.prefill') }}">
        @csrf
        <input type="hidden" name="subject" value="Marca, Banner e Logo">
        <button class="btn" type="submit">Solicitar proposta</button>
    </form>
</x-modal>
@endsection
