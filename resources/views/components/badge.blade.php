@props(['variant' => 'muted'])

@php
$styles = [
    'ok'    => 'background:rgba(57,255,20,0.12);color:#39ff14;border:1px solid rgba(57,255,20,0.3)',
    'warn'  => 'background:rgba(184,122,30,0.12);color:#c9943a;border:1px solid rgba(184,122,30,0.3)',
    'error' => 'background:rgba(184,50,50,0.12);color:#e05050;border:1px solid rgba(184,50,50,0.3)',
    'navy'  => 'background:rgba(124,34,212,0.15);color:#9b3df0;border:1px solid rgba(124,34,212,0.35)',
    'terra' => 'background:rgba(124,34,212,0.15);color:#9b3df0;border:1px solid rgba(124,34,212,0.35)',
    'gold'  => 'background:rgba(201,148,58,0.12);color:#c9943a;border:1px solid rgba(201,148,58,0.3)',
    'muted' => 'background:rgba(255,255,255,0.06);color:#7a7490;border:1px solid rgba(255,255,255,0.1)',
    'green' => 'background:rgba(57,255,20,0.12);color:#39ff14;border:1px solid rgba(57,255,20,0.3)',
    'purple'=> 'background:rgba(124,34,212,0.15);color:#9b3df0;border:1px solid rgba(124,34,212,0.35)',
];
$s = $styles[$variant] ?? $styles['muted'];
@endphp

<span {{ $attributes->merge(['style' => "display:inline-flex;align-items:center;padding:2px 8px;border-radius:3px;font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.12em;text-transform:uppercase;$s"]) }}>
    {{ $slot }}
</span>
