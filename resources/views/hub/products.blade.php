@extends('hub.layout')

@section('title', 'Sistemas | Voltrune Hub')

@section('content')
    <h1>Sistemas disponíveis</h1>

    <div class="hub-grid">
        <article class="hub-card">
            <h2>Solar</h2>
            <p>Simulação e orçamento de energia solar</p>
            <button type="button" disabled class="hub-btn-disabled" title="Em breve">
                Acessar (Em breve)
            </button>
        </article>

        <article class="hub-card">
            <h2>Vigilante</h2>
            <p>Automação para escritórios jurídicos</p>
            <button type="button" disabled class="hub-btn-disabled" title="Em breve">
                Acessar (Em breve)
            </button>
        </article>

        <article class="hub-card">
            <h2>Agro</h2>
            <p>Análise e recomendação de cultivo</p>
            <button type="button" disabled class="hub-btn-disabled" title="Em breve">
                Acessar (Em breve)
            </button>
        </article>
    </div>
@endsection
