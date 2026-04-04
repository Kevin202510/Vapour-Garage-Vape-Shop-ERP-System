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

        @can('create sales')
            <x-nav-item route="pos.index" icon="monitor" label="Point of Sale" />
        @endcan

        @canany(['view products','view inventory','view stock transfers'])
            <x-nav-section label="Inventory" />
        @endcanany

        @can('view products')
            <x-nav-item route="products.index" icon="box" label="Products"
                        :badge="($lowStockCount ?? 0)" />
        @endcan

        @can('view inventory')
            <x-nav-item route="inventory.index" icon="list" label="Inventory" />
        @endcan

        @can('view stock transfers')
            <x-nav-item route="transfers.index" icon="repeat" label="Transfers"
                        :badge="auth()->user()->can('approve stock transfers') ? ($pendingTransfers ?? 0) : 0" />
        @endcan

        @canany(['view sales','view purchase orders','view customers'])
            <x-nav-section label="Commerce" />
        @endcanany

        @can('view sales')
            <x-nav-item route="sales.index" icon="activity" label="Sales" />
        @endcan

        @can('view purchase orders')
            <x-nav-item route="purchasing.index" icon="shopping-bag" label="Purchasing" />
        @endcan

        @can('view customers')
            <x-nav-item route="customers.index" icon="users" label="Customers" />
        @endcan

        @canany(['view employees','view payroll','view branches'])
            <x-nav-section label="HR & Admin" />
        @endcanany

        @can('view employees')
            <x-nav-item route="employees.index" icon="user" label="Employees" />
        @endcan

        @can('view payroll')
            <x-nav-item route="payroll.index" icon="credit-card" label="Payroll" />
        @endcan

        @can('view branches')
            <x-nav-item route="branches.index" icon="home" label="Branches" />
        @endcan

    </nav>

    {{-- User card --}}
    <div class="p-4 border-t border-white/[0.08]">
        <div class="flex items-center gap-2.5 p-2.5 rounded-md hover:bg-white/5 transition-colors group">
            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-terra to-gold flex items-center justify-center font-serif font-bold text-xs text-white flex-shrink-0">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[12px] font-medium text-white/85 truncate">{{ auth()->user()->name ?? 'User' }}</p>
                @php
                    $roleLabels = [
                        'super_admin'    => ['Super Admin',    'text-gold-2'],
                        'branch_manager' => ['Branch Manager', 'text-terra-light'],
                        'supervisor'     => ['Supervisor',     'text-white/50'],
                        'cashier'        => ['Cashier',        'text-white/40'],
                        'inventory_clerk'        => ['Inventory Clerk',        'text-white/40'],
                    ];
                    $userRole = auth()->user()->roles->first()?->name ?? 'cashier';
                    [$roleDisplay, $roleColor] = $roleLabels[$userRole] ?? ['Staff', 'text-white/40'];
                    $branchName = auth()->user()->branch?->name;
                @endphp
                <p class="font-mono text-[9px] uppercase tracking-wider mt-0.5 {{ $roleColor }}">
                    {{ $roleDisplay }}
                    @if($branchName && $userRole !== 'super_admin')· {{ Str::limit($branchName, 12) }}@endif
                </p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Sign out" class="opacity-0 group-hover:opacity-100 transition-opacity">
                    <svg class="w-3.5 h-3.5 text-white/40 hover:text-white/80 transition-colors" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>
