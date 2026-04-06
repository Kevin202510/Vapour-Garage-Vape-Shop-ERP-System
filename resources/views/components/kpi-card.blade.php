@props(['label', 'value', 'delta' => null, 'deltaUp' => true, 'bgChar' => null, 'color' => 'purple'])

@php
$colors = [
    'purple' => ['bg' => 'rgba(124,34,212,0.15)', 'border' => 'rgba(124,34,212,0.3)', 'accent' => '#9b3df0', 'glow' => 'rgba(124,34,212,0.4)'],
    'green'  => ['bg' => 'rgba(57,255,20,0.08)',  'border' => 'rgba(57,255,20,0.25)',  'accent' => '#39ff14', 'glow' => 'rgba(57,255,20,0.3)'],
    'gold'   => ['bg' => 'rgba(201,148,58,0.12)', 'border' => 'rgba(201,148,58,0.3)', 'accent' => '#c9943a', 'glow' => 'rgba(201,148,58,0.3)'],
    'navy'   => ['bg' => 'rgba(124,34,212,0.08)', 'border' => 'rgba(124,34,212,0.2)', 'accent' => '#7c22d4', 'glow' => 'rgba(124,34,212,0.3)'],
    'terra'  => ['bg' => 'rgba(124,34,212,0.15)', 'border' => 'rgba(124,34,212,0.3)', 'accent' => '#9b3df0', 'glow' => 'rgba(124,34,212,0.4)'],
    'sage'   => ['bg' => 'rgba(57,255,20,0.08)',  'border' => 'rgba(57,255,20,0.25)',  'accent' => '#39ff14', 'glow' => 'rgba(57,255,20,0.3)'],
];
$c = $colors[$color] ?? $colors['purple'];
@endphp

<div style="background:#130f1e;border:1px solid {{ $c['border'] }};border-radius:6px;padding:20px;position:relative;overflow:hidden;box-shadow:0 0 20px {{ $c['glow'] }}">
    {{-- Glow top --}}
    <div style="position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,{{ $c['accent'] }},transparent)"></div>

    {{-- BG char --}}
    @if($bgChar)
    <div style="position:absolute;bottom:-8px;right:12px;font-family:'Russo One',sans-serif;font-size:56px;color:rgba(255,255,255,0.03);line-height:1;pointer-events:none">
        {{ $bgChar }}
    </div>
    @endif

    <p style="font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.2em;text-transform:uppercase;color:#7a7490;margin-bottom:10px">{{ $label }}</p>
    <p style="font-family:'Russo One',sans-serif;font-size:28px;line-height:1;color:#e8e4f0;margin-bottom:8px;letter-spacing:0.02em">{{ $value }}</p>

    @if($delta)
    <div style="display:flex;align-items:center;gap:4px;font-family:'Share Tech Mono',monospace;font-size:10px;letter-spacing:0.06em;color:{{ $deltaUp ? '#39ff14' : '#b83232' }}">
        <span>{{ $deltaUp ? '▲' : '▼' }}</span>
        <span>{{ $delta }}</span>
    </div>
    @endif
</div>
