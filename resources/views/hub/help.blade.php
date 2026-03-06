@extends('hub.layout')

@section('title', 'Suporte | Voltrune Hub')

@section('content')
    <h1>Suporte ao cliente</h1>

    <div class="hub-card">
        <h2>Como funciona o hub</h2>
        <p>
            O Voltrune Hub é a área para clientes com licença ativa ou assinatura mensal.
            Aqui, você acompanha aplicativos contratados, conta de acesso e próximas entregas.
        </p>
    </div>

    <div class="hub-card">
        <h2>Precisa de atendimento?</h2>
        <p>
            Para suporte técnico, ajuste de plano ou liberação de usuário, fale com nossa equipe de atendimento.
        </p>
        <a href="{{ route('contato') }}" class="hub-btn">Abrir contato</a>
    </div>
@endsection
