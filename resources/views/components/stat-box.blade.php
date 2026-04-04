@props(['label', 'value', 'valueClass' => 'text-ink'])

<div class="bg-white border border-cream3 rounded-lg px-5 py-[18px]">
    <p class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 mb-2">{{ $label }}</p>
    <p class="font-serif text-[26px] font-black leading-none tracking-tight {{ $valueClass }}">{{ $value }}</p>
</div>
