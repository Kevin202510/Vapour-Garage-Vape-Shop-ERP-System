@props(['title' => null])

<div class="erp-table-wrap">
    <div class="erp-table-accent"></div>

    @if($title || isset($tableHeader))
    <div class="erp-table-head">
        @if($title)
        <h3 class="erp-table-head-title">{{ $title }}</h3>
        @endif
        @if(isset($tableHeader))
        <div class="flex items-center gap-3">{{ $tableHeader }}</div>
        @endif
    </div>
    @endif

    <div class="erp-table-scroll">
        <table class="erp-table">
            @if(isset($head))
            <thead><tr>{{ $head }}</tr></thead>
            @endif
            <tbody>{{ $slot }}</tbody>
        </table>
    </div>

    @if(isset($pagination))
    <div class="erp-table-pagination">{{ $pagination }}</div>
    @endif
</div>
