@extends('layouts.app')

@section('title', 'Voltrune Solar | Software para instaladores solares')
@section('meta_description', 'Voltrune Solar é um software para energia solar voltado a instaladores, integradores e empresas do setor que precisam fazer orçamento solar online, organizar projetos e vender com mais agilidade.')
@section('canonical', route('sistemas.solar'))
@section('og_title', 'Voltrune Solar | Sistema para instaladores solares')
@section('og_description', 'Faça orçamentos solares em minutos, organize clientes e projetos e evolua propostas com mais velocidade.')

@push('structured-data')
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'SoftwareApplication',
    'name' => 'Voltrune Solar',
    'applicationCategory' => 'BusinessApplication',
    'operatingSystem' => 'Web',
    'description' => 'Software para instaladores solares, integradores e empresas de energia solar fazerem orçamentos mais rápidos, organizarem projetos e venderem com mais agilidade.',
    'url' => route('sistemas.solar'),
    'brand' => [
        '@type' => 'Brand',
        'name' => 'Voltrune',
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
<section class="section page-hero solar-lp-hero">
    <div class="container solar-lp-hero__grid">
        <div>
            <p class="eyebrow">Voltrune Solar</p>
            <h1>Faça orçamentos solares em minutos, não em horas.</h1>
            <p class="lead">O Voltrune Solar é um software para instaladores solares, integradores e empresas de energia solar que precisam ganhar velocidade comercial, organizar projetos e vender com menos planilhas e mais clareza.</p>
            <div class="hero-actions">
                <form method="POST" action="{{ route('contato.prefill') }}">
                    @csrf
                    <input type="hidden" name="subject" value="Voltrune Solar">
                    <button class="btn" type="submit">Quero conhecer o Solar</button>
                </form>
                <a class="btn btn-ghost" href="{{ route('contato') }}">Solicitar acesso</a>
            </div>
            <div class="solar-lp-proof">
                <span>Software para energia solar</span>
                <span>Orçamento solar online</span>
                <span>CRM para energia solar em evolução</span>
            </div>
        </div>

        <aside class="solar-lp-preview">
            <p class="solar-lp-preview__eyebrow">Posicionamento do produto</p>
            <h2>Venda sistemas solares com mais agilidade e menos retrabalho.</h2>
            <ul>
                <li>Cadastre clientes e projetos em um só fluxo.</li>
                <li>Preencha endereço com CEP e responda o lead mais rápido.</li>
                <li>Receba sistema sugerido, pré-orçamento e simulação financeira.</li>
            </ul>
            <div class="solar-lp-preview__metrics">
                <article>
                    <strong>Mais velocidade</strong>
                    <span>no atendimento comercial</span>
                </article>
                <article>
                    <strong>Mais padrão</strong>
                    <span>na construção do orçamento</span>
                </article>
                <article>
                    <strong>Mais controle</strong>
                    <span>sobre clientes e projetos</span>
                </article>
            </div>
        </aside>
    </div>
</section>

<section class="section">
    <div class="container narrow solar-section-heading">
        <p class="eyebrow">Dor do mercado</p>
        <h2>O instalador perde venda quando o orçamento demora.</h2>
        <p class="lead">O Solar foi pensado para operações que precisam responder leads com rapidez, manter padrão comercial e transformar atendimento em proposta com menos atrito.</p>
    </div>

    <div class="container solar-pain-grid">
        <article class="solar-lp-card">
            <h3>Orçamento demorado</h3>
            <p>Quando a equipe leva horas para consolidar dados, o lead esfria e a conversa perde ritmo.</p>
        </article>
        <article class="solar-lp-card">
            <h3>Cálculo manual em Excel</h3>
            <p>Planilhas soltas aumentam retrabalho, erro operacional e dependência de quem montou a lógica.</p>
        </article>
        <article class="solar-lp-card">
            <h3>Proposta sem padrão</h3>
            <p>Cada vendedor organiza a informação de um jeito, o que reduz consistência e percepção profissional.</p>
        </article>
        <article class="solar-lp-card">
            <h3>Resposta lenta ao lead</h3>
            <p>Sem fluxo claro, a empresa demora a devolver uma estimativa inicial e perde timing comercial.</p>
        </article>
        <article class="solar-lp-card">
            <h3>Projetos dispersos</h3>
            <p>Cliente, consumo, endereço, sistema e valor ficam espalhados em ferramentas diferentes.</p>
        </article>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="solar-section-heading solar-section-heading--split">
            <div>
                <p class="eyebrow">Como funciona</p>
                <h2>Um fluxo comercial simples para o time solar sair do lead ao pré-orçamento.</h2>
            </div>
            <p class="lead">O sistema para instaladores solares organiza a jornada em etapas objetivas, com automação onde faz sentido e liberdade de ajuste manual.</p>
        </div>

        <div class="solar-flow-grid">
            <article class="solar-step-card">
                <span>01</span>
                <h3>Cadastrar cliente</h3>
                <p>Centralize o lead e o contexto comercial desde o início.</p>
            </article>
            <article class="solar-step-card">
                <span>02</span>
                <h3>Criar projeto</h3>
                <p>Abra o local da instalação e organize a operação por projeto real.</p>
            </article>
            <article class="solar-step-card">
                <span>03</span>
                <h3>Informar consumo</h3>
                <p>Use consumo mensal e valor da conta para dar base à simulação.</p>
            </article>
            <article class="solar-step-card">
                <span>04</span>
                <h3>Receber sistema sugerido</h3>
                <p>Veja potência, módulos e geração estimada automaticamente.</p>
            </article>
            <article class="solar-step-card">
                <span>05</span>
                <h3>Gerar pré-orçamento</h3>
                <p>Apresente valor inicial e economia estimada com mais agilidade.</p>
            </article>
            <article class="solar-step-card">
                <span>06</span>
                <h3>Evoluir para proposta</h3>
                <p>Saia do rascunho e amadureça a negociação com base organizada.</p>
            </article>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="solar-section-heading solar-section-heading--split">
            <div>
                <p class="eyebrow">O que o Solar resolve</p>
                <h2>Um software de orçamento solar pensado para operação comercial, não para o consumidor final.</h2>
            </div>
            <p class="lead">O foco do produto é ajudar instaladores, integradores, revendas e empresas do setor a responder rápido, padronizar o atendimento e vender mais.</p>
        </div>

        <div class="solar-solution-grid">
            <article class="solar-lp-card solar-lp-card--accent">
                <h3>Clientes e projetos em uma só base</h3>
                <p>Menos perda de contexto entre comercial, pré-venda e evolução da proposta.</p>
            </article>
            <article class="solar-lp-card solar-lp-card--accent">
                <h3>Endereço com preenchimento por CEP</h3>
                <p>Menos digitação manual e mais velocidade no atendimento inicial.</p>
            </article>
            <article class="solar-lp-card solar-lp-card--accent">
                <h3>Sistema sugerido automaticamente</h3>
                <p>Potência, módulos, geração e leitura inicial para acelerar o orçamento solar online.</p>
            </article>
            <article class="solar-lp-card solar-lp-card--accent">
                <h3>Configuração comercial por empresa</h3>
                <p>Cada operação pode trabalhar com seus próprios padrões e referência de preço.</p>
            </article>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="solar-section-heading">
            <p class="eyebrow">Benefícios</p>
            <h2>Mais rapidez no orçamento, mais organização comercial e mais confiança na apresentação.</h2>
        </div>

        <div class="solar-benefit-grid">
            <article class="solar-benefit-card">
                <strong>Mais rapidez no orçamento</strong>
                <p>Atenda mais leads sem depender de montagem manual a cada negociação.</p>
            </article>
            <article class="solar-benefit-card">
                <strong>Menos retrabalho</strong>
                <p>Dados de cliente, endereço, consumo e sistema ficam na mesma jornada.</p>
            </article>
            <article class="solar-benefit-card">
                <strong>Mais padronização</strong>
                <p>O time comercial passa a seguir um fluxo mais claro e mais consistente.</p>
            </article>
            <article class="solar-benefit-card">
                <strong>Mais percepção profissional</strong>
                <p>O cliente final recebe respostas mais rápidas e uma leitura comercial mais sólida.</p>
            </article>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="solar-section-heading solar-section-heading--split">
            <div>
                <p class="eyebrow">Prévia do sistema</p>
                <h2>O Solar já nasce para apoiar a venda antes da proposta final.</h2>
            </div>
            <p class="lead">Hoje o produto já organiza a base comercial e entrega uma experiência inicial de pré-orçamento para empresas de energia solar.</p>
        </div>

        <div class="solar-preview-grid">
            <article class="solar-preview-card">
                <span class="system-badge">Clientes</span>
                <h3>Base comercial organizada</h3>
                <p>Cadastre clientes e mantenha o histórico comercial centralizado para cada operação.</p>
            </article>
            <article class="solar-preview-card">
                <span class="system-badge">Projetos</span>
                <h3>Fluxo por instalação</h3>
                <p>Trabalhe cada oportunidade com endereço, consumo, concessionária e status do projeto.</p>
            </article>
            <article class="solar-preview-card">
                <span class="system-badge">Sistema sugerido</span>
                <h3>Automação com ajuste manual</h3>
                <p>Receba potência, módulos, geração e valor sugerido, sem perder controle sobre a edição.</p>
            </article>
            <article class="solar-preview-card">
                <span class="system-badge">Pré-orçamento</span>
                <h3>Leitura comercial rápida</h3>
                <p>Mostre preço sugerido e simulação financeira inicial para ganhar velocidade na negociação.</p>
            </article>
        </div>
    </div>
</section>

<section class="section">
    <div class="container narrow solar-final-cta">
        <div>
            <p class="eyebrow">CTA</p>
            <h2>Se você vende energia solar, o Solar foi desenhado para a sua operação.</h2>
            <p>Entre em contato para conhecer o produto, solicitar acesso ou entrar na lista de interesse enquanto o módulo continua amadurecendo.</p>
        </div>
        <div class="solar-final-cta__actions">
            <form method="POST" action="{{ route('contato.prefill') }}">
                @csrf
                <input type="hidden" name="subject" value="Voltrune Solar">
                <button class="btn" type="submit">Solicitar acesso</button>
            </form>
            <a class="btn btn-ghost" href="{{ route('contato') }}">Falar com a Voltrune</a>
            <form method="POST" action="{{ route('contato.prefill') }}">
                @csrf
                <input type="hidden" name="subject" value="Fila de Novos Lançamentos">
                <button class="btn btn-ghost" type="submit">Entrar na lista de interesse</button>
            </form>
        </div>
    </div>
</section>
@endsection
