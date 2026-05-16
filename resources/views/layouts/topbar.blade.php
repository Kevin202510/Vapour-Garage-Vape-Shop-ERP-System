<header class="vg-topbar">

    {{-- Hamburger (mobile only) --}}
    <button @click="sidebarOpen = !sidebarOpen" class="vg-hamburger" aria-label="Toggle menu">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="3" y1="6"  x2="21" y2="6"/>
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
    </button>

    {{-- Breadcrumb --}}
    <div class="vg-topbar-breadcrumb">
        <span class="vg-topbar-brand">VGM</span>
        <span class="vg-topbar-sep">//</span>
        <span class="vg-topbar-page">{{ $title ?? 'Dashboard' }}</span>
        @if(auth()->user()->branch && !auth()->user()->isSuperAdmin())
            <span class="vg-topbar-sep">·</span>
            <span class="vg-topbar-branch">{{ auth()->user()->branch->name }}</span>
        @endif
    </div>

    {{-- Branch switcher (admin / super_admin only) --}}
    @if(auth()->user()->isSuperAdmin())
    @php
        $topbarBranches   = \App\Models\Branch::where('is_active', true)->orderBy('name')->get();
        $topbarActiveBranchId = auth()->user()->activeBranchId();
        $topbarActiveName = $topbarBranches->firstWhere('id', $topbarActiveBranchId)?->name ?? 'All Branches';
    @endphp
    <div class="relative" x-data="{ open: false }">
        <button type="button" @click="open = !open"
                :class="open ? 'vg-branch-btn vg-branch-btn--open' : 'vg-branch-btn'">
            {{-- Building icon --}}
            <svg class="vg-branch-btn-icon" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M3 21h18M9 21V7l7-4v18M9 11H5a2 2 0 00-2 2v8"/>
                <path d="M13 21V11"/>
            </svg>
            <span class="vg-branch-btn-name">{{ $topbarActiveName }}</span>
            <svg class="vg-branch-btn-chevron" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="6 9 12 15 18 9"/>
            </svg>
        </button>

        <div x-show="open" @click.outside="open = false"
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             class="vg-branch-dropdown" style="display:none">

            <div class="vg-topbar-dropdown-header">
                <p class="vg-topbar-dropdown-title">Active Branch</p>
            </div>

            {{-- All Branches option --}}
            <form method="POST" action="{{ route('branch.switch') }}">
                @csrf
                <input type="hidden" name="branch_id" value="all">
                <button type="submit" class="vg-branch-option {{ !$topbarActiveBranchId ? 'vg-branch-option--active' : '' }}">
                    <span class="vg-branch-option-dot"></span>
                    <div class="vg-branch-option-info">
                        <div class="vg-branch-option-name">All Branches</div>
                        <div class="vg-branch-option-city">View all data across branches</div>
                    </div>
                    @if(!$topbarActiveBranchId)
                    <svg class="vg-branch-option-check" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    @endif
                </button>
            </form>

            {{-- Individual branches --}}
            @foreach($topbarBranches as $branch)
            <form method="POST" action="{{ route('branch.switch') }}">
                @csrf
                <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                <button type="submit" class="vg-branch-option {{ $topbarActiveBranchId == $branch->id ? 'vg-branch-option--active' : '' }}">
                    <span class="vg-branch-option-dot"></span>
                    <div class="vg-branch-option-info">
                        <div class="vg-branch-option-name">{{ $branch->name }}</div>
                        <div class="vg-branch-option-city">{{ $branch->city }}</div>
                    </div>
                    @if($topbarActiveBranchId == $branch->id)
                    <svg class="vg-branch-option-check" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    @endif
                </button>
            </form>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Search (hidden on mobile) --}}
    <div class="vg-topbar-search hidden md:flex">
        <svg class="vg-topbar-search-icon" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input type="text" placeholder="Search…">
    </div>

    {{-- Dark / Light mode toggle --}}
    <button onclick="toggleTheme()" class="vg-theme-toggle" title="Toggle dark/light mode" id="theme-toggle-btn">
        {{-- Sun icon (shown in dark mode) --}}
        <svg id="icon-sun" class="w-[15px] h-[15px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
             style="display:none">
            <circle cx="12" cy="12" r="5"/>
            <line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
            <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
        </svg>
        {{-- Moon icon (shown in light mode) --}}
        <svg id="icon-moon" class="w-[15px] h-[15px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
        </svg>
    </button>

    <script>
    function toggleTheme() {
        const isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('vg_theme', isDark ? 'dark' : 'light');
        document.getElementById('icon-sun').style.display  = isDark ? 'block' : 'none';
        document.getElementById('icon-moon').style.display = isDark ? 'none'  : 'block';
    }
    // Set correct icon on load
    (function() {
        const isDark = document.documentElement.classList.contains('dark');
        const sun  = document.getElementById('icon-sun');
        const moon = document.getElementById('icon-moon');
        if (sun && moon) {
            sun.style.display  = isDark ? 'block' : 'none';
            moon.style.display = isDark ? 'none'  : 'block';
        }
    })();
    </script>

    {{-- Notifications --}}
    @canany(['view inventory','view stock transfers','approve stock transfers'])
    <div class="relative" x-data="{ open: false }">
        <button @click="open = !open" :class="open ? 'vg-topbar-icon-btn vg-topbar-icon-btn--open' : 'vg-topbar-icon-btn'">
            <svg class="w-[14px] h-[14px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
            @if(($pendingTransfers ?? 0) + ($lowStockCount ?? 0) > 0)
                <span class="vg-topbar-dot"></span>
            @endif
        </button>

        <div x-show="open" @click.outside="open = false"
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="vg-topbar-dropdown" style="display:none">
            <div class="vg-topbar-dropdown-header">
                <p class="vg-topbar-dropdown-title">Alerts</p>
            </div>
            <div style="max-height:240px;overflow-y:auto">
                @can('view inventory')
                    @if(($lowStockCount ?? 0) > 0)
                    <a href="{{ route('inventory.index') }}" class="vg-topbar-alert-row">
                        <div class="vg-topbar-alert-icon vg-topbar-alert-icon--warn">
                            <svg class="w-[13px] h-[13px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="vg-topbar-alert-title">{{ $lowStockCount }} low stock alerts</p>
                            <p class="vg-topbar-alert-sub">View inventory →</p>
                        </div>
                    </a>
                    @endif
                @endcan

                @can('approve stock transfers')
                    @if(($pendingTransfers ?? 0) > 0)
                    <a href="{{ route('transfers.index') }}" class="vg-topbar-alert-row">
                        <div class="vg-topbar-alert-icon vg-topbar-alert-icon--purple">
                            <svg class="w-[13px] h-[13px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/>
                            </svg>
                        </div>
                        <div>
                            <p class="vg-topbar-alert-title">{{ $pendingTransfers }} transfers pending</p>
                            <p class="vg-topbar-alert-sub">Review transfers →</p>
                        </div>
                    </a>
                    @endif
                @endcan

                @if(($lowStockCount ?? 0) + ($pendingTransfers ?? 0) === 0)
                    <div class="vg-topbar-empty">All systems nominal</div>
                @endif
            </div>
        </div>
    </div>
    @endcanany

    {{-- New Transaction --}}
    @can('create sales')
    <a href="{{ route('pos.index') }}" class="vg-new-txn-btn">
        <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        <span class="hidden sm:inline">New Transaction</span>
    </a>
    @endcan

</header>
