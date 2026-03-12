@extends('solar.layout')

@section('title', 'Editar projeto | Voltrune Solar')

@section('solar-content')
    <section class="hub-card">
        <h2>Refinar pré-orçamento solar</h2>
        <p class="hub-note">Atualize o fluxo comercial do projeto com consumo, sistema sugerido, valor inicial e contexto da instalação.</p>

        <div class="hub-actions">
            <a href="{{ route('solar.projects.index') }}" class="hub-btn hub-btn--subtle">Voltar para projetos</a>
        </div>

        @if ($errors->any())
            <div class="hub-alert hub-alert--danger">
                <strong>Revise os campos do formulário.</strong>
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
@endsection
