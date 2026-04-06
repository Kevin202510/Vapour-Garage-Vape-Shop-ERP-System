@props(['name', 'title', 'maxWidth' => 'lg'])

@php
$widths = ['sm' => '420px', 'md' => '560px', 'lg' => '680px', 'xl' => '800px', '2xl' => '960px'];
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
     style="position:fixed;inset:0;z-index:50;display:flex;align-items:center;justify-content:center;padding:16px"
     x-cloak>

    {{-- Backdrop --}}
    <div @click="open = false" style="position:absolute;inset:0;background:rgba(6,6,8,0.85);backdrop-filter:blur(8px)"></div>

    {{-- Panel --}}
    <div style="position:relative;z-index:10;width:100%;max-width:{{ $mw }};background:#130f1e;border:1px solid rgba(124,34,212,0.4);border-radius:6px;box-shadow:0 8px 60px rgba(0,0,0,0.8),0 0 40px rgba(124,34,212,0.2);overflow:hidden"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         @click.stop>

        {{-- Top accent --}}
        <div style="height:2px;background:linear-gradient(90deg,#5b16a0,#39ff14,#5b16a0)"></div>

        {{-- Header --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid rgba(124,34,212,0.2)">
            <h2 style="font-family:'Russo One',sans-serif;font-size:14px;letter-spacing:0.08em;color:#e8e4f0;text-transform:uppercase">{{ $title }}</h2>
            <button @click="open = false" style="color:#7a7490;background:transparent;border:none;cursor:pointer;transition:color 0.2s"
                    onmouseover="this.style.color='#39ff14'" onmouseout="this.style.color='#7a7490'">
                <svg style="width:14px;height:14px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        {{-- Body --}}
        <div style="padding:20px">{{ $slot }}</div>

        {{-- Footer --}}
        @if(isset($footer))
        <div style="display:flex;align-items:center;justify-content:flex-end;gap:10px;padding:14px 20px;border-top:1px solid rgba(124,34,212,0.2);background:rgba(0,0,0,0.2)">
            {{ $footer }}
        </div>
        @endif
    </div>
</div>
