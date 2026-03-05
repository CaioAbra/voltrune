<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esqueci Minha Senha | Voltrune Hub</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body>
    <main class="hub-auth">
        <div class="hub-auth-card">
            <h1>Esqueci minha senha</h1>
            <p>Informe seu email para envio de link (mock).</p>

            <form class="hub-auth-form" action="#" method="get">
                <div>
                    <label for="email" class="hub-auth-label">Email</label>
                    <input id="email" name="email" type="email" placeholder="voce@exemplo.com" class="hub-auth-input" />
                </div>

                <button type="submit" class="hub-btn">
                    Enviar link (mock)
                </button>
            </form>

            <div class="hub-auth-links">
                <a href="{{ route('hub.login') }}">Voltar para login</a>
            </div>
        </div>
    </main>
</body>
</html>
