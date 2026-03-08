@extends('errors.layout')

@section('title', '429 | Muitas tentativas')
@section('heading', '429 — Muitas tentativas')
@section('message', 'Recebemos muitas requisições em sequência. Aguarde alguns instantes e tente novamente.')

@section('primary_action')
    <a href="{{ url()->previous() }}" class="error-btn">Tentar novamente</a>
@endsection

@section('secondary_action')
    <a href="{{ route('home') }}" class="error-btn error-btn--ghost">Voltar ao site</a>
@endsection
