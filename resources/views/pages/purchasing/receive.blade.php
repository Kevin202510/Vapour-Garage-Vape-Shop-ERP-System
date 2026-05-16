<x-app-layout :title="'Receive — ' . $po->po_number">

    <x-page-header
        overline="Procurement · Receive Items"
        :title="'Receive: ' . $po->po_number"
        :subtitle="$po->supplier->company_name . ' → ' . $po->branch->name"
    >
        <x-slot:actions>
            <x-btn :href="route('purchasing.show', $po)" variant="ghost" size="sm">← Back to PO</x-btn>
        </x-slot:actions>
    </x-page-header>

    @if($errors->has('receive'))
        <x-alert type="error" :message="$errors->first('receive')" class="mb-5" />
    @endif

    <div class="flex items-start gap-3 px-4 py-3 bg-purple/5 border border-purple/20 rounded-lg mb-5">
        <x-icon name="info" class="w-4 h-4 text-purple flex-shrink-0 mt-0.5" />
        <div>
            <p class="text-[12.5px] text-ink font-medium mb-0.5">How to fill in this form</p>
            <p class="text-[12px] text-ink3 leading-relaxed">
                Enter how many units you are physically receiving <strong>right now</strong> for each line item.
                These quantities will be <strong>added</strong> to any previously received amounts.
                Quantities already received cannot be changed here.
                Inventory at <strong>{{ $po->branch->name }}</strong> will be updated immediately on submit.
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('purchasing.receive', $po) }}">
        @csrf

        <x-data-table :title="'Items on ' . $po->po_number">
            <x-slot:head>
                <x-th>SKU</x-th>
                <x-th>Product</x-th>
                <x-th>Unit Cost</x-th>
                <x-th>Ordered</x-th>
                <x-th>Previously Received</x-th>
                <x-th>Still Remaining</x-th>
                <x-th>Receive Now</x-th>
            </x-slot:head>

            @foreach($po->items as $i => $item)
                @php
                    $product   = $item->product;
                    $remaining = $item->remaining_quantity;
                @endphp
                <input type="hidden" name="items[{{ $i }}][product_id]" value="{{ $item->product_id }}">
                <tr class="hover:bg-cream transition-colors {{ $remaining === 0 ? 'opacity-60' : '' }}">
                    <x-td>
                        <span class="font-mono text-[11px] text-ink3">{{ $product->sku ?? '—' }}</span>
                    </x-td>
                    <x-td class="font-medium text-ink">
                        {{ $product->name ?? "(Deleted product #{$item->product_id})" }}
                    </x-td>
                    <x-td>
                        <span class="font-mono text-[12px]">₱{{ number_format($item->unit_cost, 2) }}</span>
                    </x-td>
                    <x-td>
                        <span class="font-mono">{{ $item->quantity_ordered }}</span>
                    </x-td>
                    <x-td>
                        <span class="font-mono text-ink4">{{ $item->quantity_received }}</span>
                    </x-td>
                    <x-td>
                        <span class="font-mono font-semibold {{ $remaining > 0 ? 'text-warn' : 'text-ok' }}">
                            {{ $remaining > 0 ? $remaining : '—' }}
                        </span>
                    </x-td>
                    <x-td>
                        @if($remaining > 0)
                            <input type="number"
                                   name="items[{{ $i }}][quantity_received]"
                                   min="0" max="{{ $remaining }}"
                                   value="{{ old("items.{$i}.quantity_received", $remaining) }}"
                                   class="erp-input w-28 text-center font-mono text-[13px]">
                        @else
                            <input type="hidden" name="items[{{ $i }}][quantity_received]" value="0">
                            <x-badge variant="ok">Fully received</x-badge>
                        @endif
                    </x-td>
                </tr>
            @endforeach

            {{-- Summary row --}}
            <tr class="border-t-2 border-cream3 bg-cream/40">
                <td colspan="3" class="px-4 py-3"></td>
                <x-td>
                    <span class="font-mono font-bold text-ink">{{ $po->items->sum('quantity_ordered') }}</span>
                </x-td>
                <x-td>
                    <span class="font-mono font-bold text-ink2">{{ $po->items->sum('quantity_received') }}</span>
                </x-td>
                <x-td>
                    <span class="font-mono font-bold text-warn">{{ $po->items->sum('remaining_quantity') }}</span>
                </x-td>
                <x-td></x-td>
            </tr>
        </x-data-table>

        <div class="flex items-center gap-3 mt-4">
            <x-btn type="submit" variant="primary">
                Confirm Receipt &amp; Update Inventory
            </x-btn>
            <x-btn :href="route('purchasing.show', $po)" variant="ghost">Cancel</x-btn>
        </div>
    </form>

</x-app-layout>
