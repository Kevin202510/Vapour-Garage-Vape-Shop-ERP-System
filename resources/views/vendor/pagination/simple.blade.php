@if($paginator->hasPages())
<nav role="navigation" style="display:flex;align-items:center;justify-content:space-between">
    <div style="font-family:'Share Tech Mono',monospace;font-size:10px;color:#7a7490;letter-spacing:0.06em">
        // SHOWING {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}
    </div>
    <div style="display:flex;align-items:center;gap:6px">
        @if($paginator->onFirstPage())
            <span style="padding:5px 12px;font-family:'Share Tech Mono',monospace;font-size:11px;border:1px solid rgba(124,34,212,0.15);border-radius:3px;color:rgba(255,255,255,0.2);cursor:not-allowed">← PREV</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}"
               style="padding:5px 12px;font-family:'Share Tech Mono',monospace;font-size:11px;border:1px solid rgba(124,34,212,0.3);border-radius:3px;color:#7a7490;text-decoration:none;transition:all 0.2s"
               onmouseover="this.style.background='rgba(124,34,212,0.15)';this.style.color='#e8e4f0';this.style.borderColor='rgba(124,34,212,0.6)'"
               onmouseout="this.style.background='';this.style.color='#7a7490';this.style.borderColor='rgba(124,34,212,0.3)'">← PREV</a>
        @endif

        <span style="padding:5px 14px;font-family:'Share Tech Mono',monospace;font-size:11px;color:rgba(124,34,212,0.7);letter-spacing:0.06em">PAGE {{ $paginator->currentPage() }}</span>

        @if($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}"
               style="padding:5px 12px;font-family:'Share Tech Mono',monospace;font-size:11px;border:1px solid rgba(124,34,212,0.3);border-radius:3px;color:#7a7490;text-decoration:none;transition:all 0.2s"
               onmouseover="this.style.background='rgba(124,34,212,0.15)';this.style.color='#e8e4f0';this.style.borderColor='rgba(124,34,212,0.6)'"
               onmouseout="this.style.background='';this.style.color='#7a7490';this.style.borderColor='rgba(124,34,212,0.3)'">NEXT →</a>
        @else
            <span style="padding:5px 12px;font-family:'Share Tech Mono',monospace;font-size:11px;border:1px solid rgba(124,34,212,0.15);border-radius:3px;color:rgba(255,255,255,0.2);cursor:not-allowed">NEXT →</span>
        @endif
    </div>
</nav>
@endif
