<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#15120f">

    @php
        $baseTitle = 'Voltrune | Ordem de Artesaos Digitais';
        $title = trim($__env->yieldContent('title'));
        $metaTitle = $title !== '' ? $title . ' | Voltrune' : $baseTitle;
        $description = trim($__env->yieldContent('meta_description')) ?: 'Voltrune cria websites, apps e estrategias de midia com design premium, foco em performance e SEO para gerar vendas.';
        $canonical = trim($__env->yieldContent('canonical')) ?: url()->current();
        $ogTitle = trim($__env->yieldContent('og_title')) ?: $metaTitle;
        $ogDescription = trim($__env->yieldContent('og_description')) ?: $description;
        $ogImage = trim($__env->yieldContent('og_image')) ?: asset('og/voltrune.jpg');
        $whatsappUrl = env('WHATSAPP_URL', 'https://wa.me/5511998479359');
        $organizationSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'Voltrune',
            'url' => config('app.url'),
            'logo' => asset('og/voltrune.jpg'),
            'sameAs' => [$whatsappUrl],
        ];
        $websiteSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => 'Voltrune',
            'url' => config('app.url'),
        ];
    @endphp

    <title>{{ $metaTitle }}</title>
    <meta name="description" content="{{ $description }}">
    <link rel="canonical" href="{{ $canonical }}">
    <meta name="robots" content="index,follow">

    <meta property="og:type" content="website">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:site_name" content="Voltrune">
    <meta property="og:title" content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $ogDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $ogImage }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $ogTitle }}">
    <meta name="twitter:description" content="{{ $ogDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Cinzel:wght@500;600&family=Manrope:wght@400;500;600;700&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,500,0,0" rel="stylesheet">

    @vite(['resources/scss/app.scss', 'resources/js/app.js'])

    <script type="application/ld+json">
        @json($organizationSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    </script>
    <script type="application/ld+json">
        @json($websiteSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    </script>
    @stack('structured-data')

    @if ($gaId = env('GA_MEASUREMENT_ID'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ $gaId }}');
        </script>
    @endif

    @if ($pixelId = env('META_PIXEL_ID'))
        <script>
            !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
            n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}
            (window, document,'script','https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '{{ $pixelId }}');
            fbq('track', 'PageView');
        </script>
        <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{ $pixelId }}&ev=PageView&noscript=1" alt=""></noscript>
    @endif
</head>
<body>
    <x-navbar />

    <main>
        @yield('content')
    </main>

    <x-footer />
</body>
</html>
