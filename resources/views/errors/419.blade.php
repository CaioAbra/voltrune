@extends('errors.layout')

@section('title', '419 | Sessão expirada')
@section('heading', '419 — Sessão expirada')
@section('message', 'Sua sessão expirou por segurança. Atualize a página e faça login novamente para continuar.')

@section('primary_action')
    <a href="{{ route('hub.login') }}" class="error-btn">Entrar novamente</a>
@endsection

@section('secondary_action')
    <a href="{{ route('home') }}" class="error-btn error-btn--ghost">Voltar ao site</a>
@endsection
