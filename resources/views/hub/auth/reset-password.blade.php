<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha | Voltrune Hub</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body>
    <main class="hub-auth">
        <div class="hub-auth-card">
            <h1>Redefinir senha</h1>
            <p>Defina uma nova senha para restabelecer o acesso ao ambiente do Hub.</p>

            @if ($errors->any())
                <div class="hub-alert hub-alert--danger">{{ $errors->first() }}</div>
            @endif

            <form class="hub-auth-form" action="{{ route('hub.password.update') }}" method="post">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label for="email" class="hub-auth-label">E-mail de acesso</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email', $email) }}"
                        placeholder="voce@empresa.com"
                        class="hub-auth-input"
                        required
                        autofocus
                    />
                </div>

                <div>
                    <label for="password" class="hub-auth-label">Nova senha</label>
                    <div class="hub-password-field" data-password-toggle>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            placeholder="********"
                            class="hub-auth-input"
                            data-password-input
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
                            placeholder="********"
                            class="hub-auth-input"
                            data-password-input
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

                <button type="submit" class="hub-btn">
                    Salvar nova senha
                </button>
            </form>

            <div class="hub-auth-links">
                <a href="{{ route('hub.login') }}">Voltar para o login</a>
            </div>
        </div>
    </main>
</body>
</html>

