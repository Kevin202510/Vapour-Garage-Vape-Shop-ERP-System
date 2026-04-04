@props(['type' => 'success', 'message'])

@php
    $styles = [
        'success' => 'border-ok bg-ok/5 text-ok',
        'error'   => 'border-danger bg-danger/5 text-danger',
        'warning' => 'border-warn bg-warn/5 text-warn',
        'info'    => 'border-navy bg-navy/5 text-navy',
    ];
    $icons = [
        'success' => 'check-circle',
        'error'   => 'alert-triangle',
        'warning' => 'alert-triangle',
        'info'    => 'info',
    ];
    $cls  = $styles[$type] ?? $styles['info'];
    $icon = $icons[$type] ?? 'info';
@endphp

<div {{ $attributes->merge(['class' => "flex items-start gap-3 px-4 py-3 rounded-lg border $cls"]) }}
     x-data="{ show: true }" x-show="show" x-transition>
    <x-icon :name="$icon" class="w-4 h-4 flex-shrink-0 mt-0.5" />
    <p class="text-[12.5px] font-medium flex-1">{{ $message }}</p>
    <button @click="show = false" class="opacity-50 hover:opacity-80 transition-opacity ml-2">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
</div>
