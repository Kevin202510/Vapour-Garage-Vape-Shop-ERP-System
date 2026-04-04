@props(['title' => null])

<div class="bg-white border border-cream3 rounded-lg overflow-hidden mb-5">
    @if($title || isset($tableHeader))
        <div class="flex items-center justify-between px-6 py-[18px] border-b border-cream2">
            @if($title)
                <h3 class="font-serif text-[15px] font-bold text-ink">{{ $title }}</h3>
            @endif
            @if(isset($tableHeader))
                {{ $tableHeader }}
            @endif
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead>
                <tr>
                    {{ $head }}
                </tr>
            </thead>
            <tbody class="divide-y divide-cream2">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    @if(isset($pagination))
        <div class="px-6 py-3 border-t border-cream2 bg-cream/50">
            {{ $pagination }}
        </div>
    @endif
</div>
