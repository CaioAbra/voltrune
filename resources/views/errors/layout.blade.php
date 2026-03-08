<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Voltrune | Erro')</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body>
    <main class="error-shell" data-error-shell>
        @hasSection('outer_eggs')
            @yield('outer_eggs')
        @endif

        <section class="error-card">
            <span class="error-kicker">Voltrune Hub</span>
            <h1 class="error-title">@yield('heading')</h1>
            <p class="error-copy">@yield('message')</p>

            @hasSection('illustration')
                @yield('illustration')
            @endif

            <div class="error-actions">
                @hasSection('primary_action')
                    @yield('primary_action')
                @else
                    <a href="{{ route('home') }}" class="error-btn">Voltar ao site</a>
                @endif

                @hasSection('secondary_action')
                    @yield('secondary_action')
                @endif
            </div>
        </section>
    </main>
</body>
</html>
