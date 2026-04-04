<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'VapeHub ERP') }} — {{ $title ?? 'Dashboard' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400;1,700&family=IBM+Plex+Mono:wght@300;400;500&family=Epilogue:wght@300;400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    @stack('scripts')
</head>
<body class="h-full bg-cream font-sans antialiased" x-data="{ sidebarOpen: false }">

{{-- Grain texture --}}
<div class="fixed inset-0 pointer-events-none z-50 opacity-[0.025]"
     style="background-image:url('data:image/svg+xml,%3Csvg viewBox=\'0 0 256 256\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cfilter id=\'n\'%3E%3CfeTurbulence type=\'fractalNoise\' baseFrequency=\'0.9\' numOctaves=\'4\' stitchTiles=\'stitch\'/%3E%3C/filter%3E%3Crect width=\'100%25\' height=\'100%25\' filter=\'url(%23n)\'/%3E%3C/svg%3E');background-size:200px"></div>

<div class="flex h-full">

    {{-- Sidebar --}}
    @include('layouts.sidebar')

    {{-- Main --}}
    <div class="flex flex-col flex-1 min-w-0 overflow-hidden bg-paper">

        {{-- Topbar --}}
        @include('layouts.topbar')

        {{-- Page content --}}
        <main class="flex-1 overflow-y-auto px-10 py-9 pb-16">

            {{-- Flash messages --}}
            @if (session('success'))
                <x-alert type="success" :message="session('success')" class="mb-6" />
            @endif
            @if (session('error'))
                <x-alert type="error" :message="session('error')" class="mb-6" />
            @endif
            @if ($errors->any())
                <x-alert type="error" :message="$errors->first()" class="mb-6" />
            @endif

            {{ $slot }}
        </main>
    </div>
</div>
</body>
</html>
