@if ($paginator->hasPages())
<nav role="navigation" class="flex items-center justify-between">
    <div class="font-mono text-[10px] text-ink4">
        Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}
    </div>
    <div class="flex items-center gap-1">
        @if ($paginator->onFirstPage())
            <span class="px-3 py-1.5 text-[11px] font-mono border border-cream3 rounded text-ink4 opacity-40">← Prev</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}"
               class="px-3 py-1.5 text-[11px] font-mono border border-cream3 rounded text-ink3 hover:bg-cream hover:border-terra transition-colors">
                ← Prev
            </a>
        @endif

        <span class="px-3 py-1.5 font-mono text-[11px] text-ink4">Page {{ $paginator->currentPage() }}</span>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}"
               class="px-3 py-1.5 text-[11px] font-mono border border-cream3 rounded text-ink3 hover:bg-cream hover:border-terra transition-colors">
                Next →
            </a>
        @else
            <span class="px-3 py-1.5 text-[11px] font-mono border border-cream3 rounded text-ink4 opacity-40">Next →</span>
        @endif
    </div>
</nav>
@endif
