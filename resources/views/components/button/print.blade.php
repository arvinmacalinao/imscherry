@props([
    'route'
])

<x-button {{ $attributes->class(['btn btn-success']) }} route="{{ $route }}">
    <x-icon.printer/>

    {{ $slot }}
</x-button>
