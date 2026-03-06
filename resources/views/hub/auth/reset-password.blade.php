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
            <p>Defina uma nova senha para restabelecer o acesso ao ambiente de cliente.</p>

            <form class="hub-auth-form" action="#" method="get">
                <div>
                    <label for="password" class="hub-auth-label">Nova senha</label>
                    <input id="password" name="password" type="password" placeholder="********" class="hub-auth-input" />
                </div>

                <div>
                    <label for="password_confirmation" class="hub-auth-label">Confirmar nova senha</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" placeholder="********" class="hub-auth-input" />
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
