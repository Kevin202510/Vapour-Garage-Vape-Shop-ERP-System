{{-- th.blade.php --}}
<th {{ $attributes->merge(['class' => 'font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 px-6 py-3 text-left border-b border-cream2 font-normal whitespace-nowrap bg-cream/30']) }}>
    {{ $slot }}
</th>
