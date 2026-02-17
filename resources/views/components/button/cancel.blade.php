@props([
    'route'
])

<x-button {{ $attributes->class(['btn btn-warning']) }} route="{{ $route }}">
    <x-icon.cancel/>

    {{ $slot }}
</x-button>
