@props(['type' => 'success', 'message'])

@php
$styles = [
    'success' => ['bg' => 'rgba(57,255,20,0.08)', 'border' => 'rgba(57,255,20,0.3)', 'color' => '#39ff14', 'icon' => 'check-circle'],
    'error'   => ['bg' => 'rgba(184,50,50,0.08)',  'border' => 'rgba(184,50,50,0.3)',  'color' => '#e05050', 'icon' => 'alert-circle'],
    'warning' => ['bg' => 'rgba(184,122,30,0.08)', 'border' => 'rgba(184,122,30,0.3)', 'color' => '#c9943a', 'icon' => 'alert-triangle'],
    'info'    => ['bg' => 'rgba(124,34,212,0.08)', 'border' => 'rgba(124,34,212,0.3)', 'color' => '#9b3df0', 'icon' => 'info'],
];
$s = $styles[$type] ?? $styles['success'];
@endphp

<div {{ $attributes }}
     style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-radius:4px;border:1px solid {{ $s['border'] }};background:{{ $s['bg'] }}"
     x-data="{ show: true }" x-show="show" data-flash>
    <x-icon :name="$s['icon']" style="width:15px;height:15px;flex-shrink:0;color:{{ $s['color'] }}" />
    <p style="font-size:13px;color:#e8e4f0;flex:1">{{ $message }}</p>
    <button @click="show = false" style="background:transparent;border:none;cursor:pointer;color:#7a7490"
            onmouseover="this.style.color='{{ $s['color'] }}'" onmouseout="this.style.color='#7a7490'">
        <svg style="width:12px;height:12px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
</div>
