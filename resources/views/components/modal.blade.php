@props([
    'id',
    'title',
])

<div id="{{ $id }}" class="modal" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="{{ $id }}-title" data-modal>
    <div class="modal-backdrop" data-modal-close></div>
    <div class="modal-panel" role="document">
        <button class="modal-close" type="button" aria-label="Fechar" data-modal-close>
            <span class="material-symbols-rounded" aria-hidden="true">close</span>
        </button>
        <h3 id="{{ $id }}-title">{{ $title }}</h3>
        {{ $slot }}
    </div>
</div>
