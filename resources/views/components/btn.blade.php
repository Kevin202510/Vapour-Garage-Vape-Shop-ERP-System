@props(['variant' => 'ghost', 'size' => 'md', 'href' => null, 'type' => 'button'])

@php
$variants = [
    'primary' => "background:#7c22d4;color:#fff;border:1px solid #7c22d4;box-shadow:0 0 20px rgba(124,34,212,0.35)",
    'ghost'   => "background:transparent;color:#c8c4d4;border:1px solid rgba(124,34,212,0.25)",
    'danger'  => "background:rgba(184,50,50,0.15);color:#e05050;border:1px solid rgba(184,50,50,0.3)",
    'outline' => "background:transparent;color:#39ff14;border:1px solid rgba(57,255,20,0.35)",
];
$sizes = [
    'sm' => "padding:5px 12px;font-size:11px",
    'md' => "padding:8px 18px;font-size:12px",
    'lg' => "padding:11px 24px;font-size:14px",
];
$vs = $variants[$variant] ?? $variants['ghost'];
$ss = $sizes[$size] ?? $sizes['md'];
$hover = $variant === 'primary'
    ? "onmouseover=\"this.style.background='#9b3df0';this.style.boxShadow='0 0 32px rgba(124,34,212,0.6)';this.style.transform='translateY(-1px)'\" onmouseout=\"this.style.background='#7c22d4';this.style.boxShadow='0 0 20px rgba(124,34,212,0.35)';this.style.transform=''\""
    : "onmouseover=\"this.style.borderColor='rgba(124,34,212,0.5)';this.style.color='#e8e4f0'\" onmouseout=\"this.style.borderColor='rgba(124,34,212,0.25)';this.style.color=''\"";
$base = "display:inline-flex;align-items:center;gap:6px;font-family:'Share Tech Mono',monospace;letter-spacing:0.1em;text-transform:uppercase;border-radius:3px;cursor:pointer;text-decoration:none;transition:all 0.2s;white-space:nowrap;clip-path:polygon(0 0,calc(100% - 4px) 0,100% 4px,100% 100%,4px 100%,0 calc(100% - 4px));$vs;$ss";
@endphp

@if($href)
    <a href="{{ $href }}" style="{{ $base }}" {!! $hover !!} {{ $attributes->except(['variant','size','href']) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" style="{{ $base }}" {!! $hover !!} {{ $attributes->except(['variant','size','href','type']) }}>{{ $slot }}</button>
@endif
