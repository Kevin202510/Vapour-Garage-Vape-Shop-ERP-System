<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Vapour Garage') }} — {{ $title ?? 'Dashboard' }}</title>
    {{-- Apply theme before CSS loads to prevent flash --}}
    <script>(function(){if(localStorage.getItem('vg_theme')==='dark')document.documentElement.classList.add('dark');})()</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Russo+One&family=Exo+2:ital,wght@0,300;0,400;0,500;0,600;0,800;1,300;1,600&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full antialiased" x-data="{ sidebarOpen: false }">

    <div class="relative flex h-full">

        {{-- Mobile backdrop --}}
        <div x-show="sidebarOpen"
             @click="sidebarOpen = false"
             x-transition:enter="transition-opacity duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[90] bg-ink/50 md:hidden"
             style="display:none">
        </div>

        @include('layouts.sidebar')

        <div class="flex flex-col flex-1 min-w-0 overflow-hidden">
            @include('layouts.topbar')

            <main class="flex-1 overflow-y-auto px-4 py-4 md:px-8 md:py-7 pb-16 page-enter bg-cream">

                @if(session('success'))
                    <x-alert type="success" :message="session('success')" class="mb-5" />
                @endif
                @if(session('error'))
                    <x-alert type="error" :message="session('error')" class="mb-5" />
                @endif
                @if($errors->any())
                    <x-alert type="error" :message="$errors->first()" class="mb-5" />
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
