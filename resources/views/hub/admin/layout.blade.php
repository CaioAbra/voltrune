<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Painel Interno Voltrune')</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div class="hub-shell hub-admin-shell">
        <header class="hub-topbar">
            <div class="hub-topbar__inner">
                <a href="{{ route('hub.admin.dashboard') }}" class="hub-brand">
                    Painel Interno Voltrune
                </a>

                <div class="hub-actions">
                    <a href="{{ route('hub.dashboard') }}" class="hub-btn hub-btn--subtle">Hub do cliente</a>
                    <form action="{{ route('hub.logout') }}" method="post">
                        @csrf
                        <button type="submit" class="hub-btn">Encerrar sessão</button>
                    </form>
                </div>
            </div>
        </header>

        <div class="hub-layout">
            <aside class="hub-sidebar">
                <nav class="hub-nav">
                    <a href="{{ route('hub.admin.dashboard') }}" class="hub-nav__link {{ request()->routeIs('hub.admin.dashboard') || request()->routeIs('hub.admin.home') ? 'is-active' : '' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('hub.admin.companies.index') }}" class="hub-nav__link {{ request()->routeIs('hub.admin.companies.index') ? 'is-active' : '' }}">
                        Clientes
                    </a>
                    <a href="{{ route('hub.admin.contracts.index') }}" class="hub-nav__link {{ request()->routeIs('hub.admin.contracts.index') ? 'is-active' : '' }}">
                        Contratações
                    </a>
                    <a href="{{ route('hub.admin.billing.index') }}" class="hub-nav__link {{ request()->routeIs('hub.admin.billing.index') ? 'is-active' : '' }}">
                        Cobrança
                    </a>
                    <a href="{{ route('hub.admin.access.index') }}" class="hub-nav__link {{ request()->routeIs('hub.admin.access.index') ? 'is-active' : '' }}">
                        Acessos
                    </a>
                    <a href="{{ route('hub.admin.account.edit') }}" class="hub-nav__link {{ request()->routeIs('hub.admin.account.*') ? 'is-active' : '' }}">
                        Conta
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