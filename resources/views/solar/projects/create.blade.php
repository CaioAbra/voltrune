@extends('solar.layout')

@section('title', 'Novo projeto | Voltrune Solar')

@section('solar-content')
    <section class="hub-card">
        <h2>Projeto de instalacao</h2>
        <p class="hub-note">Cada projeto representa o local da instalacao solar e sera a base para simulacao tecnica nas proximas etapas.</p>

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
@endsection
