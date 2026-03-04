@extends('layouts.app')

@section('title', 'Hospedagem Voltrune | Portal em configuração')
@section('meta_description', 'Portal de hospedagem da Voltrune em configuração. Fale no WhatsApp para contratação imediata.')
@section('canonical', route('portal'))

@section('content')
<section class="section page-hero">
    <div class="container narrow">
        <p class="eyebrow">Hospedagem da ordem Voltrune</p>
        <h1>Portal em configuração</h1>
        <p class="lead">Estamos finalizando o acesso do parceiro whitelabel. Enquanto isso, sua contratação pode ser feita direto no atendimento.</p>
        <a class="btn" href="{{ env('WHATSAPP_URL', 'https://wa.me/5511998479359') }}" target="_blank" rel="noopener">Falar no WhatsApp</a>
    </div>
</section>
@endsection
