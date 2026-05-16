<x-app-layout :title="$transaction->transaction_number">

    <x-page-header
        overline="Sales · Transaction"
        :title="$transaction->transaction_number"
        :subtitle="$transaction->transaction_date->format('F d, Y · g:i A') . ' · ' . ($transaction->branch->name ?? '—')"
    >
        <x-slot:actions>
            <x-btn :href="route('sales.index')" variant="ghost" size="sm">← All Sales</x-btn>
            @if($transaction->status === 'completed')
                @can('void sales')
                    <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'void-txn' }))"
                            class="inline-flex items-center px-3 py-[5px] text-[11px] font-medium text-danger/70 border border-danger/20 rounded-md hover:bg-danger/5 transition-colors">
                        Void Transaction
                    </button>
                @endcan
            @endif
        </x-slot:actions>
    </x-page-header>

    {{-- Void modal --}}
    <x-modal name="void-txn" title="Void Transaction" max-width="sm">
        <form method="POST" action="{{ route('sales.void', $transaction) }}">
            @csrf
            <p class="text-[13px] text-ink3 mb-4">
                Voiding will reverse inventory and loyalty points. This cannot be undone.
            </p>
            <x-form-input label="Reason for void" name="reason" required
                          placeholder="e.g. Customer changed mind, wrong item scanned…" />
            <div class="flex items-center justify-end gap-3 pt-3 mt-2 border-t border-cream3">
                <x-btn type="button" variant="ghost" size="sm"
                       x-on:click="$dispatch('close-modal', 'void-txn')">Cancel</x-btn>
                <x-btn type="submit" variant="danger" size="sm">Confirm Void</x-btn>
            </div>
        </form>
    </x-modal>

    <div class="grid grid-cols-4 gap-4 mb-6">
        <x-kpi-card label="Subtotal"        :value="'₱' . number_format($transaction->subtotal, 2)"        color="terra" />
        <x-kpi-card label="Discount"        :value="'₱' . number_format($transaction->discount_amount, 2)" color="navy" />
        <x-kpi-card label="VAT (12%)"       :value="'₱' . number_format($transaction->tax_amount, 2)"      color="gold" />
        <x-kpi-card label="Total Charged"   :value="'₱' . number_format($transaction->total_amount, 2)"    color="sage" />
    </div>

    <div class="grid grid-cols-2 gap-5 mb-5">

        {{-- Transaction info --}}
        <x-card title="Transaction Details">
            <dl class="flex flex-col gap-4">
                @foreach([
                    'TXN Number'     => $transaction->transaction_number,
                    'Branch'         => $transaction->branch->name ?? '—',
                    'Cashier'        => optional($transaction->cashier)->full_name ?? '—',
                    'Customer'       => $transaction->customer ? $transaction->customer->full_name : 'Walk-in',
                    'Payment Method' => ucfirst($transaction->payment_method),
                    'Amount Tendered'=> '₱' . number_format($transaction->amount_tendered, 2),
                    'Change Given'   => '₱' . number_format($transaction->change_amount, 2),
                    'Date & Time'    => $transaction->transaction_date->format('F d, Y · g:i A'),
                    'Status'         => ucfirst($transaction->status),
                ] as $label => $value)
                    <div class="flex justify-between items-start border-b border-cream2 pb-3 last:border-0 last:pb-0">
                        <dt class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 pt-0.5 flex-shrink-0">{{ $label }}</dt>
                        <dd class="text-[12.5px] text-ink2 text-right ml-4">
                            @if($label === 'Status')
                                <x-badge variant="{{ match($transaction->status) { 'completed' => 'ok', 'voided' => 'error', 'refunded' => 'warn', default => 'muted' } }}">
                                    {{ $value }}
                                </x-badge>
                            @elseif($label === 'Payment Method')
                                <x-badge variant="{{ match($transaction->payment_method) { 'gcash','maya' => 'navy', 'card' => 'terra', default => 'muted' } }}">
                                    {{ $value }}
                                </x-badge>
                            @else
                                {{ $value }}
                            @endif
                        </dd>
                    </div>
                @endforeach
            </dl>

            @if($transaction->void_reason)
                <div class="mt-4 pt-4 border-t border-cream2">
                    <p class="font-mono text-[9px] tracking-[0.14em] uppercase text-danger mb-1">Void Reason</p>
                    <p class="text-[12.5px] text-ink3">{{ $transaction->void_reason }}</p>
                </div>
            @endif
        </x-card>

        {{-- Customer loyalty earned --}}
        <x-card title="Loyalty & Customer">
            @if($transaction->customer)
                <div class="flex items-center gap-3 p-4 bg-cream rounded-lg mb-4">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-terra to-gold flex items-center justify-center font-serif font-bold text-white text-sm flex-shrink-0">
                        {{ strtoupper(substr($transaction->customer->first_name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-semibold text-[13px] text-ink">{{ $transaction->customer->full_name }}</p>
                        <p class="font-mono text-[10px] text-ink4 mt-0.5">
                            {{ number_format($transaction->customer->loyalty_points) }} pts ·
                            <x-badge variant="{{ match($transaction->customer->tier) { 'platinum' => 'terra', 'gold' => 'gold', 'silver' => 'navy', default => 'muted' } }}">
                                {{ ucfirst($transaction->customer->tier) }}
                            </x-badge>
                        </p>
                    </div>
                </div>
                @php $pointsEarned = (int) floor($transaction->total_amount / 10); @endphp
                <div class="flex justify-between items-center px-4 py-3 bg-ok/5 border border-ok/20 rounded-lg">
                    <p class="text-[12px] text-ink3">Points earned this transaction</p>
                    <p class="font-serif text-[18px] font-bold text-ok">+{{ $pointsEarned }}</p>
                </div>
            @else
                <x-empty-state title="Walk-in customer" description="No loyalty account linked to this transaction." icon="user" />
            @endif
        </x-card>
    </div>

    {{-- Line items --}}
    <x-data-table title="Items Purchased">
        <x-slot:head>
            <x-th>SKU</x-th>
            <x-th>Product</x-th>
            <x-th>Unit Price</x-th>
            <x-th>Qty</x-th>
            <x-th>Discount</x-th>
            <x-th>VAT</x-th>
            <x-th>Subtotal</x-th>
        </x-slot:head>
        @foreach($transaction->items as $item)
            <tr class="hover:bg-cream transition-colors">
                <x-td><span class="font-mono text-[11px] text-ink3">{{ $item->product->sku ?? '—' }}</span></x-td>
                <x-td class="font-medium">{{ $item->product->name ?? 'Unknown Product' }}</x-td>
                <x-td><span class="font-mono">₱{{ number_format($item->unit_price, 2) }}</span></x-td>
                <x-td><span class="font-mono font-medium">{{ $item->quantity }}</span></x-td>
                <x-td>
                    @if($item->discount_amount > 0)
                        <span class="font-mono text-ok">− ₱{{ number_format($item->discount_amount, 2) }}</span>
                    @else
                        <span class="text-ink4">—</span>
                    @endif
                </x-td>
                <x-td><span class="font-mono text-[11px] text-ink4">₱{{ number_format($item->tax_amount, 2) }}</span></x-td>
                <x-td><span class="font-mono font-medium">₱{{ number_format($item->subtotal, 2) }}</span></x-td>
            </tr>
        @endforeach

        {{-- Totals footer --}}
        <tr class="bg-cream/60 border-t-2 border-cream3">
            <x-td colspan="6" class="text-right font-mono text-[10px] uppercase tracking-[0.1em] text-ink4">Subtotal</x-td>
            <x-td><span class="font-mono font-medium">₱{{ number_format($transaction->subtotal, 2) }}</span></x-td>
        </tr>
        @if($transaction->discount_amount > 0)
        <tr class="bg-cream/60">
            <x-td colspan="6" class="text-right font-mono text-[10px] uppercase tracking-[0.1em] text-ok">Discount</x-td>
            <x-td><span class="font-mono text-ok">− ₱{{ number_format($transaction->discount_amount, 2) }}</span></x-td>
        </tr>
        @endif
        <tr class="bg-cream/60">
            <x-td colspan="6" class="text-right font-mono text-[10px] uppercase tracking-[0.1em] text-ink4">VAT (12%)</x-td>
            <x-td><span class="font-mono">₱{{ number_format($transaction->tax_amount, 2) }}</span></x-td>
        </tr>
        <tr class="bg-cream/60">
            <x-td colspan="6" class="text-right font-serif text-[14px] font-bold text-ink">Total Charged</x-td>
            <x-td><span class="font-serif text-[16px] font-bold text-terra">₱{{ number_format($transaction->total_amount, 2) }}</span></x-td>
        </tr>
    </x-data-table>

</x-app-layout>
