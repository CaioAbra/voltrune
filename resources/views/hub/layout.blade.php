<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Voltrune Hub')</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body>
    @php
        $isHubAdmin = \App\Support\HubAdminAccess::isAdmin(auth()->user());
    @endphp

    <div class="hub-shell">
        <header class="hub-topbar">
            <div class="hub-topbar__inner">
                <a href="{{ route('hub.dashboard') }}" class="hub-brand">
                    Voltrune Hub | Área do Cliente
                </a>

                @if (auth()->check())
                    <form action="{{ route('hub.logout') }}" method="post">
                        @csrf
                        <button type="submit" class="hub-btn">Encerrar sessão</button>
                    </form>
                @else
                    <a href="{{ route('hub.login') }}" class="hub-btn">Entrar</a>
                @endif
            </div>
        </header>

        <div class="hub-layout">
            <aside class="hub-sidebar">
                <nav class="hub-nav">
                    <a href="{{ route('hub.dashboard') }}" class="hub-nav__link {{ request()->routeIs('hub.dashboard') ? 'is-active' : '' }}">
                        Visão geral
                    </a>
                    <a href="{{ route('hub.products') }}" class="hub-nav__link {{ request()->routeIs('hub.products') ? 'is-active' : '' }}">
                        Sistemas
                    </a>
                    <a href="{{ route('hub.account') }}" class="hub-nav__link {{ request()->routeIs('hub.account') ? 'is-active' : '' }}">
                        Conta e acesso
                    </a>
                    <a href="{{ route('hub.billing') }}" class="hub-nav__link {{ request()->routeIs('hub.billing') ? 'is-active' : '' }}">
                        Billing / Assinatura
                    </a>
                    <a href="{{ route('hub.help') }}" class="hub-nav__link {{ request()->routeIs('hub.help') ? 'is-active' : '' }}">
                        Suporte
                    </a>
                    @if ($isHubAdmin)
                        <a href="{{ route('hub.admin.dashboard') }}" class="hub-nav__link {{ request()->routeIs('hub.admin.*') ? 'is-active' : '' }}">
                            Painel Interno
                        </a>
                    @endif
                </nav>
            </aside>

            <main class="hub-main">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
