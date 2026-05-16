@props(['name', 'title', 'maxWidth' => 'lg'])

@php
$widths = ['sm' => '440px', 'md' => '580px', 'lg' => '700px', 'xl' => '860px', '2xl' => '980px'];
$mw = $widths[$maxWidth] ?? $widths['lg'];
@endphp

<div x-data="{ open: false }"
     x-on:open-modal.window="$event.detail === '{{ $name }}' && (open = true)"
     x-on:close-modal.window="$event.detail === '{{ $name }}' && (open = false)"
     x-show="open"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-[200] flex items-center justify-center p-4 overflow-y-auto"
     @keydown.escape.window="open = false"
     x-cloak>

    {{-- Backdrop --}}
    <div @click="open = false"
         class="fixed inset-0 bg-ink/40 backdrop-blur-sm"></div>

    {{-- Panel --}}
    <div class="relative z-10 w-full bg-white rounded-xl shadow-[0_20px_60px_rgba(28,24,48,0.2)] overflow-hidden my-auto"
         style="max-width:{{ $mw }}"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         @click.stop>

        {{-- Top accent line --}}
        <div class="h-0.5 bg-gradient-to-r from-purple-3 via-purple to-purple-2"></div>

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-cream3">
            <h2 class="font-display text-[13px] tracking-wide text-ink uppercase">{{ $title }}</h2>
            <button @click="open = false"
                    class="text-ink4 hover:text-ink transition-colors w-7 h-7 flex items-center justify-center rounded-md hover:bg-cream">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="px-6 py-5">{{ $slot }}</div>

        {{-- Footer --}}
        @if(isset($footer))
        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-cream3 bg-cream/50">
            {{ $footer }}
        </div>
        @endif
    </div>
</div>
