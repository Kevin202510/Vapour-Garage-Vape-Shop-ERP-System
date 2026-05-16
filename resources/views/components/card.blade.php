@props(['title' => null, 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'erp-card']) }}>
    <div class="erp-card-accent"></div>
    <div class="erp-card-corner-tl"></div>
    <div class="erp-card-corner-br"></div>

    @if($title)
    <div class="erp-card-title-bar">
        <h3 class="erp-card-title">{{ $title }}</h3>
        @if($subtitle)
        <span class="erp-card-subtitle">// {{ $subtitle }}</span>
        @endif
    </div>
    @endif

    <div class="{{ $title ? 'erp-card-body' : 'erp-card-body--no-title' }}">
        {{ $slot }}
    </div>
</div>
