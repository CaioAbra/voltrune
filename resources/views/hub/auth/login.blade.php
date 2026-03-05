<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar | Voltrune Hub</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body>
    <main class="hub-auth">
        <div class="hub-auth-card">
            <h1>Entrar no Voltrune Hub</h1>
            <p>Tela mock de autenticação (sem integração).</p>

            <form class="hub-auth-form" action="#" method="get">
                <div>
                    <label for="email" class="hub-auth-label">Email</label>
                    <input id="email" name="email" type="email" placeholder="voce@exemplo.com" class="hub-auth-input" />
                </div>

                <div>
                    <label for="password" class="hub-auth-label">Senha</label>
                    <input id="password" name="password" type="password" placeholder="********" class="hub-auth-input" />
                </div>

                <button type="submit" class="hub-btn">
                    Entrar (mock)
                </button>
            </form>

            <div class="hub-auth-links">
                <a href="{{ route('hub.forgot-password') }}">Esqueci minha senha</a>
                <a href="{{ url('register') }}">Criar conta</a>
                <a href="{{ route('hub.dashboard') }}">Voltar ao Hub</a>
            </div>
        </div>
    </main>
</body>
</html>
