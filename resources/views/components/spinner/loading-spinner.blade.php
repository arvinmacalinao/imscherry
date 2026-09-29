@props([
    // optional: only spin for these Livewire actions/properties (comma separated), e.g. "search,perPage"
    'target' => null,
])

<div class="d-flex justify-content-center">
    <div wire:loading @if ($target) wire:target="{{ $target }}" @endif class="spinner-border text-muted" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>
