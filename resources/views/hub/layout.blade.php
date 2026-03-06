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
                    Voltrune Hub | Área do Cliente
                </a>

                <a href="{{ route('hub.login') }}" class="hub-btn">
                    Encerrar sessão
                </a>
            </div>
        </header>

        <div class="hub-layout">
            <aside class="hub-sidebar">
                <nav class="hub-nav">
                    <a href="{{ route('hub.dashboard') }}" class="hub-nav__link {{ request()->routeIs('hub.dashboard') ? 'is-active' : '' }}">
                        Visão geral
                    </a>
                    <a href="{{ route('hub.products') }}" class="hub-nav__link {{ request()->routeIs('hub.products') ? 'is-active' : '' }}">
                        Aplicativos contratados
                    </a>
                    <a href="{{ route('hub.account') }}" class="hub-nav__link {{ request()->routeIs('hub.account') ? 'is-active' : '' }}">
                        Assinatura e acesso
                    </a>
                    <a href="{{ route('hub.help') }}" class="hub-nav__link {{ request()->routeIs('hub.help') ? 'is-active' : '' }}">
                        Suporte
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
