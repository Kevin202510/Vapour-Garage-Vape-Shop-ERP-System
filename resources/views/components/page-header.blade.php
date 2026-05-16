@props(['overline' => null, 'title', 'subtitle' => null])

<div class="erp-page-header">
    <div class="erp-page-header-left">
        @if($overline)
        <div class="erp-page-header-overline">
            <span class="erp-page-header-slash">//</span>{{ $overline }}
        </div>
        @endif
        <h1 class="erp-page-header-title">{!! $title !!}</h1>
        @if($subtitle)
        <p class="erp-page-header-subtitle">{{ $subtitle }}</p>
        @endif
    </div>
    @if(isset($actions))
    <div class="erp-page-header-actions">{{ $actions }}</div>
    @endif
</div>
