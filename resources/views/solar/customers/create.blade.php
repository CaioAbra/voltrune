@extends('solar.layout')

@section('title', 'Novo cliente | Solar')

@section('solar-content')
    <section class="solar-page-shell">
        <section class="hub-card hub-card--subtle solar-page-intro">
            <div class="solar-page-intro__header">
                <div class="solar-page-intro__copy">
                    <p class="solar-section-eyebrow">Novo cliente</p>
                    <h2>Cadastre o cliente do atendimento</h2>
                    <p class="hub-note">Use este formulario para registrar a pessoa ou empresa atendida. O local da instalacao entra depois, na etapa de projeto.</p>
                </div>

                <div class="solar-page-intro__meta">
                    <span class="solar-project-showcase__status-label">Proximo passo</span>
                    <strong>Cliente pronto para virar projeto</strong>
                    <p>Depois de salvar, abra o projeto e preencha local, consumo e os dados base da instalacao.</p>
                </div>
            </div>
        </section>

        <section class="hub-card solar-page-panel">
            <div class="solar-page-panel__header">
                <h2>Dados do cliente</h2>
                <p class="hub-note">Preencha o minimo necessario para localizar o atendimento e seguir para o projeto sem retrabalho.</p>
            </div>

            <div class="hub-actions">
                <a href="{{ route('solar.customers.index') }}" class="hub-btn hub-btn--subtle">Voltar para clientes</a>
            </div>

            @if ($errors->any())
                <div class="hub-alert hub-alert--danger">
                    <strong>Revise os campos do formulario.</strong>
                </div>
            @endif

            <form action="{{ route('solar.customers.store') }}" method="post" class="hub-auth-form">
                @csrf

                <div>
                    <label for="name" class="hub-auth-label">Nome *</label>
                    <input id="name" name="name" type="text" class="hub-auth-input" value="{{ old('name') }}" required>
                    @error('name')
                        <p class="hub-note">{{ $message }}</p>
                    @enderror
                </div>

                <div class="hub-grid hub-grid--billing">
                    <div>
                        <label for="email" class="hub-auth-label">Email</label>
                        <input id="email" name="email" type="email" class="hub-auth-input" value="{{ old('email') }}">
                        @error('email')
                            <p class="hub-note">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="hub-auth-label">Telefone</label>
                        <input id="phone" name="phone" type="text" class="hub-auth-input" value="{{ old('phone') }}">
                        @error('phone')
                            <p class="hub-note">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="hub-grid hub-grid--billing">
                    <div>
                        <label for="document" class="hub-auth-label">Documento</label>
                        <input id="document" name="document" type="text" class="hub-auth-input" value="{{ old('document') }}">
                        @error('document')
                            <p class="hub-note">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="city" class="hub-auth-label">Cidade</label>
                        <input id="city" name="city" type="text" class="hub-auth-input" value="{{ old('city') }}">
                        @error('city')
                            <p class="hub-note">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="hub-grid hub-grid--billing">
                    <div>
                        <label for="state" class="hub-auth-label">UF</label>
                        <input id="state" name="state" type="text" class="hub-auth-input" value="{{ old('state') }}" maxlength="2">
                        @error('state')
                            <p class="hub-note">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="notes" class="hub-auth-label">Observacoes</label>
                    <textarea id="notes" name="notes" class="hub-auth-input">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="hub-note">{{ $message }}</p>
                    @enderror
                </div>

                <div class="hub-actions">
                    <button type="submit" class="hub-btn">Salvar cliente</button>
                    <a href="{{ route('solar.customers.index') }}" class="hub-btn hub-btn--subtle">Cancelar</a>
                </div>
            </form>
        </section>
    </section>
@endsection
