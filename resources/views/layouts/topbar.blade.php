<header style="height:56px;background:#060608;border-bottom:1px solid rgba(124,34,212,0.25);display:flex;align-items:center;padding:0 32px;gap:16px;flex-shrink:0">

    {{-- Breadcrumb --}}
    <div style="flex:1;font-family:'Share Tech Mono',monospace;font-size:11px;letter-spacing:0.08em;color:#7a7490">
        <span style="color:rgba(124,34,212,0.6)">VGM</span>
        <span style="color:rgba(124,34,212,0.3);margin:0 6px">//</span>
        <span style="color:#9b3df0">{{ $title ?? 'Dashboard' }}</span>
        @if(auth()->user()->branch && !auth()->user()->isSuperAdmin())
            <span style="color:rgba(124,34,212,0.3);margin:0 6px">·</span>
            <span style="color:#7a7490">{{ auth()->user()->branch->name }}</span>
        @endif
    </div>

    {{-- Search --}}
    <div style="display:flex;align-items:center;gap:8px;background:#130f1e;border:1px solid rgba(124,34,212,0.25);border-radius:4px;padding:7px 14px;width:220px;transition:border-color 0.2s"
         onfocus-within="this.style.borderColor='rgba(124,34,212,0.6)'">
        <svg style="width:13px;height:13px;flex-shrink:0;color:#7a7490" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" placeholder="Search…"
               style="background:transparent;border:none;outline:none;font-family:'Exo 2',sans-serif;font-size:12px;color:#e8e4f0;width:100%;caret-color:#39ff14"
               ::placeholder="color:#7a7490">
    </div>

    {{-- Notifications --}}
    @canany(['view inventory','view stock transfers','approve stock transfers'])
    <div style="position:relative" x-data="{ open: false }">
        <button @click="open = !open"
                style="position:relative;width:34px;height:34px;border-radius:4px;background:#130f1e;border:1px solid rgba(124,34,212,0.25);display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all 0.2s"
                :style="open ? 'border-color:rgba(124,34,212,0.6);background:#1a1428' : ''">
            <svg style="width:14px;height:14px;color:#c8c4d4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
            @if(($pendingTransfers ?? 0) + ($lowStockCount ?? 0) > 0)
                <span style="position:absolute;top:7px;right:7px;width:6px;height:6px;border-radius:50%;background:#39ff14;box-shadow:0 0 6px #39ff14"></span>
            @endif
        </button>

        <div x-show="open" @click.outside="open = false"
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             style="position:absolute;right:0;top:42px;width:280px;background:#130f1e;border:1px solid rgba(124,34,212,0.3);border-radius:6px;box-shadow:0 8px 40px rgba(0,0,0,0.6);z-index:50;overflow:hidden"
             style="display:none">
            <div style="padding:12px 16px;border-bottom:1px solid rgba(124,34,212,0.2)">
                <p style="font-family:'Russo One',sans-serif;font-size:12px;letter-spacing:0.08em;color:#e8e4f0">ALERTS</p>
            </div>
            <div style="max-height:240px;overflow-y:auto">
                @can('view inventory')
                    @if(($lowStockCount ?? 0) > 0)
                    <a href="{{ route('inventory.index') }}" style="display:flex;align-items:flex-start;gap:12px;padding:12px 16px;text-decoration:none;border-bottom:1px solid rgba(124,34,212,0.1);transition:background 0.2s"
                       onmouseover="this.style.background='rgba(124,34,212,0.08)'" onmouseout="this.style.background='transparent'">
                        <div style="width:28px;height:28px;border-radius:4px;background:rgba(184,122,30,0.15);border:1px solid rgba(184,122,30,0.3);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                            <svg style="width:13px;height:13px;color:#b87a1e" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
                        </div>
                        <div>
                            <p style="font-size:12px;font-weight:600;color:#e8e4f0">{{ $lowStockCount }} low stock alerts</p>
                            <p style="font-family:'Share Tech Mono',monospace;font-size:10px;color:#7a7490;margin-top:2px">View inventory →</p>
                        </div>
                    </a>
                    @endif
                @endcan

                @can('approve stock transfers')
                    @if(($pendingTransfers ?? 0) > 0)
                    <a href="{{ route('transfers.index') }}" style="display:flex;align-items:flex-start;gap:12px;padding:12px 16px;text-decoration:none;transition:background 0.2s"
                       onmouseover="this.style.background='rgba(124,34,212,0.08)'" onmouseout="this.style.background='transparent'">
                        <div style="width:28px;height:28px;border-radius:4px;background:rgba(124,34,212,0.15);border:1px solid rgba(124,34,212,0.3);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                            <svg style="width:13px;height:13px;color:#9b3df0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/></svg>
                        </div>
                        <div>
                            <p style="font-size:12px;font-weight:600;color:#e8e4f0">{{ $pendingTransfers }} transfers pending</p>
                            <p style="font-family:'Share Tech Mono',monospace;font-size:10px;color:#7a7490;margin-top:2px">Review transfers →</p>
                        </div>
                    </a>
                    @endif
                @endcan

                @if(($lowStockCount ?? 0) + ($pendingTransfers ?? 0) === 0)
                    <div style="padding:24px 16px;text-align:center;font-family:'Share Tech Mono',monospace;font-size:11px;color:#7a7490">// ALL SYSTEMS NOMINAL</div>
                @endif
            </div>
        </div>
    </div>
    @endcanany

    {{-- New Transaction CTA --}}
    @can('create sales')
    <a href="{{ route('pos.index') }}"
       style="display:inline-flex;align-items:center;gap:6px;padding:8px 18px;background:#7c22d4;color:#fff;font-family:'Russo One',sans-serif;font-size:12px;letter-spacing:0.06em;border-radius:4px;text-decoration:none;box-shadow:0 0 20px rgba(124,34,212,0.4);transition:all 0.2s;clip-path:polygon(0 0,calc(100% - 6px) 0,100% 6px,100% 100%,6px 100%,0 calc(100% - 6px))"
       onmouseover="this.style.background='#9b3df0';this.style.boxShadow='0 0 36px rgba(124,34,212,0.7)';this.style.transform='translateY(-1px)'"
       onmouseout="this.style.background='#7c22d4';this.style.boxShadow='0 0 20px rgba(124,34,212,0.4)';this.style.transform='translateY(0)'">
        <svg style="width:12px;height:12px" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        NEW TRANSACTION
    </a>
    @endcan

</header>
