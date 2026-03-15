@extends('solar.layout')

@section('title', 'Novo projeto | Voltrune Solar')

@section('solar-content')
    <section class="solar-page-shell">
        <section class="hub-card hub-card--subtle solar-page-intro">
            <div class="solar-page-intro__header">
                <div class="solar-page-intro__copy">
                    <p class="solar-section-eyebrow">Novo projeto</p>
                    <h2>Monte um pre-orcamento comercial sem perder contexto</h2>
                    <p class="hub-note">Este fluxo foi reorganizado para o vendedor enxergar local, automacao, sistema sugerido e leitura financeira sem esmagar a informacao em cards estreitos.</p>
                </div>

                <div class="solar-page-intro__meta">
                    <span class="solar-project-showcase__status-label">Resultado esperado</span>
                    <strong>Projeto pronto para virar simulacao</strong>
                    <p>Preencha a base do cliente, valide local e consumo, e deixe o Solar preparar a primeira leitura tecnico-comercial.</p>
                </div>
            </div>
        </section>

        <section class="hub-card solar-page-panel">
            <div class="solar-page-panel__header">
                <h2>Novo pre-orcamento solar</h2>
                <p class="hub-note">Monte um projeto comercial com cliente, local, consumo, sistema sugerido e valor inicial sem sair da jornada do instalador.</p>
            </div>

            <div class="hub-actions">
                <a href="{{ route('solar.projects.index') }}" class="hub-btn hub-btn--subtle">Voltar para projetos</a>
            </div>

            @if ($errors->any())
                <div class="hub-alert hub-alert--danger">
                    <strong>Revise os campos do formulario.</strong>
                </div>
            @endif

            <form action="{{ route('solar.projects.store') }}" method="post" class="hub-auth-form">
                @csrf

                @include('solar.projects.partials.form', [
                    'project' => $project,
                    'customers' => $customers,
                    'submitLabel' => 'Salvar projeto',
                ])
            </form>
        </section>
    </section>
@endsection
