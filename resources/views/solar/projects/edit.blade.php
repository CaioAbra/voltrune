@extends('solar.layout')

@section('title', 'Editar projeto | Voltrune Solar')

@section('solar-content')
    <section class="solar-page-shell">
        <section class="hub-card hub-card--subtle solar-page-intro">
            <div class="solar-page-intro__header">
                <div class="solar-page-intro__copy">
                    <p class="solar-section-eyebrow">Editar projeto</p>
                    <h2>Refine o pre-orcamento com leitura comercial mais clara</h2>
                    <p class="hub-note">O objetivo desta tela e facilitar ajustes de local, consumo, automacao e simulacao financeira sem quebrar a narrativa da proposta.</p>
                </div>

                <div class="solar-page-intro__meta">
                    <span class="solar-project-showcase__status-label">Uso recomendado</span>
                    <strong>Revise antes de abrir a simulacao</strong>
                    <p>Quanto melhor a base do projeto, mais confiantes ficam potencia, geracao e preco sugerido no restante do fluxo.</p>
                </div>
            </div>
        </section>

        <section class="hub-card solar-page-panel">
            <div class="solar-page-panel__header">
                <h2>Refinar pre-orcamento solar</h2>
                <p class="hub-note">Atualize o fluxo comercial do projeto com consumo, sistema sugerido, valor inicial e contexto da instalacao.</p>
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
