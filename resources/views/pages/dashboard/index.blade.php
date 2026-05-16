<x-app-layout title="Dashboard">

    <x-page-header
        overline="Overview · {{ now()->format('l, F d Y') }}"
        title="Good morning,<br><em>Operations.</em>"
        :subtitle="(auth()->user()->isSuperAdmin() ? 'All ' . $branches->count() . ' branches' : (auth()->user()->branch->name ?? 'Your branch')) . ' · ' . ucfirst($range) . ' view'"
    >
        <x-slot:actions>
            <div class="flex gap-1 bg-cream border border-cream3 rounded-md p-[3px]">
                @foreach(['today' => 'Today', 'week' => 'Week', 'month' => 'Month'] as $val => $lbl)
                    <a href="{{ route('dashboard', ['range' => $val]) }}"
                       class="px-3.5 py-[5px] rounded text-[11px] font-mono tracking-wide transition-all
                              {{ $range === $val ? 'bg-white text-ink shadow-sm' : 'text-ink4 hover:text-ink' }}">
                        {{ $lbl }}
                    </a>
                @endforeach
            </div>
            @can('export reports')
            <x-btn :href="route('dashboard.export', ['range' => $range])" variant="ghost" size="sm">
                <x-icon name="download" class="w-3 h-3" /> Export
            </x-btn>
            @endcan
        </x-slot:actions>
    </x-page-header>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-4 gap-4 mb-7">
        <x-kpi-card :label="$range === 'today' ? 'Revenue Today' : ($range === 'week' ? 'Revenue This Week' : 'Revenue This Month')"
                    :value="'₱' . number_format($stats['revenue_today'])"
                    delta="{{ number_format($stats['revenue_delta'], 1) }}% {{ $deltaLabel }}"
                    :deltaUp="$stats['revenue_delta'] >= 0" bgChar="₱" color="terra" />
        <x-kpi-card label="Transactions"
                    :value="number_format($stats['transactions_today'])"
                    delta="{{ number_format($stats['transactions_delta'], 1) }}% {{ $deltaLabel }}"
                    :deltaUp="$stats['transactions_delta'] >= 0" bgChar="—" color="navy" />
        <x-kpi-card label="Items Sold"
                    :value="number_format($stats['items_sold'])"
                    delta="{{ number_format($stats['items_delta'], 1) }}% {{ $deltaLabel }}"
                    :deltaUp="$stats['items_delta'] >= 0" bgChar="1k" color="gold" />
        <x-kpi-card label="New Customers"
                    :value="number_format($stats['new_customers'])"
                    delta="{{ number_format($stats['customers_delta'], 1) }}% {{ $deltaLabel }}"
                    :deltaUp="$stats['customers_delta'] >= 0" bgChar="—" color="sage" />
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-[1fr_320px] gap-4 mb-7">
        <x-card title="Revenue by Branch" :subtitle="strtoupper($dateFrom === $dateTo ? $dateFrom : $dateFrom . ' – ' . $dateTo) . ' · COMPLETED TRANSACTIONS'">
            <div class="grid grid-cols-2 gap-3 mb-5">
                <div class="bg-cream rounded-lg px-4 py-3">
                    <p class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 mb-1">Highest Branch</p>
                    <p class="font-serif text-[14px] font-bold text-ink">{{ $topBranch->name ?? '—' }}</p>
                </div>
                <div class="bg-cream rounded-lg px-4 py-3">
                    <p class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 mb-1">Avg. Basket Size</p>
                    <p class="font-serif text-[16px] font-bold text-ink">₱{{ number_format($stats['avg_basket'], 0) }}</p>
                </div>
            </div>
            <div class="flex items-end gap-3 h-32 border-b border-cream3 pb-6 relative">
                @php $colors = ['bg-terra','bg-navy','bg-gold','bg-sage']; $max = $branchRevenue->max('revenue') ?: 1; @endphp
                @foreach($branchRevenue as $i => $branch)
                    <div class="flex-1 flex flex-col items-center gap-1.5 h-full">
                        <span class="font-mono text-[9px] text-ink4">₱{{ number_format($branch->revenue / 1000, 0) }}k</span>
                        <div class="w-full flex items-end flex-1">
                            <div class="{{ $colors[$i % 4] }} w-full rounded-t-[3px]"
                                 style="height:{{ round(($branch->revenue / $max) * 100) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="flex flex-wrap gap-4 mt-3.5">
                @foreach($branchRevenue as $i => $branch)
                    <div class="flex items-center gap-1.5 font-mono text-[10px] text-ink3">
                        <span class="w-2 h-2 rounded-[2px] {{ $colors[$i % 4] }} inline-block"></span>
                        {{ $branch->name }} — ₱{{ number_format($branch->revenue) }}
                    </div>
                @endforeach
            </div>
        </x-card>

        <x-card title="Sales Mix" :subtitle="'BY CATEGORY · ' . strtoupper($range)">
            <div class="flex justify-center my-2">
                <svg width="110" height="110" viewBox="0 0 110 110">
                    <circle cx="55" cy="55" r="42" fill="none" stroke="#e3dccf" stroke-width="16"/>
                    @php
                        $catColors = ['#c45e3c','#1e2d4a','#b8882a','#5a7a5c'];
                        $offset    = 0;
                        $circumf   = 2 * M_PI * 42;
                        $totalSales = $categorySales->sum('total') ?: 1;
                    @endphp
                    @foreach($categorySales as $j => $cat)
                        @php $pct = $cat->total / $totalSales; $dash = $pct * $circumf; @endphp
                        <circle cx="55" cy="55" r="42" fill="none" stroke="{{ $catColors[$j % 4] }}"
                                stroke-width="16" stroke-dasharray="{{ $dash }} {{ $circumf }}"
                                stroke-dashoffset="{{ -$offset }}" transform="rotate(-90 55 55)"/>
                        @php $offset += $dash; @endphp
                    @endforeach
                    <text x="55" y="51" text-anchor="middle" font-family="'Playfair Display'" font-size="14" font-weight="900" fill="#1a1714">
                        {{ $categorySales->isNotEmpty() ? round(($categorySales->first()->total / $totalSales) * 100) : 0 }}%
                    </text>
                    <text x="55" y="63" text-anchor="middle" font-family="'IBM Plex Mono'" font-size="7" fill="#a09589" letter-spacing="1">
                        {{ strtoupper(Str::limit($categorySales->first()->name ?? 'N/A', 8)) }}
                    </text>
                </svg>
            </div>
            <div class="flex flex-col gap-3 mt-1">
                @foreach($categorySales as $j => $cat)
                    @php $pct = round(($cat->total / $totalSales) * 100); @endphp
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-[2px] flex-shrink-0" style="background:{{ $catColors[$j % 4] }}"></span>
                        <span class="text-[11px] text-ink3 flex-1">{{ $cat->name }}</span>
                        <div class="flex-1 h-[3px] bg-cream2 rounded-full overflow-hidden">
                            <div class="h-full rounded-full" style="width:{{ $pct }}%;background:{{ $catColors[$j % 4] }}"></div>
                        </div>
                        <span class="font-mono text-[11px] font-medium text-ink w-8 text-right">{{ $pct }}%</span>
                    </div>
                @endforeach
            </div>
        </x-card>
    </div>

    {{-- Recent Transactions --}}
    @can('view sales')
    <x-data-table title="Recent Transactions">
        <x-slot:tableHeader>
            <x-btn :href="route('sales.index')" variant="ghost" size="sm">View all →</x-btn>
        </x-slot:tableHeader>
        <x-slot:head>
            <x-th>TXN Number</x-th>
            <x-th>Branch</x-th>
            <x-th>Cashier</x-th>
            <x-th>Customer</x-th>
            <x-th>Items</x-th>
            <x-th>Payment</x-th>
            <x-th>Total</x-th>
            <x-th>Status</x-th>
        </x-slot:head>
        @forelse($recentTransactions as $txn)
            <tr class="hover:bg-cream transition-colors cursor-pointer"
                onclick="window.location='{{ route('sales.show', $txn) }}'">
                <x-td><span class="font-mono text-[11px] text-ink3">{{ $txn->transaction_number }}</span></x-td>
                <x-td>{{ $txn->branch->name ?? '—' }}</x-td>
                <x-td>{{ optional($txn->cashier)->full_name ?? '—' }}</x-td>
                <x-td>{{ $txn->customer ? $txn->customer->full_name : 'Walk-in' }}</x-td>
                <x-td>{{ $txn->items_count ?? $txn->items->count() }}</x-td>
                <x-td>
                    <x-badge variant="{{ match($txn->payment_method) { 'gcash','maya' => 'navy', 'card' => 'terra', default => 'muted' } }}">
                        {{ ucfirst($txn->payment_method) }}
                    </x-badge>
                </x-td>
                <x-td><span class="font-mono text-[12px] font-medium">₱{{ number_format($txn->total_amount, 2) }}</span></x-td>
                <x-td>
                    <x-badge variant="{{ $txn->status === 'completed' ? 'ok' : ($txn->status === 'voided' ? 'error' : 'warn') }}">
                        {{ ucfirst($txn->status) }}
                    </x-badge>
                </x-td>
            </tr>
        @empty
            <tr><td colspan="8"><x-empty-state title="No transactions today" icon="activity" /></td></tr>
        @endforelse
    </x-data-table>
    @endcan

    {{-- Low stock alert panel (branch managers / supervisors / inventory) --}}
    @can('view inventory')
        @if(($lowStockCount ?? 0) > 0)
        <div class="bg-warn/5 border border-warn/20 rounded-lg px-5 py-4 flex items-center justify-between mt-4">
            <div class="flex items-center gap-3">
                <x-icon name="alert-triangle" class="w-4 h-4 text-warn flex-shrink-0" />
                <div>
                    <p class="text-[12.5px] font-semibold text-ink">{{ $lowStockCount }} product{{ $lowStockCount > 1 ? 's' : '' }} below reorder level</p>
                    <p class="font-mono text-[10px] text-ink4 mt-0.5">Immediate restocking may be required.</p>
                </div>
            </div>
            <x-btn :href="route('inventory.index')" variant="ghost" size="sm">View Inventory →</x-btn>
        </div>
        @endif
    @endcan

</x-app-layout>
