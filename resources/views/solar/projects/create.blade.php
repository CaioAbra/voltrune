@extends('solar.layout')

@section('title', 'Novo projeto | Voltrune Solar')

@section('solar-content')
    <section class="solar-page-shell">
        <section class="hub-card hub-card--subtle solar-page-intro">
            <div class="solar-page-intro__header">
                <div class="solar-page-intro__copy">
                    <p class="solar-section-eyebrow">Novo projeto</p>
                    <h2>Monte um projeto comercial sem perder contexto</h2>
                    <p class="hub-note">Comece pelo essencial: cliente, local e consumo. O Solar atualiza a leitura inicial do orcamento logo abaixo.</p>
                </div>

                <div class="solar-page-intro__meta">
                    <span class="solar-project-showcase__status-label">Resultado esperado</span>
                    <strong>Projeto pronto para gerar a primeira simulacao</strong>
                    <p>Preencha os dados base primeiro. Os ajustes do sistema e a leitura comercial ficam disponiveis na mesma jornada.</p>
                </div>
            </div>
        </section>

        <section class="hub-card solar-page-panel">
            <div class="solar-page-panel__header">
                <h2>Novo projeto solar</h2>
                <p class="hub-note">Preencha o essencial primeiro. Os blocos de revisao tecnica e comercial aparecem logo abaixo para ajuste fino.</p>
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
