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
        $currentCompany = \App\Support\CurrentCompanyContext::resolve(auth()->user(), request()->session());
        $availableCompanies = auth()->check()
            ? \App\Support\CurrentCompanyContext::available(auth()->user())
            : collect();
    @endphp

    <div class="hub-shell">
        <header class="hub-topbar">
            <div class="hub-topbar__inner">
                <a href="{{ route('hub.dashboard') }}" class="hub-brand">
                    Voltrune Hub | Área do Cliente
                </a>

                @if (auth()->check())
                    <div class="hub-actions">
                        @if ($availableCompanies->count() > 1)
                            <form action="{{ route('workspace.company.update') }}" method="post">
                                @csrf
                                <label class="sr-only" for="hub-active-company">Empresa ativa</label>
                                <select id="hub-active-company" name="company_id" class="hub-auth-input" onchange="this.form.submit()">
                                    @foreach ($availableCompanies as $companyOption)
                                        <option value="{{ $companyOption->id }}" @selected($currentCompany?->id === $companyOption->id)>
                                            {{ $companyOption->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        @endif

                        <form action="{{ route('hub.logout') }}" method="post">
                            @csrf
                            <button type="submit" class="hub-btn">Encerrar sessão</button>
                        </form>
                    </div>
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
