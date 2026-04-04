@props([
    'label',
    'value',
    'delta'    => null,
    'deltaUp'  => true,
    'bgChar'   => null,
    'color'    => 'terra',   // terra | navy | gold | sage
])

@php
    $colors = [
        'terra' => 'bg-terra',
        'navy'  => 'bg-navy',
        'gold'  => 'bg-gold',
        'sage'  => 'bg-sage',
    ];
    $accent = $colors[$color] ?? 'bg-terra';
@endphp

<div class="relative bg-white border border-cream3 rounded-lg px-6 py-[22px] overflow-hidden
            hover:shadow-card hover:-translate-y-0.5 transition-all duration-200 cursor-default">

    {{-- Top accent bar --}}
    <div class="absolute top-0 left-0 right-0 h-[3px] {{ $accent }}"></div>

    {{-- Background number --}}
    @if($bgChar)
        <span class="absolute right-4 top-3 font-serif text-[64px] font-black text-ink opacity-[0.04] leading-none pointer-events-none select-none">
            {{ $bgChar }}
        </span>
    @endif

    <p class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 mb-3">{{ $label }}</p>
    <p class="font-serif text-[30px] font-black text-ink leading-none tracking-tight mb-2">{{ $value }}</p>

    @if($delta)
        <p class="font-mono text-[10px] flex items-center gap-1 {{ $deltaUp ? 'text-ok' : 'text-danger' }}">
            {{ $deltaUp ? '↑' : '↓' }} {{ $delta }}
        </p>
    @endif
</div>
