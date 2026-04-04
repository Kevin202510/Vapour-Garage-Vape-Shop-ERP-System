@props(['overline', 'title', 'subtitle' => null])

<div class="flex items-end justify-between mb-8 pb-5 border-b border-cream3">
    <div>
        @if($overline)
            <p class="font-mono text-[9px] tracking-[0.22em] uppercase text-terra mb-1">{{ $overline }}</p>
        @endif
        <h1 class="font-serif text-[34px] font-bold text-ink leading-none tracking-tight">
            {!! $title !!}
        </h1>
        @if($subtitle)
            <p class="text-[12px] text-ink4 mt-1.5 font-light">{{ $subtitle }}</p>
        @endif
    </div>
    @if(isset($actions))
        <div class="flex items-center gap-2">
            {{ $actions }}
        </div>
    @endif
</div>
