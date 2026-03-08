@extends('hub.layout')

@section('title', 'Assinatura e Acesso | Voltrune Hub')

@section('content')
    <h1>Assinatura e acesso</h1>
    <p>Gerencie o plano ativo, usuários autorizados e segurança da sua conta.</p>

    @if (session('password_status'))
        <div class="hub-alert hub-alert--success">
            {{ session('password_status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="hub-alert hub-alert--danger">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="hub-grid">
        <article class="hub-card">
            <h2>Status da assinatura</h2>
            <p>Plano mensal ativo. Renovação e histórico de cobrança disponíveis em breve.</p>
            <span class="hub-badge">Gestão em evolução</span>
        </article>

        <article class="hub-card">
            <h2>Usuários autorizados</h2>
            <p>Controle de permissão por equipe e perfil será liberado gradualmente.</p>
            <span class="hub-badge">Em breve</span>
        </article>

        <article class="hub-card">
            <h2>Segurança</h2>
            <p>Atualize a senha de acesso ao Hub sempre que necessário.</p>
            <form class="hub-auth-form" method="post" action="{{ route('hub.account.password.update') }}">
                @csrf

                <div>
                    <label for="current_password" class="hub-auth-label">Senha atual</label>
                    <div class="hub-password-field" data-password-toggle>
                        <input
                            id="current_password"
                            name="current_password"
                            type="password"
                            class="hub-auth-input"
                            data-password-input
                            autocomplete="current-password"
                            required
                        />
                        <button type="button" class="hub-password-toggle" data-password-trigger aria-label="Mostrar senha" aria-pressed="false">
                            <svg class="hub-password-toggle__icon hub-password-toggle__icon--show" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M1.5 12s3.8-6.5 10.5-6.5S22.5 12 22.5 12s-3.8 6.5-10.5 6.5S1.5 12 1.5 12z"></path>
                                <circle cx="12" cy="12" r="3.2"></circle>
                            </svg>
                            <svg class="hub-password-toggle__icon hub-password-toggle__icon--hide" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M3 3l18 18"></path>
                                <path d="M10.6 5.7A10.7 10.7 0 0 1 12 5.5C18.7 5.5 22.5 12 22.5 12a17.9 17.9 0 0 1-4.1 5.1"></path>
                                <path d="M6.1 6.1A18.6 18.6 0 0 0 1.5 12s3.8 6.5 10.5 6.5c1.5 0 2.8-.3 4-.8"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div>
                    <label for="password" class="hub-auth-label">Nova senha</label>
                    <div class="hub-password-field" data-password-toggle>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            class="hub-auth-input"
                            data-password-input
                            autocomplete="new-password"
                            required
                        />
                        <button type="button" class="hub-password-toggle" data-password-trigger aria-label="Mostrar senha" aria-pressed="false">
                            <svg class="hub-password-toggle__icon hub-password-toggle__icon--show" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M1.5 12s3.8-6.5 10.5-6.5S22.5 12 22.5 12s-3.8 6.5-10.5 6.5S1.5 12 1.5 12z"></path>
                                <circle cx="12" cy="12" r="3.2"></circle>
                            </svg>
                            <svg class="hub-password-toggle__icon hub-password-toggle__icon--hide" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M3 3l18 18"></path>
                                <path d="M10.6 5.7A10.7 10.7 0 0 1 12 5.5C18.7 5.5 22.5 12 22.5 12a17.9 17.9 0 0 1-4.1 5.1"></path>
                                <path d="M6.1 6.1A18.6 18.6 0 0 0 1.5 12s3.8 6.5 10.5 6.5c1.5 0 2.8-.3 4-.8"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div>
                    <label for="password_confirmation" class="hub-auth-label">Confirmar nova senha</label>
                    <div class="hub-password-field" data-password-toggle>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            class="hub-auth-input"
                            data-password-input
                            autocomplete="new-password"
                            required
                        />
                        <button type="button" class="hub-password-toggle" data-password-trigger aria-label="Mostrar senha" aria-pressed="false">
                            <svg class="hub-password-toggle__icon hub-password-toggle__icon--show" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M1.5 12s3.8-6.5 10.5-6.5S22.5 12 22.5 12s-3.8 6.5-10.5 6.5S1.5 12 1.5 12z"></path>
                                <circle cx="12" cy="12" r="3.2"></circle>
                            </svg>
                            <svg class="hub-password-toggle__icon hub-password-toggle__icon--hide" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M3 3l18 18"></path>
                                <path d="M10.6 5.7A10.7 10.7 0 0 1 12 5.5C18.7 5.5 22.5 12 22.5 12a17.9 17.9 0 0 1-4.1 5.1"></path>
                                <path d="M6.1 6.1A18.6 18.6 0 0 0 1.5 12s3.8 6.5 10.5 6.5c1.5 0 2.8-.3 4-.8"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="hub-btn">Atualizar senha</button>
            </form>
        </article>
    </div>
@endsection
