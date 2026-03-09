@extends('hub.layout')

@section('content')
    <section class="hub-card">
        <p class="hub-note">Produto ativo: Solar</p>
        <h1>{{ $pageTitle ?? 'Solar' }}</h1>
        <p>Fundacao inicial do modulo Solar dentro do mesmo Laravel da plataforma Voltrune.</p>

        <div class="hub-actions">
            @foreach ($navigationItems as $navigationItem)
                <a
                    href="{{ route($navigationItem['route']) }}"
                    class="hub-btn {{ request()->routeIs($navigationItem['active']) ? '' : 'hub-btn--subtle' }}"
                >
                    {{ $navigationItem['label'] }}
                </a>
            @endforeach
        </div>
    </section>

    @yield('solar-content')
@endsection
