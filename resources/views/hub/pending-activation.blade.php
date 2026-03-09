@extends('hub.layout')

@section('title', 'Status da Conta | Voltrune Hub')

@section('content')
    @php
        $isSuspended = ($companyStatus ?? 'pending') === 'suspended';
    @endphp

    <h1>{{ $isSuspended ? 'Conta suspensa' : 'Conta criada com sucesso' }}</h1>

    <div class="hub-card hub-alert {{ $isSuspended ? 'hub-alert--danger' : 'hub-alert--warning' }}">
        @if ($isSuspended)
            <p><strong>Sua conta esta temporariamente suspensa pela equipe Voltrune.</strong></p>
            <p>Enquanto o status estiver como suspended, o acesso aos sistemas permanece bloqueado.</p>
        @else
            <p><strong>Sua conta foi criada e esta aguardando ativacao pela equipe Voltrune.</strong></p>
            <p>Enquanto o status da empresa estiver como pending, o acesso aos sistemas permanece bloqueado.</p>
        @endif
    </div>

    <div class="hub-actions">
        <a href="{{ route('contato') }}" class="hub-btn">Falar com a Voltrune</a>
        <a href="{{ route('hub.dashboard') }}" class="hub-btn">Voltar ao dashboard</a>
    </div>
@endsection

