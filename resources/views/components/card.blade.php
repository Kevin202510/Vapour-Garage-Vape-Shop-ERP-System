@props(['title' => null, 'subtitle' => null, 'noPadding' => false])

<div class="bg-white border border-cream3 rounded-lg overflow-hidden">
    @if($title)
        <div class="px-6 py-4 border-b border-cream2 flex items-center justify-between">
            <div>
                <h3 class="font-serif text-[15px] font-bold text-ink">{{ $title }}</h3>
                @if($subtitle)
                    <p class="font-mono text-[10px] text-ink4 mt-0.5 tracking-[0.04em]">{{ $subtitle }}</p>
                @endif
            </div>
            @if(isset($headerActions))
                <div class="flex items-center gap-2">{{ $headerActions }}</div>
            @endif
        </div>
    @endif
    <div class="{{ $noPadding ? '' : 'p-6' }}">
        {{ $slot }}
    </div>
</div>
