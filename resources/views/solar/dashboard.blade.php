@extends('solar.layout')

@section('title', 'Solar | Voltrune')

@section('solar-content')
    <section class="solar-page-shell">
        <section class="hub-card hub-card--subtle solar-page-intro">
            <div class="solar-page-intro__header">
                <div class="solar-page-intro__copy">
                    <p class="solar-section-eyebrow">Operacao comercial</p>
                    <h2>Fluxo de venda para energia solar</h2>
                    <p class="hub-note">O Solar foi organizado para quem precisa sair do lead ao orcamento com velocidade, leitura tecnica clara e discurso comercial consistente.</p>
                </div>

                <div class="solar-page-intro__meta">
                    <span class="solar-project-showcase__status-label">Jornada recomendada</span>
                    <strong>Cliente -> projeto -> simulacao -> proposta</strong>
                    <p>Entre no modulo certo de acordo com o estagio da venda e mantenha o contexto da operacao em cada etapa.</p>
                </div>
            </div>
        </section>

        <section class="solar-page-grid solar-page-grid--cards">
            <article class="hub-card hub-card--subtle solar-quick-card">
                <span class="solar-quick-card__eyebrow">Base comercial</span>
                <div>
                    <h3>Clientes</h3>
                    <p>Cadastre contratantes e mantenha a base pronta para abrir novos projetos sem retrabalho.</p>
                </div>
                <a href="{{ route('solar.customers.index') }}" class="hub-btn">Abrir clientes</a>
            </article>

            <article class="hub-card hub-card--subtle solar-quick-card">
                <span class="solar-quick-card__eyebrow">Entrada do funil</span>
                <div>
                    <h3>Projetos</h3>
                    <p>Organize local, consumo e contexto da instalacao para preparar o dimensionamento automatico.</p>
                </div>
                <a href="{{ route('solar.projects.index') }}" class="hub-btn">Abrir projetos</a>
            </article>

            <article class="hub-card hub-card--subtle solar-quick-card">
                <span class="solar-quick-card__eyebrow">Analise tecnica</span>
                <div>
                    <h3>Simulacoes</h3>
                    <p>Compare potencia, economia e preco sugerido antes de transformar um cenario em proposta.</p>
                </div>
                <a href="{{ route('solar.simulations.index') }}" class="hub-btn">Abrir simulacoes</a>
            </article>

            <article class="hub-card hub-card--subtle solar-quick-card">
                <span class="solar-quick-card__eyebrow">Fechamento</span>
                <div>
                    <h3>Orcamentos</h3>
                    <p>Consolide materiais, servicos, preco final e status comercial para avancar a negociacao.</p>
                </div>
                <a href="{{ route('solar.quotes.index') }}" class="hub-btn">Abrir orcamentos</a>
            </article>
        </section>
    </section>
@endsection
