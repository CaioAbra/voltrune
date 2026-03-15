<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#11191c">
    <title>@yield('title', 'Voltrune Solar')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Marcellus&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body class="solar-body">
    @php
        $currentCompany = \App\Support\CurrentCompanyContext::resolve(auth()->user(), request()->session());
        $availableCompanies = auth()->check()
            ? \App\Support\CurrentCompanyContext::available(auth()->user())
            : collect();
    @endphp

    <div class="solar-app">
        <header class="solar-topbar">
            <div class="solar-brand">
                <span class="solar-brand__eyebrow">Produto SaaS</span>
                <a href="{{ route('solar.dashboard') }}" class="solar-brand__title">
                    <span>Voltrune</span>
                    <strong>Solar</strong>
                </a>
                <p class="solar-brand__subtitle">Gestão comercial e operacional para energia solar.</p>
            </div>

            <div class="hub-actions">
                @if ($availableCompanies->count() > 1)
                    <form action="{{ route('workspace.company.update') }}" method="POST">
                        @csrf
                        <label class="sr-only" for="solar-active-company">Empresa ativa</label>
                        <select id="solar-active-company" name="company_id" class="hub-auth-input" onchange="this.form.submit()">
                            @foreach ($availableCompanies as $companyOption)
                                <option value="{{ $companyOption->id }}" @selected($currentCompany?->id === $companyOption->id)>
                                    {{ $companyOption->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                @endif

                <a href="{{ route('hub.dashboard') }}" class="solar-backlink">Voltar ao Hub</a>
            </div>
        </header>

        <div class="solar-workspace">
            <aside class="solar-sidebar">
                <p class="solar-sidebar__label">Fluxo do produto</p>

                <nav class="solar-nav">
                    @foreach ($navigationItems as $navigationItem)
                        <a
                            href="{{ route($navigationItem['route']) }}"
                            class="solar-nav__link {{ request()->routeIs($navigationItem['active']) ? 'is-active' : '' }}"
                        >
                            <span class="solar-nav__title">{{ $navigationItem['label'] }}</span>
                            <span class="solar-nav__meta">{{ $navigationItem['description'] }}</span>
                        </a>
                    @endforeach
                </nav>
            </aside>

            <main class="solar-main">
                <section class="solar-hero">
                    <p class="solar-hero__eyebrow">Voltrune Solar</p>
                    <h1 class="solar-hero__title">{{ $pageTitle ?? 'Solar' }}</h1>
                    <p class="solar-hero__description">
                        {{ $pageDescription ?? 'Fluxo dedicado do produto Solar dentro da plataforma Voltrune.' }}
                    </p>

                    @if ($currentCompany)
                        <div class="solar-hero__company">
                            <span>Empresa ativa:</span>
                            <strong>{{ $currentCompany->name }}</strong>
                        </div>
                    @endif
                </section>

                @yield('solar-content')
            </main>
        </div>
    </div>
</body>
</html>
