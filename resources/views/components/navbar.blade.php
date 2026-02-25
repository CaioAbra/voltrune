<header class="site-header" data-header>
    <div class="container nav-wrap">
        <a class="brand" href="{{ route('home') }}" aria-label="Voltrune - inicio">
            <span class="brand-rune" aria-hidden="true">V</span>
            <span class="brand-text">Voltrune</span>
        </a>

        <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="main-nav" data-menu-toggle>
            <span class="material-symbols-rounded" aria-hidden="true">menu</span>
            <span class="sr-only">Abrir menu</span>
        </button>

        <nav id="main-nav" class="main-nav" data-menu>
            <a href="{{ route('home') }}" @class(['nav-link', 'active' => request()->routeIs('home')])>Inicio</a>
            <a href="{{ route('servicos') }}" @class(['nav-link', 'active' => request()->routeIs('servicos')])>Servicos</a>
            <a href="{{ route('portfolio') }}" @class(['nav-link', 'active' => request()->routeIs('portfolio')])>Missoes</a>
            <a href="{{ route('sistemas') }}" @class(['nav-link', 'active' => request()->routeIs('sistemas*') || request()->routeIs('vigilante')])>Sistemas</a>
            <a href="{{ route('contato') }}" class="nav-cta">Contato</a>
        </nav>
    </div>
</header>
