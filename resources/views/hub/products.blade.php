@extends('hub.layout')

@section('title', 'Sistemas | Voltrune Hub')

@section('content')
    <h1>Sistemas</h1>
    <p>Ative e acompanhe os módulos incluídos no seu plano SaaS.</p>

    <div class="hub-grid">
        <article class="hub-card">
            <h2>Solar</h2>
            <p>Simulação e orçamento para operações de energia solar.</p>
            <p class="hub-note">Disponível por contratação comercial no momento</p>
            <button type="button" disabled class="hub-btn-disabled" title="Em breve">
                Disponível em breve
            </button>
        </article>

        <article class="hub-card">
            <h2>Vigilante</h2>
            <p>Automação de fluxos para escritórios jurídicos.</p>
            <p class="hub-note">Disponível por contratação comercial no momento</p>
            <button type="button" disabled class="hub-btn-disabled" title="Em breve">
                Disponível em breve
            </button>
        </article>

        <article class="hub-card">
            <h2>Agro</h2>
            <p>Análise técnica e recomendação orientada a cultivo.</p>
            <p class="hub-note">Autoatendimento em breve</p>
            <button type="button" disabled class="hub-btn-disabled" title="Em breve">
                Disponível em breve
            </button>
        </article>
    </div>
@endsection
