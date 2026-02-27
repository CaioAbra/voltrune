@props([
    'title',
    'description',
    'tags' => [],
    'result' => null,
    'image' => null,
    'seal' => null,
    'class' => '',
])

@php
    $tagsString = implode(',', $tags);
@endphp

<article class="portfolio-card quest-card {{ $class }}" data-tags="{{ $tagsString }}" data-reveal>
    @if ($image)
        <img src="{{ $image }}" alt="Projeto {{ $title }}" width="640" height="420" loading="lazy" decoding="async">
    @endif
    @if ($seal)
        <span class="quest-seal" aria-label="Status {{ $seal }}">{{ $seal }}</span>
    @endif
    <div class="portfolio-content">
        <h3>{{ $title }}</h3>
        <p>{{ $description }}</p>
        <div class="tag-list quest-tags">
            @foreach ($tags as $tag)
                <span>{{ $tag }}</span>
            @endforeach
        </div>
        @if ($result)
            <p class="portfolio-result">{{ $result }}</p>
        @endif
        <p class="portfolio-meta">Disponibilidade de detalhes sob consulta.</p>
    </div>
</article>
