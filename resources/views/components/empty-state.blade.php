@props(['title', 'description' => null, 'icon' => 'box'])

<div class="flex flex-col items-center justify-center py-16 text-center">
    <div class="w-14 h-14 rounded-full bg-cream2 flex items-center justify-center mb-4">
        <x-icon :name="$icon" class="w-6 h-6 text-ink4" />
    </div>
    <p class="font-serif text-[15px] font-bold text-ink mb-1">{{ $title }}</p>
    @if($description)
        <p class="text-[12px] text-ink4 max-w-xs">{{ $description }}</p>
    @endif
    @if(isset($action))
        <div class="mt-4">{{ $action }}</div>
    @endif
</div>
