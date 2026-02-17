@props([
    'title',
    'message',
    'button_label',
    'button_route',
    'button_label2' => null,
    'button_route2' => null,
])

<div class="empty">
    <div class="empty-icon">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <circle cx="12" cy="12" r="9" />
            <line x1="9" y1="10" x2="9.01" y2="10" />
            <line x1="15" y1="10" x2="15.01" y2="10" />
            <path d="M9.5 15.25a3.5 3.5 0 0 1 5 0" />
        </svg>
    </div>
    <p class="empty-title">
        {{ $title }}
    </p>
    <p class="empty-subtitle text-secondary">
        {{ $message }}
    </p>
    <div class="empty-action flex gap-2">
    <a href="{{ $button_route }}" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M12 5l0 14"></path><path d="M5 12l14 0"></path></svg>
        {{ $button_label }}
    </a>

    @if($button_label2 && $button_route2)
        <a href="{{ $button_route2 }}" class="btn btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M12 5l0 14"></path><path d="M5 12l14 0"></path></svg>
            {{ $button_label2 }}
        </a>
    @endif
</div>
</div>
