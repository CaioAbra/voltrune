@extends('errors.layout')

@section('title', '503 | Em manutenção')
@section('heading', '503 — Serviço indisponível')
@section('message', 'Estamos em manutenção para melhorar o ambiente. Tente novamente em alguns minutos.')

@section('primary_action')
    <a href="{{ route('home') }}" class="error-btn">Voltar ao site</a>
@endsection

@section('secondary_action')
    <a href="{{ route('contato') }}" class="error-btn error-btn--ghost">Falar com a Voltrune</a>
@endsection
