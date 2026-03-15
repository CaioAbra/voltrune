@extends('solar.layout')

@section('title', 'Editar projeto | Voltrune Solar')

@section('solar-content')
    <section class="solar-page-shell">
        <section class="hub-card hub-card--subtle solar-page-intro">
            <div class="solar-page-intro__header">
                <div class="solar-page-intro__copy">
                    <p class="solar-section-eyebrow">Editar projeto</p>
                    <h2>Revise os dados do projeto e ajuste a leitura inicial</h2>
                    <p class="hub-note">Atualize cliente, local e consumo primeiro. Depois refine sistema, orcamento e contexto comercial sem perder o historico.</p>
                </div>

                <div class="solar-page-intro__meta">
                    <span class="solar-project-showcase__status-label">Uso recomendado</span>
                    <strong>Revise antes de gerar ou atualizar a simulacao</strong>
                    <p>Quanto melhor a base do projeto, mais confiantes ficam potencia, geracao e preco sugerido nas telas seguintes.</p>
                </div>
            </div>
        </section>

        <section class="hub-card solar-page-panel">
            <div class="solar-page-panel__header">
                <h2>Editar projeto solar</h2>
                <p class="hub-note">Revise os dados base do projeto e use os blocos abaixo para ajustar a leitura tecnica e comercial quando precisar.</p>
            </div>

            <div class="hub-actions">
                <a href="{{ route('solar.projects.index') }}" class="hub-btn hub-btn--subtle">Voltar para projetos</a>
            </div>

            @if ($errors->any())
                <div class="hub-alert hub-alert--danger">
                    <strong>Revise os campos do formulario.</strong>
                </div>
            @endif

            <form action="{{ route('solar.projects.update', $project->id) }}" method="post" class="hub-auth-form">
                @csrf
                @method('PUT')

                @include('solar.projects.partials.form', [
                    'project' => $project,
                    'customers' => $customers,
                    'submitLabel' => 'Salvar alteracoes',
                ])
            </form>
        </section>
    </section>
@endsection
