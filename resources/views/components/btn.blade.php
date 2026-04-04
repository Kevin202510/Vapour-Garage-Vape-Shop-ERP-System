@props(['variant' => 'ghost', 'size' => 'md', 'href' => null, 'type' => 'button'])

@php
    $variants = [
        'primary' => 'bg-terra text-white hover:bg-terra-dark hover:-translate-y-px shadow-sm',
        'navy'    => 'bg-navy text-white hover:bg-navy-dark hover:-translate-y-px shadow-sm',
        'ghost'   => 'bg-transparent text-ink3 border border-cream3 hover:bg-cream',
        'danger'  => 'bg-danger/10 text-danger border border-danger/30 hover:bg-danger/20',
    ];
    $sizes = [
        'sm' => 'px-3 py-[5px] text-[11px]',
        'md' => 'px-4 py-[7px] text-[12px]',
        'lg' => 'px-5 py-2.5 text-[13px]',
    ];
    $cls = ($variants[$variant] ?? $variants['ghost']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

@if($href)
    <a href="{{ $href }}"
       {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-md font-medium font-sans transition-all $cls"]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}"
            {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-md font-medium font-sans transition-all $cls"]) }}>
        {{ $slot }}
    </button>
@endif
