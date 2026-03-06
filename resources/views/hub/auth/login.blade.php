<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso do Cliente | Voltrune Hub</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body>
    <main class="hub-auth">
        <div class="hub-auth-card">
            <h1>Acessar área do cliente</h1>
            <p>Use seu e-mail e senha para entrar no ambiente interno da Voltrune.</p>

            <form class="hub-auth-form" action="#" method="get">
                <div>
                    <label for="email" class="hub-auth-label">E-mail de acesso</label>
                    <input id="email" name="email" type="email" placeholder="voce@empresa.com" class="hub-auth-input" />
                </div>

                <div>
                    <label for="password" class="hub-auth-label">Senha</label>
                    <input id="password" name="password" type="password" placeholder="********" class="hub-auth-input" />
                </div>

                <button type="submit" class="hub-btn">
                    Entrar no hub
                </button>

                <div class="hub-auth-form__meta">
                    <a href="{{ route('hub.forgot-password') }}" class="hub-auth-inline-link">Esqueci minha senha</a>
                </div>
            </form>

            <div class="hub-auth-assist">
                <span>Não tenho acesso.</span>
                <a href="{{ route('contato') }}">Solicitar liberação</a>
            </div>
        </div>
    </main>
</body>
</html>
