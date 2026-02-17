<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Scan Page')</title>
    @livewireStyles
</head>
<body>
    {{ $slot }} {{-- Livewire page component injects content here --}}

    @livewireScripts
</body>
</html>
