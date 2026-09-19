<x-app-layout title="Online Orders">

    <x-page-header overline="E-Commerce" title="Online Orders"
                   :subtitle="$activeBranchLabel . ' · ' . $totalPending . ' pending · ' . $totalToday . ' received today'">
        <x-slot:actions>
            @can('view sales')
            <x-btn :href="route('online-orders.export', request()->query())" variant="ghost" size="sm">
                <x-icon name="download" class="w-3 h-3" /> Export CSV
            </x-btn>
            @endcan
            <x-btn :href="route('shop.index')" variant="ghost" size="sm" target="_blank">
                View Shop →
            </x-btn>
        </x-slot:actions>
    </x-page-header>

    @if($errors->has('error'))
        <x-alert type="error" :message="$errors->first('error')" class="mb-5" />
    @endif
    @if(session('success'))
        <x-alert type="success" :message="session('success')" class="mb-5" />
    @endif

    {{-- KPI cards --}}
    <div class="grid grid-cols-4 gap-3 mb-6">
        <x-kpi-card label="Pending"       :value="$totalPending"                       color="purple" />
        <x-kpi-card label="Confirmed"     :value="$totalConfirmed"                     color="navy" />
        <x-kpi-card label="Ready"         :value="$totalReady"                         color="gold" />
        <x-kpi-card label="Today Revenue" :value="'₱' . number_format($todayRevenue)"  color="terra" />
    </div>

    {{-- Search & date filters --}}
    <form method="GET" action="{{ route('online-orders.index') }}" class="flex items-center gap-2 flex-wrap mb-4">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Order #, name, email, phone…"
               class="erp-input" style="width:220px">
        <input type="date" name="date_from" value="{{ request('date_from') }}"
               class="erp-input" style="width:150px">
        <span class="font-mono text-[10px] text-ink4">to</span>
        <input type="date" name="date_to" value="{{ request('date_to') }}"
               class="erp-input" style="width:150px">
        @if(auth()->user()->isSuperAdmin())
        <select name="branch_id" class="erp-select" style="width:180px">
            <option value="">All Branches</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}" {{ $effectiveBranchId == $branch->id ? 'selected' : '' }}>
                    {{ $branch->name }}
                </option>
            @endforeach
        </select>
        @endif
        <x-btn type="submit" variant="ghost" size="sm">Apply</x-btn>
        @if(request()->hasAny(['search','date_from','date_to','branch_id']))
            <a href="{{ route('online-orders.index', request()->only('status')) }}"
               class="font-mono text-[10px] text-ink4 hover:text-ink">Clear</a>
        @endif
    </form>

    {{-- Status tabs --}}
    <div class="flex gap-1 flex-wrap mb-5">
        @foreach(['all' => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'processing' => 'Processing', 'ready' => 'Ready', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $val => $lbl)
        <a href="{{ route('online-orders.index', array_merge(request()->query(), ['status' => $val])) }}"
           class="px-3.5 py-[5px] rounded text-[10px] font-mono tracking-wide transition-all text-decoration-none
                  {{ request('status', 'all') === $val
                       ? 'bg-purple/10 text-purple border border-purple/30'
                       : 'text-ink4 border border-cream3 hover:text-ink hover:border-purple/20' }}">
            {{ $lbl }}
        </a>
        @endforeach
    </div>

    <x-data-table title="Online Orders">
        <x-slot:tableHeader>
            <span class="font-mono text-[11px] text-ink4">{{ $orders->total() }} orders</span>
        </x-slot:tableHeader>
        <x-slot:head>
            <x-th>Order #</x-th>
            <x-th>Customer</x-th>
            <x-th>Contact</x-th>
            <x-th>Items</x-th>
            <x-th>Payment</x-th>
            <x-th>Pickup Branch</x-th>
            <x-th>Total</x-th>
            <x-th>Date</x-th>
            <x-th>Status</x-th>
            <x-th>Actions</x-th>
        </x-slot:head>

        @forelse($orders as $order)
        <tr class="hover:bg-cream transition-colors cursor-pointer"
            onclick="window.location='{{ route('online-orders.show', $order) }}'">
            <x-td>
                <a href="{{ route('online-orders.show', $order) }}"
                   class="font-mono text-[11px] text-purple hover:text-purple-2 font-semibold"
                   @click.stop>
                    {{ $order->order_number }}
                </a>
            </x-td>
            <x-td class="font-semibold text-ink">{{ $order->full_name }}</x-td>
            <x-td>
                <div class="text-[11px] text-ink2">{{ $order->phone }}</div>
                <div class="font-mono text-[9.5px] text-ink4 mt-0.5">{{ $order->email }}</div>
            </x-td>
            <x-td>
                <span class="font-mono text-[12px] text-ink2">{{ $order->items->count() }} items</span>
            </x-td>
            <x-td>
                <x-badge variant="{{ match($order->payment_method) { 'gcash','maya' => 'ok', 'bank_transfer' => 'purple', 'cod' => 'gold', default => 'muted' } }}">
                    {{ ucfirst(str_replace('_',' ', $order->payment_method)) }}
                </x-badge>
            </x-td>
            <x-td class="text-ink3 text-[12px]">{{ $order->branch?->name ?? '— Delivery' }}</x-td>
            <x-td>
                <span class="font-mono text-[12px] font-semibold text-ok">
                    ₱{{ number_format($order->total_amount, 2) }}
                </span>
            </x-td>
            <x-td>
                <span class="font-mono text-[10px] text-ink4">{{ $order->created_at->format('M d · g:i A') }}</span>
            </x-td>
            <x-td>
                <x-badge variant="{{ match($order->status) { 'completed','ready' => 'ok', 'confirmed','processing' => 'navy', 'pending' => 'warn', 'cancelled' => 'error', default => 'muted' } }}">
                    {{ $order->status_label }}
                </x-badge>
            </x-td>
            <x-td>
                <div class="flex gap-1.5" @click.stop>
                    <x-btn :href="route('online-orders.show', $order)" variant="ghost" size="sm">View</x-btn>

                    @if($order->status === 'pending')
                        <form method="POST" action="{{ route('online-orders.update', $order) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="confirmed">
                            <x-btn type="submit" variant="ghost" size="sm"
                                   class="!text-ok !border-ok/30 hover:!bg-ok/10">Confirm</x-btn>
                        </form>
                    @elseif($order->status === 'confirmed')
                        <form method="POST" action="{{ route('online-orders.update', $order) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="processing">
                            <x-btn type="submit" variant="ghost" size="sm">Process</x-btn>
                        </form>
                    @elseif($order->status === 'processing')
                        <form method="POST" action="{{ route('online-orders.update', $order) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="ready">
                            <x-btn type="submit" variant="ghost" size="sm"
                                   class="!text-gold !border-gold/30 hover:!bg-gold/10">Ready</x-btn>
                        </form>
                    @elseif($order->status === 'ready')
                        <form method="POST" action="{{ route('online-orders.update', $order) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="completed">
                            <x-btn type="submit" variant="primary" size="sm">Complete</x-btn>
                        </form>
                    @endif

                    @if(!in_array($order->status, ['completed','cancelled']))
                        <form method="POST" action="{{ route('online-orders.update', $order) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="cancelled">
                            <x-btn type="submit" variant="danger" size="sm"
                                   onclick="return confirm('Cancel this order?')">Cancel</x-btn>
                        </form>
                    @endif
                </div>
            </x-td>
        </tr>
        @empty
            <tr><td colspan="10"><x-empty-state title="No orders found" icon="shopping-bag" /></td></tr>
        @endforelse

        <x-slot:pagination>{{ $orders->withQueryString()->links('vendor.pagination.simple') }}</x-slot:pagination>
    </x-data-table>

</x-app-layout>
