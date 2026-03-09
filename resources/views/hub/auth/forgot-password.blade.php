<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Acesso | Voltrune Hub</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body>
    <main class="hub-auth">
        <div class="hub-auth-card">
            <h1>Recuperar acesso</h1>
            <p>Informe seu e-mail para receber o link de redefinicao de senha.</p>

            @if (session('status'))
                <div class="hub-alert hub-alert--success">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="hub-alert hub-alert--danger">{{ $errors->first() }}</div>
            @endif

            <form class="hub-auth-form" action="{{ route('hub.password.email') }}" method="post">
                @csrf
                <div>
                    <label for="email" class="hub-auth-label">E-mail de acesso</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        placeholder="voce@empresa.com"
                        class="hub-auth-input"
                        required
                        autofocus
                    />
                </div>

                <button type="submit" class="hub-btn">
                    Enviar link
                </button>
            </form>

            <div class="hub-auth-links">
                <a href="{{ route('hub.login') }}">Voltar para o login</a>
            </div>
        </div>
    </main>
</body>
</html>

