@extends('layouts.app')

@section('title', 'Contato')
@section('meta_description', 'Solicite um orcamento com a Voltrune para websites, apps, midia, branding e hospedagem.')
@section('canonical', route('contato'))

@section('content')
<section class="section page-hero">
    <div class="container narrow">
        <p class="eyebrow">Contato Voltrune</p>
        <h1>Envie sua missao e receba um plano de execucao.</h1>
        <p class="lead">Respondemos com rapidez, clareza de escopo e foco em resultado comercial.</p>
    </div>
</section>

<section class="section">
    <div class="container narrow">
        @if (session('status'))
            <div class="flash-success" role="status">{{ session('status') }}</div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="flash-error" role="alert">
                <p>Verifique os campos abaixo:</p>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="contact-form" method="POST" action="{{ route('contato.store', [], false) }}">
            @csrf
            <label for="name">Nome</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="120">

            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="160">

            <label for="whatsapp">WhatsApp</label>
            <input id="whatsapp" name="whatsapp" type="text" value="{{ old('whatsapp') }}" required maxlength="30" inputmode="tel" autocomplete="tel" placeholder="(00) 00000-0000" data-phone-mask>

            <label for="subject">Assunto</label>
            <select id="subject" name="subject" required>
                <option value="" disabled {{ old('subject') ? '' : 'selected' }}>Selecione um servico</option>
                @foreach ([
                    'Websites e Landings',
                    'Apps e Dashboards',
                    'Trafego Pago e Midia',
                    'Marca, Banner e Logo',
                    'Hospedagem e Manutencao',
                    'Vigilante Juridico',
                    'Outro',
                ] as $serviceOption)
                    <option value="{{ $serviceOption }}" {{ old('subject') === $serviceOption ? 'selected' : '' }}>
                        {{ $serviceOption }}
                    </option>
                @endforeach
            </select>

            <label for="message">Mensagem</label>
            <textarea id="message" name="message" rows="6" required maxlength="2000">{{ old('message') }}</textarea>

            <div class="hp-field" aria-hidden="true">
                <label for="company_website">Site da empresa</label>
                <input id="company_website" name="company_website" type="text" tabindex="-1" autocomplete="off">
            </div>

            <button class="btn" type="submit">Enviar mensagem</button>
        </form>
    </div>
</section>
@endsection
