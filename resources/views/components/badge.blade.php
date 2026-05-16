@props(['variant' => 'muted'])
<span {{ $attributes->merge(['class' => "erp-badge erp-badge--{$variant}"]) }}>{{ $slot }}</span>
