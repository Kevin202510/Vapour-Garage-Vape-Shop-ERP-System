<x-app-layout :title="'Order ' . $onlineOrder->order_number">

    <x-page-header overline="E-Commerce" :title="$onlineOrder->order_number"
                   :subtitle="'Placed ' . $onlineOrder->created_at->format('F d, Y · g:i A')">
        <x-slot:actions>
            <x-btn :href="route('online-orders.index')" variant="ghost" size="sm">← All Orders</x-btn>
        </x-slot:actions>
    </x-page-header>

    @if($errors->has('error'))
        <x-alert type="error" :message="$errors->first('error')" class="mb-5" />
    @endif
    @if(session('success'))
        <x-alert type="success" :message="session('success')" class="mb-5" />
    @endif

    <div class="grid grid-cols-[1fr_300px] gap-5 items-start">

        {{-- ── Left column ─────────────────────────────────────── --}}
        <div class="flex flex-col gap-4">

            {{-- Customer info --}}
            <x-card title="Customer Information">
                <div class="grid grid-cols-2 gap-4">
                    @foreach([
                        'Name'    => $onlineOrder->full_name,
                        'Email'   => $onlineOrder->email,
                        'Phone'   => $onlineOrder->phone,
                        'Address' => $onlineOrder->address . ', ' . $onlineOrder->city,
                    ] as $label => $value)
                    <div>
                        <p class="erp-label mb-1">{{ $label }}</p>
                        <p class="text-[13px] text-ink font-medium">{{ $value }}</p>
                    </div>
                    @endforeach
                    @if($onlineOrder->notes)
                    <div class="col-span-2">
                        <p class="erp-label mb-1">Notes</p>
                        <p class="text-[13px] text-ink2">{{ $onlineOrder->notes }}</p>
                    </div>
                    @endif
                </div>
            </x-card>

            {{-- Order items --}}
            <x-card :title="'Order Items (' . $onlineOrder->items->count() . ')'">
                <div class="-mx-5 -mb-5">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="border-b border-cream3 bg-cream/60">
                                <th class="erp-th text-left">Product</th>
                                <th class="erp-th text-right">Unit Price</th>
                                <th class="erp-th text-center">Qty</th>
                                <th class="erp-th text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($onlineOrder->items as $item)
                            <tr class="border-b border-cream3 last:border-0 hover:bg-cream transition-colors">
                                <td class="px-5 py-3">
                                    <p class="text-[13px] font-semibold text-ink">{{ $item->product_name }}</p>
                                    <p class="font-mono text-[9px] text-ink4 mt-0.5">{{ $item->sku }}</p>
                                </td>
                                <td class="px-5 py-3 font-mono text-[12px] text-ink2 text-right">
                                    ₱{{ number_format($item->unit_price, 2) }}
                                </td>
                                <td class="px-5 py-3 font-mono text-[12px] text-ink2 text-center">
                                    {{ $item->quantity }}
                                </td>
                                <td class="px-5 py-3 font-mono text-[12px] font-semibold text-ink text-right">
                                    ₱{{ number_format($item->subtotal, 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t border-cream3 bg-cream/40">
                            <tr>
                                <td colspan="3" class="px-5 py-2 font-mono text-[10px] text-ink4 text-right uppercase tracking-wide">Subtotal</td>
                                <td class="px-5 py-2 font-mono text-[12px] text-ink2 text-right">₱{{ number_format($onlineOrder->subtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="px-5 py-1 font-mono text-[10px] text-ink4 text-right uppercase tracking-wide">VAT (12%)</td>
                                <td class="px-5 py-1 font-mono text-[12px] text-ink2 text-right">₱{{ number_format($onlineOrder->tax_amount, 2) }}</td>
                            </tr>
                            <tr class="border-t border-cream3">
                                <td colspan="3" class="px-5 py-3 font-display text-[13px] font-bold text-ink text-right uppercase tracking-wide">Total</td>
                                <td class="px-5 py-3 font-display text-[18px] font-bold text-ok text-right">
                                    ₱{{ number_format($onlineOrder->total_amount, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </x-card>
        </div>

        {{-- ── Right column ─────────────────────────────────────── --}}
        <div class="flex flex-col gap-4">

            {{-- Status & actions --}}
            <x-card title="Status">
                <div class="flex flex-col gap-3">
                    <div class="flex items-center justify-between">
                        <span class="font-mono text-[9px] text-ink4 uppercase tracking-widest">Current</span>
                        <x-badge variant="{{ match($onlineOrder->status) {
                            'completed','ready' => 'ok',
                            'confirmed','processing' => 'navy',
                            'pending'   => 'warn',
                            'cancelled' => 'error',
                            default     => 'muted'
                        } }}">
                            {{ $onlineOrder->status_label }}
                        </x-badge>
                    </div>

                    @if(! in_array($onlineOrder->status, ['completed', 'cancelled']))
                    <div class="flex flex-col gap-2 pt-3 border-t border-cream3">
                        @if($onlineOrder->status === 'pending')
                            <form method="POST" action="{{ route('online-orders.update', $onlineOrder) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="confirmed">
                                <x-btn type="submit" variant="ghost" size="sm" class="w-full justify-center">
                                    ✓ Confirm Order
                                </x-btn>
                            </form>
                        @elseif($onlineOrder->status === 'confirmed')
                            <form method="POST" action="{{ route('online-orders.update', $onlineOrder) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="processing">
                                <x-btn type="submit" variant="ghost" size="sm" class="w-full justify-center">
                                    Mark Processing
                                </x-btn>
                            </form>
                        @elseif($onlineOrder->status === 'processing')
                            <form method="POST" action="{{ route('online-orders.update', $onlineOrder) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="ready">
                                <x-btn type="submit" variant="ghost" size="sm" class="w-full justify-center">
                                    Mark Ready for Pickup
                                </x-btn>
                            </form>
                        @elseif($onlineOrder->status === 'ready')
                            <form method="POST" action="{{ route('online-orders.update', $onlineOrder) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="completed">
                                <x-btn type="submit" variant="primary" size="sm" class="w-full justify-center">
                                    Complete &amp; Decrement Stock
                                </x-btn>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('online-orders.update', $onlineOrder) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="cancelled">
                            <x-btn type="submit" variant="danger" size="sm" class="w-full justify-center"
                                   onclick="return confirm('Cancel this order?')">
                                Cancel Order
                            </x-btn>
                        </form>
                    </div>
                    @endif
                </div>
            </x-card>

            {{-- Order details --}}
            <x-card title="Order Details">
                <div class="flex flex-col gap-4">
                    <div>
                        <p class="erp-label mb-1">Payment Method</p>
                        <x-badge variant="{{ match($onlineOrder->payment_method) {
                            'gcash','maya' => 'ok',
                            'bank_transfer' => 'purple',
                            'cod' => 'gold',
                            default => 'muted'
                        } }}">
                            {{ ucfirst(str_replace('_', ' ', $onlineOrder->payment_method)) }}
                        </x-badge>
                    </div>

                    @if($onlineOrder->payment_reference)
                    <div>
                        <p class="erp-label mb-1">Payment Reference</p>
                        <p class="font-mono text-[12px] text-ink2">{{ $onlineOrder->payment_reference }}</p>
                    </div>
                    @endif

                    <div>
                        <p class="erp-label mb-1">Pickup Branch</p>
                        <p class="text-[13px] text-ink">{{ $onlineOrder->branch?->name ?? '— Home Delivery' }}</p>
                    </div>

                    @if($onlineOrder->fulfilled_at)
                    <div>
                        <p class="erp-label mb-1">Fulfilled At</p>
                        <p class="font-mono text-[11px] text-ink2">{{ $onlineOrder->fulfilled_at->format('M d, Y · g:i A') }}</p>
                    </div>
                    @endif

                    @if($onlineOrder->fulfiller)
                    <div>
                        <p class="erp-label mb-1">Fulfilled By</p>
                        <p class="text-[13px] text-ink">{{ $onlineOrder->fulfiller->full_name ?? '—' }}</p>
                    </div>
                    @endif
                </div>
            </x-card>
        </div>
    </div>

</x-app-layout>
