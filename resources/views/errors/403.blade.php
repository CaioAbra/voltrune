@extends('errors.layout')

@section('title', '403 | Acesso negado')
@section('heading', '403 — Acesso negado')
@section('message', 'Você não possui permissão para acessar esta área. Se precisar, entre em contato com a equipe Voltrune.')

@section('primary_action')
    <a href="{{ route('hub.dashboard') }}" class="error-btn">Área do cliente</a>
@endsection

@section('secondary_action')
    <a href="{{ route('home') }}" class="error-btn error-btn--ghost">Voltar ao site</a>
@endsection
