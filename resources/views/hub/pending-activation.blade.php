@extends('hub.layout')

@section('title', 'Ativação Pendente | Voltrune Hub')

@section('content')
    <h1>Conta criada com sucesso</h1>

    <div class="hub-card hub-alert hub-alert--warning">
        <p><strong>Sua conta foi criada e está aguardando ativação pela equipe Voltrune.</strong></p>
        <p>Enquanto o status da empresa estiver como pending, o acesso aos sistemas permanece bloqueado.</p>
    </div>

    <div class="hub-actions">
        <a href="{{ route('contato') }}" class="hub-btn">Falar com a Voltrune</a>
        <a href="{{ route('hub.dashboard') }}" class="hub-btn">Voltar ao dashboard</a>
    </div>
@endsection
