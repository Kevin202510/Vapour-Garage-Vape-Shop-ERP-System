<aside class="vg-sidebar" :class="{ 'mobile-open': sidebarOpen }"
      @keydown.escape.window="sidebarOpen = false">

    <div class="vg-sidebar-orb-bottom"></div>
    <div class="vg-sidebar-orb-top"></div>

    {{-- Logo --}}
    <div class="vg-sidebar-logo">
        <div class="vg-sidebar-logo-tag">// Operations Platform</div>
        <div class="vg-sidebar-logo-title">VAPOUR GARAGE</div>
        <div class="vg-sidebar-logo-sub">MAIN — ERP</div>
    </div>

    {{-- Navigation --}}
    <nav class="vg-sidebar-nav scrollbar-none">

        <x-nav-section label="Overview" />
        <x-nav-item route="dashboard"    icon="grid"     label="Dashboard" />

        @can('create sales')
            <x-nav-item route="pos.index" icon="monitor" label="Point of Sale" />
        @endcan

        @canany(['view products','view inventory','view stock transfers'])
            <x-nav-section label="Inventory" />
        @endcanany

        @can('view products')
            <x-nav-item route="products.index"   icon="box"    label="Products"   :badge="$lowStockCount ?? 0" />
        @endcan
        @can('view categories')
            <x-nav-item route="categories.index" icon="list"   label="Categories" />
        @endcan
        @can('view inventory')
            <x-nav-item route="inventory.index"  icon="list"   label="Inventory" />
        @endcan
        @can('view stock transfers')
            <x-nav-item route="transfers.index"  icon="repeat" label="Transfers"
                :badge="auth()->user()->can('approve stock transfers') ? $pendingTransfers ?? 0 : 0" />
        @endcan

        @canany(['view sales','view purchase orders','view customers'])
            <x-nav-section label="Commerce" />
        @endcanany

        @can('view sales')
            <x-nav-item route="sales.index"          icon="activity"      label="Sales" />
        @endcan
        @can('view sales reports')
            <x-nav-item route="sales-periods.index"  icon="activity"      label="Period History" />
        @endcan
        @can('view purchase orders')
            <x-nav-item route="purchasing.index"     icon="shopping-bag"  label="Purchasing" />
        @endcan
        @can('view suppliers')
            <x-nav-item route="suppliers.index"      icon="users"         label="Suppliers" />
        @endcan
        @can('view customers')
            <x-nav-item route="customers.index"      icon="users"         label="Customers" />
        @endcan
        @can('view sales')
            <x-nav-item route="online-orders.index"  icon="shopping-bag"  label="Online Orders"
                :badge="$pendingOnlineOrders ?? 0" />
        @endcan

        @canany(['view employees','view payroll','view branches'])
            <x-nav-section label="HR & Admin" />
        @endcanany

        @can('view employees')
            <x-nav-item route="employees.index" icon="user"        label="Employees" />
        @endcan
        @can('view payroll')
            <x-nav-item route="payroll.index"   icon="credit-card" label="Payroll" />
        @endcan
        @can('view branches')
            <x-nav-item route="branches.index"  icon="home"        label="Branches" />
        @endcan

    </nav>

    {{-- User card --}}
    <div class="vg-sidebar-user">
        <div class="vg-sidebar-user-card">
            <div class="vg-sidebar-avatar">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="vg-sidebar-username">{{ auth()->user()->name ?? 'User' }}</div>
                @php
                    $rmap = [
                        'super_admin'    => ['SUPER ADMIN', '#9b3df0'],
                        'admin'          => ['ADMIN',       '#7c22d4'],
                        'branch_manager' => ['MANAGER',     '#16a34a'],
                        'supervisor'     => ['SUPERVISOR',  '#4a4465'],
                        'cashier'        => ['CASHIER',     '#8b87a3'],
                        'inventory_clerk'=> ['INVENTORY',   '#8b87a3'],
                    ];
                    $ur = auth()->user()->roles->first()?->name ?? 'cashier';
                    [$rlabel, $rcolor] = $rmap[$ur] ?? ['STAFF', '#8b87a3'];
                    $bn = auth()->user()->isSuperAdmin()
                        ? auth()->user()->activeBranch()?->name
                        : auth()->user()->branch?->name;
                @endphp
                <div class="vg-sidebar-role" style="color:{{ $rcolor }}">
                    {{ $rlabel }}@if($bn)
                        · {{ Str::limit($bn, 12) }}
                    @endif
                </div>
            </div>
            <a href="{{ route('2fa.security') }}" title="Security & 2FA" class="vg-sidebar-logout" style="text-decoration:none">
                <svg class="w-[13px] h-[13px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Sign out" class="vg-sidebar-logout">
                    <svg class="w-[13px] h-[13px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>

</aside>
