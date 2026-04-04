<header class="h-14 bg-paper border-b border-cream3 flex items-center px-8 gap-4 flex-shrink-0">

    {{-- Breadcrumb --}}
    <div class="flex-1 font-mono text-[11px] text-ink4">
        VapeHub ERP &nbsp;/&nbsp;
        <span class="text-terra">{{ $title ?? 'Dashboard' }}</span>
    </div>

    {{-- Search --}}
    <div class="flex items-center gap-2 bg-cream border border-cream3 rounded-md px-3.5 py-[7px] w-60 focus-within:border-terra transition-colors"
         x-data="{ open: false }" @keydown.window.prevent.slash="$refs.search.focus()">
        <svg class="w-3.5 h-3.5 text-ink4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input x-ref="search" type="text" placeholder="Search…  /" class="bg-transparent border-none outline-none text-[12px] font-sans text-ink placeholder-ink4 w-full">
    </div>

    {{-- Notifications --}}
    <div class="relative" x-data="{ open: false }">
        <button @click="open = !open" class="relative w-[34px] h-[34px] rounded-md bg-cream border border-cream3 flex items-center justify-center hover:bg-cream2 transition-colors">
            <svg class="w-[14px] h-[14px] text-ink3" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            @if(($pendingTransfers ?? 0) + ($lowStockCount ?? 0) > 0)
                <span class="absolute top-[7px] right-[7px] w-1.5 h-1.5 bg-terra rounded-full border-[1.5px] border-paper"></span>
            @endif
        </button>

        {{-- Notification dropdown --}}
        <div x-show="open" @click.outside="open = false" x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             class="absolute right-0 top-10 w-72 bg-white border border-cream3 rounded-lg shadow-lg z-50 overflow-hidden">
            <div class="px-4 py-3 border-b border-cream2">
                <p class="font-serif text-[13px] font-bold text-ink">Notifications</p>
            </div>
            <div class="divide-y divide-cream2 max-h-64 overflow-y-auto">
                @if(($lowStockCount ?? 0) > 0)
                <a href="{{ route('inventory.index') }}" class="flex items-start gap-3 px-4 py-3 hover:bg-cream transition-colors">
                    <div class="w-7 h-7 rounded-full bg-warn/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-3.5 h-3.5 text-warn" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-[12px] font-medium text-ink">{{ $lowStockCount }} low stock alerts</p>
                        <p class="font-mono text-[10px] text-ink4 mt-0.5">View inventory →</p>
                    </div>
                </a>
                @endif
                @if(($pendingTransfers ?? 0) > 0)
                <a href="{{ route('transfers.index') }}" class="flex items-start gap-3 px-4 py-3 hover:bg-cream transition-colors">
                    <div class="w-7 h-7 rounded-full bg-navy/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-3.5 h-3.5 text-navy" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/></svg>
                    </div>
                    <div>
                        <p class="text-[12px] font-medium text-ink">{{ $pendingTransfers }} transfers pending</p>
                        <p class="font-mono text-[10px] text-ink4 mt-0.5">Review transfers →</p>
                    </div>
                </a>
                @endif
                @if(($lowStockCount ?? 0) + ($pendingTransfers ?? 0) === 0)
                <div class="px-4 py-6 text-center font-mono text-[11px] text-ink4">All clear — no alerts</div>
                @endif
            </div>
        </div>
    </div>

    {{-- New Transaction --}}
    <a href="{{ route('pos.index') }}"
       class="inline-flex items-center gap-1.5 px-4 py-[7px] bg-terra text-white text-[12px] font-medium rounded-md hover:bg-terra-dark transition-all hover:-translate-y-px">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New Transaction
    </a>

</header>
