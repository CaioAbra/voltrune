@props([
    'title',
    'description',
    'tags' => [],
    'result' => null,
    'image' => null,
])

@php
    $tagsString = implode(',', $tags);
@endphp

<article class="portfolio-card" data-tags="{{ $tagsString }}">
    @if ($image)
        <img src="{{ $image }}" alt="Projeto {{ $title }}" width="640" height="420" loading="lazy" decoding="async">
    @endif
    <div class="portfolio-content">
        <h3>{{ $title }}</h3>
        <p>{{ $description }}</p>
        <div class="tag-list">
            @foreach ($tags as $tag)
                <span>{{ $tag }}</span>
            @endforeach
        </div>
        @if ($result)
            <p class="portfolio-result">{{ $result }}</p>
        @endif
        <a class="text-link" href="{{ route('contato') }}">Abrir missao</a>
    </div>
</article>
