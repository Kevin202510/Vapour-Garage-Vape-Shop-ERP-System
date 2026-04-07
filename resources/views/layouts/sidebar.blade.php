<aside style="width:256px;background:#060608;border-right:1px solid rgba(124,34,212,0.25);display:flex;flex-direction:column;flex-shrink:0;position:relative;overflow:hidden">

    {{-- Purple glow orb --}}
    <div style="position:absolute;bottom:-40px;right:-40px;width:200px;height:200px;border-radius:50%;background:radial-gradient(circle,rgba(124,34,212,0.18) 0%,transparent 70%);pointer-events:none"></div>
    <div style="position:absolute;top:-30px;left:-30px;width:150px;height:150px;border-radius:50%;background:radial-gradient(circle,rgba(57,255,20,0.06) 0%,transparent 70%);pointer-events:none"></div>

    {{-- Logo / Brand --}}
    <div style="padding:24px 20px 20px;border-bottom:1px solid rgba(124,34,212,0.2)">
        <div style="font-family:'Exo 2',sans-serif;font-size:9px;letter-spacing:0.22em;text-transform:uppercase;color:#7a7490;margin-bottom:10px">
            // OPERATIONS PLATFORM
        </div>
        <div style="font-family:'Russo One',sans-serif;font-size:20px;letter-spacing:0.06em;line-height:1;margin-bottom:2px;background:linear-gradient(90deg,#9b3df0,#39ff14);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">
            VAPOUR GARAGE
        </div>
        <div style="font-family:'Russo One',sans-serif;font-size:13px;letter-spacing:0.16em;color:rgba(57,255,20,0.6)">
            MAIN — ERP
        </div>
    </div>

    {{-- Nav --}}
    <nav style="flex:1;overflow-y:auto;padding:12px 0" class="scrollbar-none">

        <x-nav-section label="Overview" />
        <x-nav-item route="dashboard" icon="grid" label="Dashboard" />

        @can('create sales')
            <x-nav-item route="pos.index" icon="monitor" label="Point of Sale" />
        @endcan

        @canany(['view products','view inventory','view stock transfers'])
            <x-nav-section label="Inventory" />
        @endcanany

        @can('view products')
            <x-nav-item route="products.index" icon="box" label="Products" :badge="$lowStockCount ?? 0" />
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

        
        @can('view sales')
            <x-nav-item route="online-orders.index" icon="shopping-bag" label="Online Orders" :badge="\App\Models\OnlineOrder::where('status','pending')->count()" />
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
    <div style="padding:14px;border-top:1px solid rgba(124,34,212,0.2)">
        <div style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:4px;border:1px solid rgba(124,34,212,0.12);transition:background 0.2s"
             class="group hover:border-purple/30" style="cursor:pointer">
            {{-- Avatar --}}
            <div style="width:34px;height:34px;border-radius:4px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-family:'Russo One',sans-serif;font-size:13px;color:#fff;background:linear-gradient(135deg,#7c22d4,#39ff14);clip-path:polygon(0 0,calc(100% - 5px) 0,100% 5px,100% 100%,5px 100%,0 calc(100% - 5px))">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
            </div>
            <div style="flex:1;min-width:0">
                <div style="font-size:12px;font-weight:600;color:#e8e4f0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                    {{ auth()->user()->name ?? 'User' }}
                </div>
                @php
                    $rmap = ['super_admin'=>['SUPER ADMIN','#9b3df0'],'branch_manager'=>['MANAGER','#39ff14'],'supervisor'=>['SUPERVISOR','#c8c4d4'],'cashier'=>['CASHIER','#7a7490']];
                    $ur = auth()->user()->roles->first()?->name ?? 'cashier';
                    [$rlabel,$rcolor] = $rmap[$ur] ?? ['STAFF','#7a7490'];
                    $bn = auth()->user()->branch?->name;
                @endphp
                <div style="font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.12em;color:{{ $rcolor }};margin-top:2px">
                    {{ $rlabel }}@if($bn && $ur !== 'super_admin') · {{ Str::limit($bn, 11) }}@endif
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Sign out"
                        style="opacity:0;color:#7a7490;transition:opacity 0.2s"
                        class="group-hover:opacity-100 hover:!text-[#39ff14]">
                    <svg style="width:13px;height:13px" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>
