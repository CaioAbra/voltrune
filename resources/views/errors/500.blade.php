@extends('errors.layout')

@section('title', '500 | Erro interno')
@section('heading', '500 — Erro interno')
@section('message', 'Encontramos uma falha temporária ao processar sua solicitação. Nossa equipe já pode investigar o ocorrido.')

@section('primary_action')
    <a href="{{ route('home') }}" class="error-btn">Voltar ao site</a>
@endsection

@section('secondary_action')
    <a href="{{ route('hub.login') }}" class="error-btn error-btn--ghost">Entrar no Hub</a>
@endsection
