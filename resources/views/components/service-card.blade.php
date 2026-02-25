@props([
    'title',
    'icon' => 'shield',
    'excerpt',
    'modal',
])

<article class="service-card">
    <span class="material-symbols-rounded" aria-hidden="true">{{ $icon }}</span>
    <h3>{{ $title }}</h3>
    <p>{{ $excerpt }}</p>
    <button class="btn btn-ghost" type="button" data-modal-open="{{ $modal }}">Ver detalhes</button>
</article>
