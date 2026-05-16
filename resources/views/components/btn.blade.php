@props(['variant' => 'ghost', 'size' => 'md', 'href' => null, 'type' => 'button'])

@php $cls = "erp-btn erp-btn--{$variant} erp-btn--{$size}"; @endphp

@if($href)
    <a href="{{ $href }}" class="{{ $cls }}" {{ $attributes->except(['variant','size','href']) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" class="{{ $cls }}" {{ $attributes->except(['variant','size','href','type']) }}>{{ $slot }}</button>
@endif
