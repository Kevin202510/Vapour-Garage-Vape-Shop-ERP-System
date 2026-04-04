<x-app-layout :title="'Receive — ' . $po->po_number">

    <x-page-header
        overline="Procurement · Receive Items"
        :title="'Receive: ' . $po->po_number"
        :subtitle="$po->supplier->company_name . ' · ' . $po->branch->name"
    >
        <x-slot:actions>
            <x-btn :href="route('purchasing.show', $po)" variant="ghost" size="sm">← Back to PO</x-btn>
        </x-slot:actions>
    </x-page-header>

    <div class="bg-cream border border-warn/30 rounded-lg px-4 py-3 mb-5 flex items-center gap-3">
        <x-icon name="info" class="w-4 h-4 text-warn flex-shrink-0" />
        <p class="text-[12.5px] text-ink3">
            Receiving items will automatically add the quantities to
            <strong>{{ $po->branch->name }}</strong> branch inventory.
            Enter only the quantities physically received today.
        </p>
    </div>

    <form method="POST" action="{{ route('purchasing.receive', $po) }}">
        @csrf

        <x-data-table title="Items to Receive">
            <x-slot:head>
                <x-th>SKU</x-th>
                <x-th>Product</x-th>
                <x-th>Qty Ordered</x-th>
                <x-th>Previously Received</x-th>
                <x-th>Remaining</x-th>
                <x-th>Receive Now</x-th>
            </x-slot:head>

            @foreach($po->items as $i => $item)
                @php $remaining = max(0, $item->quantity_ordered - $item->quantity_received); @endphp
                <input type="hidden" name="items[{{ $i }}][product_id]" value="{{ $item->product_id }}">
                <tr class="hover:bg-cream transition-colors">
                    <x-td><span class="font-mono text-[11px] text-ink3">{{ $item->product->sku ?? '—' }}</span></x-td>
                    <x-td class="font-medium">{{ $item->product->name ?? '—' }}</x-td>
                    <x-td><span class="font-mono">{{ $item->quantity_ordered }}</span></x-td>
                    <x-td><span class="font-mono text-ink4">{{ $item->quantity_received }}</span></x-td>
                    <x-td>
                        <span class="font-mono {{ $remaining > 0 ? 'text-warn font-medium' : 'text-ok' }}">
                            {{ $remaining }}
                        </span>
                    </x-td>
                    <x-td>
                        @if($remaining > 0)
                            <input type="number"
                                   name="items[{{ $i }}][quantity_received]"
                                   min="0" max="{{ $remaining }}"
                                   value="{{ $remaining }}"
                                   class="w-24 bg-cream border border-cream3 rounded-md px-3 py-1.5 text-[12px] font-mono text-ink
                                          outline-none focus:border-terra transition-colors">
                        @else
                            <input type="hidden" name="items[{{ $i }}][quantity_received]" value="0">
                            <span class="font-mono text-[11px] text-ok">Fully received</span>
                        @endif
                    </x-td>
                </tr>
            @endforeach
        </x-data-table>

        <div class="flex items-center gap-3 mt-2">
            <x-btn type="submit" variant="primary">Confirm Receipt & Update Inventory</x-btn>
            <x-btn :href="route('purchasing.show', $po)" variant="ghost">Cancel</x-btn>
        </div>
    </form>

</x-app-layout>
