@extends('solar.layout')

@section('title', 'Editar cliente | Solar')

@section('solar-content')
    <section class="solar-page-shell">
        <section class="hub-card hub-card--subtle solar-page-intro">
            <div class="solar-page-intro__header">
                <div class="solar-page-intro__copy">
                    <p class="solar-section-eyebrow">Editar cliente</p>
                    <h2>Atualize a base comercial do cliente</h2>
                    <p class="hub-note">Mantenha os dados do cliente atualizados para evitar ruido no atendimento e acelerar novos projetos, simulacoes e orcamentos.</p>
                </div>

                <div class="solar-page-intro__meta">
                    <span class="solar-project-showcase__status-label">Contexto</span>
                    <strong>Cliente pronto para novos projetos</strong>
                    <p>Os dados tecnicos do local continuam nos projetos vinculados. Aqui o foco e a base comercial.</p>
                </div>
            </div>
        </section>

        <section class="hub-card solar-page-panel">
            <div class="solar-page-panel__header">
                <h2>Dados do cliente</h2>
                <p class="hub-note">Revise contato, documento e observacoes para manter a base comercial sempre atual.</p>
            </div>

            <div class="hub-actions">
                <a href="{{ route('solar.customers.index') }}" class="hub-btn hub-btn--subtle">Voltar para clientes</a>
            </div>

            @if ($errors->any())
                <div class="hub-alert hub-alert--danger">
                    <strong>Revise os campos do formulario.</strong>
                </div>
            @endif

            <form action="{{ route('solar.customers.update', $customer->id) }}" method="post" class="hub-auth-form">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="hub-auth-label">Nome *</label>
                    <input id="name" name="name" type="text" class="hub-auth-input" value="{{ old('name', $customer->name) }}" required>
                    @error('name')
                        <p class="hub-note">{{ $message }}</p>
                    @enderror
                </div>

                <div class="hub-grid hub-grid--billing">
                    <div>
                        <label for="email" class="hub-auth-label">Email</label>
                        <input id="email" name="email" type="email" class="hub-auth-input" value="{{ old('email', $customer->email) }}">
                        @error('email')
                            <p class="hub-note">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="hub-auth-label">Telefone</label>
                        <input id="phone" name="phone" type="text" class="hub-auth-input" value="{{ old('phone', $customer->phone) }}">
                        @error('phone')
                            <p class="hub-note">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="hub-grid hub-grid--billing">
                    <div>
                        <label for="document" class="hub-auth-label">Documento</label>
                        <input id="document" name="document" type="text" class="hub-auth-input" value="{{ old('document', $customer->document) }}">
                        @error('document')
                            <p class="hub-note">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="city" class="hub-auth-label">Cidade</label>
                        <input id="city" name="city" type="text" class="hub-auth-input" value="{{ old('city', $customer->city) }}">
                        @error('city')
                            <p class="hub-note">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="hub-grid hub-grid--billing">
                    <div>
                        <label for="state" class="hub-auth-label">UF</label>
                        <input id="state" name="state" type="text" class="hub-auth-input" value="{{ old('state', $customer->state) }}" maxlength="2">
                        @error('state')
                            <p class="hub-note">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="notes" class="hub-auth-label">Observacoes</label>
                    <textarea id="notes" name="notes" class="hub-auth-input">{{ old('notes', $customer->notes) }}</textarea>
                    @error('notes')
                        <p class="hub-note">{{ $message }}</p>
                    @enderror
                </div>

                <div class="hub-actions">
                    <button type="submit" class="hub-btn">Salvar alteracoes</button>
                    <a href="{{ route('solar.customers.index') }}" class="hub-btn hub-btn--subtle">Cancelar</a>
                </div>
            </form>
        </section>
    </section>
@endsection
