@props(['label', 'value', 'valueClass' => ''])

<div class="erp-stat-box">
    <div class="erp-stat-box-accent"></div>
    <p class="erp-stat-box-label">{{ $label }}</p>
    <p class="erp-stat-box-value
        {{ str_contains($valueClass,'ok') ? 'erp-stat-box-value--ok' : '' }}
        {{ str_contains($valueClass,'warn') ? 'erp-stat-box-value--warn' : '' }}
        {{ str_contains($valueClass,'danger') ? 'erp-stat-box-value--danger' : '' }}">
        {{ $value }}
    </p>
</div>
