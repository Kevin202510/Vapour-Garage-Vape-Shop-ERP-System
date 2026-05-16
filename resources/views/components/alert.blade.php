@props(['type' => 'success', 'message'])

@php
$icons = [
    'success' => 'check-circle',
    'error'   => 'alert-triangle',
    'warning' => 'alert-triangle',
    'info'    => 'info',
];
$icon = $icons[$type] ?? 'info';
@endphp

<div {{ $attributes->merge(['class' => "erp-alert erp-alert--{$type}"]) }}
     x-data="{ show: true }" x-show="show" data-flash>
    <x-icon :name="$icon" class="w-4 h-4 flex-shrink-0" />
    <p class="erp-alert-msg">{{ $message }}</p>
    <button @click="show = false" class="erp-alert-close" type="button">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
    </button>
</div>
