@extends('solar.layout')

@section('title', 'Editar cliente | Solar')

@section('solar-content')
    <section class="hub-card">
        <h2>Cliente contratante</h2>
        <p class="hub-note">Atualize os dados comerciais do contratante. Os dados tecnicos do local ficam nos projetos vinculados.</p>

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
                <input
                    id="name"
                    name="name"
                    type="text"
                    class="hub-auth-input"
                    value="{{ old('name', $customer->name) }}"
                    required
                >
                @error('name')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
            </div>

            <div class="hub-grid hub-grid--billing">
                <div>
                    <label for="email" class="hub-auth-label">Email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        class="hub-auth-input"
                        value="{{ old('email', $customer->email) }}"
                    >
                    @error('email')
                        <p class="hub-note">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="hub-auth-label">Telefone</label>
                    <input
                        id="phone"
                        name="phone"
                        type="text"
                        class="hub-auth-input"
                        value="{{ old('phone', $customer->phone) }}"
                    >
                    @error('phone')
                        <p class="hub-note">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="hub-grid hub-grid--billing">
                <div>
                    <label for="document" class="hub-auth-label">Documento</label>
                    <input
                        id="document"
                        name="document"
                        type="text"
                        class="hub-auth-input"
                        value="{{ old('document', $customer->document) }}"
                    >
                    @error('document')
                        <p class="hub-note">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="city" class="hub-auth-label">Cidade</label>
                    <input
                        id="city"
                        name="city"
                        type="text"
                        class="hub-auth-input"
                        value="{{ old('city', $customer->city) }}"
                    >
                    @error('city')
                        <p class="hub-note">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="state" class="hub-auth-label">UF</label>
                <input
                    id="state"
                    name="state"
                    type="text"
                    class="hub-auth-input"
                    value="{{ old('state', $customer->state) }}"
                    maxlength="2"
                >
                @error('state')
                    <p class="hub-note">{{ $message }}</p>
                @enderror
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
@endsection
