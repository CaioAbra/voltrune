@extends('solar.layout')

@section('title', 'Novo projeto | Voltrune Solar')

@section('solar-content')
    <section class="hub-card">
        <h2>Novo pre-orcamento solar</h2>
        <p class="hub-note">Monte um projeto comercial com cliente, local, consumo, sistema sugerido e valor inicial sem sair do fluxo do instalador.</p>

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
