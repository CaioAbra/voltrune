@extends('layouts.app')

@section('title', 'Vigilante Juridico em breve')
@section('meta_description', 'Vigilante e o sistema da Voltrune para apoiar rotinas juridicas. Cadastre seu interesse para receber aviso de abertura.')
@section('canonical', route('vigilante'))

@section('content')
<section class="section page-hero">
    <div class="container narrow">
        <p class="eyebrow">Projeto Vigilante</p>
        <h1>Vigilante esta em preparacao para o setor juridico.</h1>
        <p class="lead">Estamos finalizando um sistema para apoiar advogados no controle de rotinas e prazos. Em breve o acesso principal sera em <strong>vigilante.voltrune.com</strong>.</p>
    </div>
</section>

<section class="section">
    <div class="container narrow">
        @if (session('vigilante_status'))
            <div class="flash-success" role="status">{{ session('vigilante_status') }}</div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="flash-error" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="contact-form" method="POST" action="{{ route('vigilante.store', [], false) }}">
            @csrf
            <label for="name">Nome</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="120">

            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" maxlength="160">

            <label for="whatsapp">WhatsApp</label>
            <input id="whatsapp" name="whatsapp" type="text" value="{{ old('whatsapp') }}" maxlength="30" inputmode="tel" autocomplete="tel" placeholder="(11) 99847-9359" data-phone-mask>

            <label for="interest">Como pretende usar o Vigilante?</label>
            <textarea id="interest" name="interest" rows="4" required maxlength="300">{{ old('interest') }}</textarea>

            <div class="hp-field" aria-hidden="true">
                <label for="company_website">Site da empresa</label>
                <input id="company_website" name="company_website" type="text" tabindex="-1" autocomplete="off">
            </div>

            <button class="btn" type="submit">Quero ser avisado</button>
        </form>
    </div>
</section>
@endsection
