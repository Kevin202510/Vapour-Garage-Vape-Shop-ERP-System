<x-app-layout :title="'Order ' . $onlineOrder->order_number">

    <x-page-header overline="E-Commerce" :title="$onlineOrder->order_number"
                   subtitle="Placed {{ $onlineOrder->created_at->format('F d, Y · g:i A') }}">
        <x-slot:actions>
            <x-btn :href="route('online-orders.index')" variant="ghost" size="sm">← All Orders</x-btn>
        </x-slot:actions>
    </x-page-header>

    @if($errors->has('error'))
        <div style="margin-bottom:16px;padding:12px 16px;background:rgba(224,80,80,0.08);border:1px solid rgba(224,80,80,0.3);border-radius:4px;font-family:'Share Tech Mono',monospace;font-size:11px;color:#e05050;letter-spacing:0.04em">
            {{ $errors->first('error') }}
        </div>
    @endif
    @if(session('success'))
        <div style="margin-bottom:16px;padding:12px 16px;background:rgba(57,255,20,0.06);border:1px solid rgba(57,255,20,0.25);border-radius:4px;font-family:'Share Tech Mono',monospace;font-size:11px;color:#39ff14;letter-spacing:0.04em">
            {{ session('success') }}
        </div>
    @endif

    <div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start">

        {{-- Left column --}}
        <div style="display:flex;flex-direction:column;gap:16px">

            {{-- Customer info --}}
            <div class="bg-navy border border-cream3 rounded-lg overflow-hidden">
                <div style="padding:14px 18px;border-bottom:1px solid rgba(124,34,212,0.15)">
                    <h3 style="font-family:'Share Tech Mono',monospace;font-size:10px;letter-spacing:0.12em;text-transform:uppercase;color:#7a7490">Customer Information</h3>
                </div>
                <div style="padding:18px;display:grid;grid-template-columns:1fr 1fr;gap:14px">
                    <div>
                        <p style="font-family:'Share Tech Mono',monospace;font-size:9px;color:#7a7490;letter-spacing:0.08em;text-transform:uppercase;margin-bottom:4px">Name</p>
                        <p style="font-size:14px;font-weight:600;color:#e8e4f0">{{ $onlineOrder->full_name }}</p>
                    </div>
                    <div>
                        <p style="font-family:'Share Tech Mono',monospace;font-size:9px;color:#7a7490;letter-spacing:0.08em;text-transform:uppercase;margin-bottom:4px">Email</p>
                        <p style="font-size:13px;color:#e8e4f0">{{ $onlineOrder->email }}</p>
                    </div>
                    <div>
                        <p style="font-family:'Share Tech Mono',monospace;font-size:9px;color:#7a7490;letter-spacing:0.08em;text-transform:uppercase;margin-bottom:4px">Phone</p>
                        <p style="font-size:13px;color:#e8e4f0">{{ $onlineOrder->phone }}</p>
                    </div>
                    <div>
                        <p style="font-family:'Share Tech Mono',monospace;font-size:9px;color:#7a7490;letter-spacing:0.08em;text-transform:uppercase;margin-bottom:4px">Address</p>
                        <p style="font-size:13px;color:#e8e4f0">{{ $onlineOrder->address }}, {{ $onlineOrder->city }}</p>
                    </div>
                    @if($onlineOrder->notes)
                    <div style="grid-column:1/-1">
                        <p style="font-family:'Share Tech Mono',monospace;font-size:9px;color:#7a7490;letter-spacing:0.08em;text-transform:uppercase;margin-bottom:4px">Notes</p>
                        <p style="font-size:13px;color:#e8e4f0">{{ $onlineOrder->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Order items --}}
            <div class="bg-navy border border-cream3 rounded-lg overflow-hidden">
                <div style="padding:14px 18px;border-bottom:1px solid rgba(124,34,212,0.15)">
                    <h3 style="font-family:'Share Tech Mono',monospace;font-size:10px;letter-spacing:0.12em;text-transform:uppercase;color:#7a7490">Order Items ({{ $onlineOrder->items->count() }})</h3>
                </div>
                <table style="width:100%;border-collapse:collapse">
                    <thead>
                        <tr style="border-bottom:1px solid rgba(124,34,212,0.12)">
                            <th style="padding:10px 18px;font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.1em;text-transform:uppercase;color:#7a7490;text-align:left">Product</th>
                            <th style="padding:10px 18px;font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.1em;text-transform:uppercase;color:#7a7490;text-align:right">Unit Price</th>
                            <th style="padding:10px 18px;font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.1em;text-transform:uppercase;color:#7a7490;text-align:center">Qty</th>
                            <th style="padding:10px 18px;font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.1em;text-transform:uppercase;color:#7a7490;text-align:right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($onlineOrder->items as $item)
                        <tr style="border-bottom:1px solid rgba(124,34,212,0.08)">
                            <td style="padding:12px 18px">
                                <p style="font-size:13px;font-weight:600;color:#e8e4f0">{{ $item->product_name }}</p>
                                <p style="font-family:'Share Tech Mono',monospace;font-size:9px;color:#7a7490;margin-top:2px">{{ $item->sku }}</p>
                            </td>
                            <td style="padding:12px 18px;font-family:'Share Tech Mono',monospace;font-size:12px;color:#e8e4f0;text-align:right">₱{{ number_format($item->unit_price, 2) }}</td>
                            <td style="padding:12px 18px;font-family:'Share Tech Mono',monospace;font-size:12px;color:#e8e4f0;text-align:center">{{ $item->quantity }}</td>
                            <td style="padding:12px 18px;font-family:'Share Tech Mono',monospace;font-size:12px;color:#e8e4f0;text-align:right">₱{{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="border-top:1px solid rgba(124,34,212,0.2)">
                            <td colspan="3" style="padding:10px 18px;font-family:'Share Tech Mono',monospace;font-size:10px;color:#7a7490;text-align:right">Subtotal</td>
                            <td style="padding:10px 18px;font-family:'Share Tech Mono',monospace;font-size:12px;color:#e8e4f0;text-align:right">₱{{ number_format($onlineOrder->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" style="padding:4px 18px;font-family:'Share Tech Mono',monospace;font-size:10px;color:#7a7490;text-align:right">VAT (12%)</td>
                            <td style="padding:4px 18px;font-family:'Share Tech Mono',monospace;font-size:12px;color:#e8e4f0;text-align:right">₱{{ number_format($onlineOrder->tax_amount, 2) }}</td>
                        </tr>
                        <tr style="border-top:1px solid rgba(124,34,212,0.15)">
                            <td colspan="3" style="padding:10px 18px;font-family:'Share Tech Mono',monospace;font-size:11px;font-weight:600;color:#e8e4f0;text-align:right;text-transform:uppercase;letter-spacing:0.06em">Total</td>
                            <td style="padding:10px 18px;font-family:'Share Tech Mono',monospace;font-size:16px;color:#39ff14;text-align:right;font-weight:700">₱{{ number_format($onlineOrder->total_amount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Right column --}}
        <div style="display:flex;flex-direction:column;gap:16px">

            {{-- Status & actions --}}
            <div class="bg-navy border border-cream3 rounded-lg overflow-hidden">
                <div style="padding:14px 18px;border-bottom:1px solid rgba(124,34,212,0.15)">
                    <h3 style="font-family:'Share Tech Mono',monospace;font-size:10px;letter-spacing:0.12em;text-transform:uppercase;color:#7a7490">Status</h3>
                </div>
                <div style="padding:18px;display:flex;flex-direction:column;gap:12px">
                    <div style="display:flex;align-items:center;justify-content:space-between">
                        <span style="font-family:'Share Tech Mono',monospace;font-size:10px;color:#7a7490;text-transform:uppercase;letter-spacing:0.08em">Current</span>
                        <x-badge variant="{{ match($onlineOrder->status) { 'completed' => 'ok', 'ready' => 'ok', 'confirmed','processing' => 'purple', 'pending' => 'warn', 'cancelled' => 'error', default => 'muted' } }}">
                            {{ $onlineOrder->status_label }}
                        </x-badge>
                    </div>

                    @if(! in_array($onlineOrder->status, ['completed', 'cancelled']))
                    <div style="display:flex;flex-direction:column;gap:8px;padding-top:8px;border-top:1px solid rgba(124,34,212,0.12)">
                        @if($onlineOrder->status === 'pending')
                            <form method="POST" action="{{ route('online-orders.update', $onlineOrder) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="confirmed">
                                <button type="submit" style="width:100%;padding:8px;background:rgba(57,255,20,0.1);color:#39ff14;border:1px solid rgba(57,255,20,0.3);border-radius:3px;font-family:'Share Tech Mono',monospace;font-size:10px;letter-spacing:0.1em;text-transform:uppercase;cursor:pointer">Confirm Order</button>
                            </form>
                        @elseif($onlineOrder->status === 'confirmed')
                            <form method="POST" action="{{ route('online-orders.update', $onlineOrder) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="processing">
                                <button type="submit" style="width:100%;padding:8px;background:rgba(124,34,212,0.15);color:#9b3df0;border:1px solid rgba(124,34,212,0.35);border-radius:3px;font-family:'Share Tech Mono',monospace;font-size:10px;letter-spacing:0.1em;text-transform:uppercase;cursor:pointer">Mark Processing</button>
                            </form>
                        @elseif($onlineOrder->status === 'processing')
                            <form method="POST" action="{{ route('online-orders.update', $onlineOrder) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="ready">
                                <button type="submit" style="width:100%;padding:8px;background:rgba(201,148,58,0.12);color:#c9943a;border:1px solid rgba(201,148,58,0.3);border-radius:3px;font-family:'Share Tech Mono',monospace;font-size:10px;letter-spacing:0.1em;text-transform:uppercase;cursor:pointer">Mark Ready</button>
                            </form>
                        @elseif($onlineOrder->status === 'ready')
                            <form method="POST" action="{{ route('online-orders.update', $onlineOrder) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" style="width:100%;padding:8px;background:rgba(57,255,20,0.1);color:#39ff14;border:1px solid rgba(57,255,20,0.3);border-radius:3px;font-family:'Share Tech Mono',monospace;font-size:10px;letter-spacing:0.1em;text-transform:uppercase;cursor:pointer">Complete &amp; Decrement Stock</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('online-orders.update', $onlineOrder) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="cancelled">
                            <button type="submit" onclick="return confirm('Cancel this order?')"
                                    style="width:100%;padding:8px;background:rgba(224,80,80,0.08);color:#e05050;border:1px solid rgba(224,80,80,0.25);border-radius:3px;font-family:'Share Tech Mono',monospace;font-size:10px;letter-spacing:0.1em;text-transform:uppercase;cursor:pointer">Cancel Order</button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Order details --}}
            <div class="bg-navy border border-cream3 rounded-lg overflow-hidden">
                <div style="padding:14px 18px;border-bottom:1px solid rgba(124,34,212,0.15)">
                    <h3 style="font-family:'Share Tech Mono',monospace;font-size:10px;letter-spacing:0.12em;text-transform:uppercase;color:#7a7490">Order Details</h3>
                </div>
                <div style="padding:18px;display:flex;flex-direction:column;gap:12px">
                    <div>
                        <p style="font-family:'Share Tech Mono',monospace;font-size:9px;color:#7a7490;letter-spacing:0.08em;text-transform:uppercase;margin-bottom:3px">Payment Method</p>
                        <x-badge variant="{{ match($onlineOrder->payment_method) { 'gcash','maya' => 'ok', 'bank_transfer' => 'purple', 'cod' => 'gold', default => 'muted' } }}">
                            {{ ucfirst(str_replace('_', ' ', $onlineOrder->payment_method)) }}
                        </x-badge>
                    </div>
                    @if($onlineOrder->payment_reference)
                    <div>
                        <p style="font-family:'Share Tech Mono',monospace;font-size:9px;color:#7a7490;letter-spacing:0.08em;text-transform:uppercase;margin-bottom:3px">Payment Reference</p>
                        <p style="font-family:'Share Tech Mono',monospace;font-size:12px;color:#e8e4f0">{{ $onlineOrder->payment_reference }}</p>
                    </div>
                    @endif
                    <div>
                        <p style="font-family:'Share Tech Mono',monospace;font-size:9px;color:#7a7490;letter-spacing:0.08em;text-transform:uppercase;margin-bottom:3px">Pickup Branch</p>
                        <p style="font-size:13px;color:#e8e4f0">{{ $onlineOrder->branch?->name ?? '— (Home Delivery)' }}</p>
                    </div>
                    @if($onlineOrder->fulfilled_at)
                    <div>
                        <p style="font-family:'Share Tech Mono',monospace;font-size:9px;color:#7a7490;letter-spacing:0.08em;text-transform:uppercase;margin-bottom:3px">Fulfilled At</p>
                        <p style="font-family:'Share Tech Mono',monospace;font-size:11px;color:#e8e4f0">{{ $onlineOrder->fulfilled_at->format('M d, Y · g:i A') }}</p>
                    </div>
                    @endif
                    @if($onlineOrder->fulfiller)
                    <div>
                        <p style="font-family:'Share Tech Mono',monospace;font-size:9px;color:#7a7490;letter-spacing:0.08em;text-transform:uppercase;margin-bottom:3px">Fulfilled By</p>
                        <p style="font-size:13px;color:#e8e4f0">{{ $onlineOrder->fulfiller->full_name ?? '—' }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
