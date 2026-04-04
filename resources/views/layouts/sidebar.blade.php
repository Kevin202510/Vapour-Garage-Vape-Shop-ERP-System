<aside class="w-64 bg-navy flex flex-col flex-shrink-0 relative overflow-hidden">

    {{-- Decorative blobs --}}
    <div class="absolute -bottom-16 -right-16 w-48 h-48 bg-terra opacity-[0.08] rounded-full pointer-events-none"></div>
    <div class="absolute -top-10 -left-10 w-40 h-40 bg-gold opacity-[0.06] rounded-full pointer-events-none"></div>

    {{-- Logo --}}
    <div class="px-6 py-7 border-b border-white/[0.08]">
        <p class="font-mono text-[9px] tracking-[0.2em] uppercase text-white/35 mb-1.5">Operations Platform</p>
        <h1 class="font-serif text-[22px] font-bold text-white leading-none tracking-tight">VapeHub</h1>
        <p class="font-mono text-[10px] text-terra-light tracking-[0.08em] mt-1">ERP · v2.1.0</p>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto py-4 scrollbar-none">

        <x-nav-section label="Overview" />
        <x-nav-item route="dashboard" icon="grid" label="Dashboard" />
        <x-nav-item route="pos.index" icon="monitor" label="Point of Sale" />

        <x-nav-section label="Inventory" />
        <x-nav-item route="products.index" icon="box" label="Products" :badge="$lowStockCount ?? 0" />
        <x-nav-item route="inventory.index" icon="list" label="Inventory" />
        <x-nav-item route="transfers.index" icon="repeat" label="Transfers" :badge="$pendingTransfers ?? 0" />

        <x-nav-section label="Commerce" />
        <x-nav-item route="sales.index" icon="activity" label="Sales" />
        <x-nav-item route="purchasing.index" icon="shopping-bag" label="Purchasing" />
        <x-nav-item route="customers.index" icon="users" label="Customers" />

        <x-nav-section label="HR & Admin" />
        <x-nav-item route="employees.index" icon="user" label="Employees" />
        <x-nav-item route="payroll.index" icon="credit-card" label="Payroll" />
        <x-nav-item route="branches.index" icon="home" label="Branches" />

    </nav>

    {{-- User --}}
    <div class="p-4 border-t border-white/[0.08]">
        <div class="flex items-center gap-2.5 p-2.5 rounded-md cursor-pointer hover:bg-white/5 transition-colors group">
            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-terra to-gold flex items-center justify-center font-serif font-bold text-xs text-white flex-shrink-0">
                {{ strtoupper(substr(auth()->user()->employee?->full_name ?? 'SA', 0, 2)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[12px] font-medium text-white/85 truncate">{{ auth()->user()->employee?->full_name ?? 'Super Admin' }}</p>
                <p class="font-mono text-[9px] text-white/30 uppercase tracking-wider mt-0.5">
                    {{ auth()->user()?->roles?->first()?->name ?? 'super_admin' }}
                </p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Logout" class="opacity-0 group-hover:opacity-100 transition-opacity">
                    <svg class="w-3.5 h-3.5 text-white/40 hover:text-white/80" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </button>
            </form>
        </div>
    </div>
</aside>
