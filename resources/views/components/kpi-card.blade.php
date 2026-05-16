@props(['label', 'value', 'delta' => null, 'deltaUp' => true, 'bgChar' => null, 'color' => 'purple'])

<div class="erp-kpi erp-kpi--{{ $color }}">
    <div class="erp-kpi-accent"></div>

    @if($bgChar)
    <div class="erp-kpi-bgchar">{{ $bgChar }}</div>
    @endif

    <p class="erp-kpi-label">{{ $label }}</p>
    <p class="erp-kpi-value">{{ $value }}</p>

    @if($delta)
    <div class="erp-kpi-delta {{ $deltaUp ? 'erp-kpi-delta--up' : 'erp-kpi-delta--down' }}">
        <span>{{ $deltaUp ? '▲' : '▼' }}</span>
        <span>{{ $delta }}</span>
    </div>
    @endif
</div>
