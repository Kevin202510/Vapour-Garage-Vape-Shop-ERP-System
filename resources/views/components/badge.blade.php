@props(['variant' => 'muted'])

@php
    $variants = [
        'ok'     => 'bg-ok/10 text-ok',
        'warn'   => 'bg-warn/10 text-warn',
        'error'  => 'bg-danger/10 text-danger',
        'navy'   => 'bg-navy/10 text-navy',
        'terra'  => 'bg-terra/10 text-terra',
        'gold'   => 'bg-gold/10 text-gold',
        'muted'  => 'bg-cream2 text-ink4',
        'sage'   => 'bg-sage/10 text-sage',
    ];
    $cls = $variants[$variant] ?? $variants['muted'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-[9px] py-[3px] rounded-full font-mono text-[9px] tracking-[0.06em] font-medium $cls"]) }}>
    {{ $slot }}
</span>
