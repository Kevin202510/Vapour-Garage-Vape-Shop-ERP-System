<x-app-layout title="Online Orders">

    <x-page-header overline="E-Commerce" title="Online Orders"
                   :subtitle="$totalPending . ' pending · ' . $totalToday . ' received today'">
        <x-slot:actions>
            <x-btn :href="route('shop.index')" variant="ghost" size="sm" target="_blank">
                View Shop →
            </x-btn>
        </x-slot:actions>
    </x-page-header>

    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px">
        <x-kpi-card label="Pending"    :value="$totalPending"   color="purple" />
        <x-kpi-card label="Confirmed"  :value="$totalConfirmed" color="green" />
        <x-kpi-card label="Ready"      :value="$totalReady"     color="gold" />
        <x-kpi-card label="Today Revenue" :value="'₱' . number_format($todayRevenue)" color="green" />
    </div>

    {{-- Status filter --}}
    <div style="display:flex;gap:4px;margin-bottom:20px;flex-wrap:wrap">
        @foreach(['all' => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'processing' => 'Processing', 'ready' => 'Ready', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $val => $lbl)
        <a href="{{ route('online-orders.index', array_merge(request()->query(), ['status' => $val])) }}"
           style="padding:6px 14px;border-radius:3px;font-family:'Share Tech Mono',monospace;font-size:10px;letter-spacing:0.1em;text-transform:uppercase;text-decoration:none;transition:all 0.2s;border:1px solid {{ request('status', 'all') === $val ? 'rgba(124,34,212,0.6)' : 'rgba(124,34,212,0.2)' }};background:{{ request('status', 'all') === $val ? 'rgba(124,34,212,0.18)' : 'transparent' }};color:{{ request('status', 'all') === $val ? '#9b3df0' : '#7a7490' }}">
            {{ $lbl }}
        </a>
        @endforeach
    </div>

    <x-data-table title="Online Orders">
        <x-slot:tableHeader>
            <span style="font-family:'Share Tech Mono',monospace;font-size:11px;color:#7a7490">{{ $orders->total() }} orders</span>
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
        <tr style="border-bottom:1px solid rgba(124,34,212,0.1);transition:background 0.15s;cursor:pointer"
            onmouseover="this.style.background='rgba(124,34,212,0.05)'" onmouseout="this.style.background=''">
            <x-td>
                <span style="font-family:'Share Tech Mono',monospace;font-size:11px;color:#9b3df0;letter-spacing:0.06em">
                    {{ $order->order_number }}
                </span>
            </x-td>
            <x-td style="font-weight:600;color:#e8e4f0">{{ $order->full_name }}</x-td>
            <x-td>
                <div style="font-size:11px;color:#7a7490">{{ $order->phone }}</div>
                <div style="font-family:'Share Tech Mono',monospace;font-size:9.5px;color:#7a7490;margin-top:1px">{{ $order->email }}</div>
            </x-td>
            <x-td><span style="font-family:'Share Tech Mono',monospace">{{ $order->items->count() }} items</span></x-td>
            <x-td>
                <x-badge variant="{{ match($order->payment_method) { 'gcash','maya' => 'ok', 'bank_transfer' => 'purple', 'cod' => 'gold', default => 'muted' } }}">
                    {{ ucfirst(str_replace('_',' ', $order->payment_method)) }}
                </x-badge>
            </x-td>
            <x-td style="color:#7a7490;font-size:12px">{{ $order->branch?->name ?? 'Delivery' }}</x-td>
            <x-td><span style="font-family:'Share Tech Mono',monospace;font-size:12px;color:#39ff14">₱{{ number_format($order->total_amount, 2) }}</span></x-td>
            <x-td><span style="font-family:'Share Tech Mono',monospace;font-size:10px;color:#7a7490">{{ $order->created_at->format('M d · g:i A') }}</span></x-td>
            <x-td>
                <x-badge variant="{{ match($order->status) { 'completed' => 'ok', 'ready' => 'ok', 'confirmed','processing' => 'purple', 'pending' => 'warn', 'cancelled' => 'error', default => 'muted' } }}">
                    {{ $order->status_label }}
                </x-badge>
            </x-td>
            <x-td>
                <div style="display:flex;gap:6px">
                    @if($order->status === 'pending')
                        <form method="POST" action="{{ route('online-orders.update', $order) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="confirmed">
                            <button type="submit" style="padding:4px 10px;background:rgba(57,255,20,0.1);color:#39ff14;border:1px solid rgba(57,255,20,0.3);border-radius:3px;font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.1em;text-transform:uppercase;cursor:pointer;transition:all 0.2s"
                                    onmouseover="this.style.background='rgba(57,255,20,0.2)'" onmouseout="this.style.background='rgba(57,255,20,0.1)'">
                                Confirm
                            </button>
                        </form>
                    @elseif($order->status === 'confirmed')
                        <form method="POST" action="{{ route('online-orders.update', $order) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="processing">
                            <button type="submit" style="padding:4px 10px;background:rgba(124,34,212,0.15);color:#9b3df0;border:1px solid rgba(124,34,212,0.35);border-radius:3px;font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.1em;text-transform:uppercase;cursor:pointer">Process</button>
                        </form>
                    @elseif($order->status === 'processing')
                        <form method="POST" action="{{ route('online-orders.update', $order) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="ready">
                            <button type="submit" style="padding:4px 10px;background:rgba(201,148,58,0.12);color:#c9943a;border:1px solid rgba(201,148,58,0.3);border-radius:3px;font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.1em;text-transform:uppercase;cursor:pointer">Ready</button>
                        </form>
                    @elseif($order->status === 'ready')
                        <form method="POST" action="{{ route('online-orders.update', $order) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" style="padding:4px 10px;background:rgba(57,255,20,0.1);color:#39ff14;border:1px solid rgba(57,255,20,0.3);border-radius:3px;font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.1em;text-transform:uppercase;cursor:pointer">Complete</button>
                        </form>
                    @endif
                    @if(!in_array($order->status, ['completed','cancelled']))
                        <form method="POST" action="{{ route('online-orders.update', $order) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="cancelled">
                            <button type="submit" style="padding:4px 10px;background:rgba(224,80,80,0.08);color:#e05050;border:1px solid rgba(224,80,80,0.25);border-radius:3px;font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.1em;text-transform:uppercase;cursor:pointer"
                                    onclick="return confirm('Cancel this order?')">Cancel</button>
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
