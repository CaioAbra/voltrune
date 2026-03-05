<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Voltrune Hub')</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div class="hub-shell">
        <header class="hub-topbar">
            <div class="hub-topbar__inner">
                <a href="{{ route('hub.dashboard') }}" class="hub-brand">
                    Voltrune Hub
                </a>

                <a href="{{ route('hub.login') }}" class="hub-btn">
                    Sair
                </a>
            </div>
        </header>

        <div class="hub-layout">
            <aside class="hub-sidebar">
                <nav class="hub-nav">
                    <a href="{{ route('hub.dashboard') }}" class="hub-nav__link {{ request()->routeIs('hub.dashboard') ? 'is-active' : '' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('hub.products') }}" class="hub-nav__link {{ request()->routeIs('hub.products') ? 'is-active' : '' }}">
                        Sistemas
                    </a>
                    <a href="{{ route('hub.account') }}" class="hub-nav__link {{ request()->routeIs('hub.account') ? 'is-active' : '' }}">
                        Conta
                    </a>
                    <a href="{{ route('hub.help') }}" class="hub-nav__link {{ request()->routeIs('hub.help') ? 'is-active' : '' }}">
                        Ajuda
                    </a>
                </nav>
            </aside>

            <main class="hub-main">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
