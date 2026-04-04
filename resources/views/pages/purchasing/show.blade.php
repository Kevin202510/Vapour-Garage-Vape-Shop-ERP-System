<x-app-layout :title="$po->po_number">

    <x-page-header
        overline="Procurement · Purchase Order"
        :title="$po->po_number"
        :subtitle="$po->supplier->company_name . ' → ' . $po->branch->name"
    >
        <x-slot:actions>
            <x-btn :href="route('purchasing.index')" variant="ghost" size="sm">← All POs</x-btn>
            @if($po->status === 'draft')
                @can('approve purchase orders')
                    <form method="POST" action="{{ route('purchasing.approve', $po) }}" class="inline">
                        @csrf @method('POST')
                        <x-btn type="submit" variant="primary" size="sm">Approve PO</x-btn>
                    </form>
                @endcan
                @can('cancel purchase orders')
                <form method="POST" action="{{ route('purchasing.cancel', $po) }}" class="inline">
                    @csrf @method('POST')
                    <button type="submit" class="inline-flex items-center px-3 py-[5px] text-[11px] font-medium text-danger/70 border border-danger/20 rounded-md hover:bg-danger/5 transition-colors">
                        Cancel PO
                    </button>
                </form>
                @endcan
            @elseif(in_array($po->status, ['approved','sent','partial']))
                @can('receive purchase orders')
                    <x-btn :href="route('purchasing.receive-form', $po)" variant="primary" size="sm">Receive Items</x-btn>
                @endcan
            @endif
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-4 gap-4 mb-6">
        <x-kpi-card label="Total Order Value"   :value="'₱' . number_format($po->total_amount, 2)"                            color="terra" />
        <x-kpi-card label="Line Items"          :value="$po->items->count()"                                                  color="navy" />
        <x-kpi-card label="Units Ordered"       :value="number_format($po->items->sum('quantity_ordered'))"                   color="gold" />
        <x-kpi-card label="Units Received"      :value="number_format($po->items->sum('quantity_received'))"                  color="sage" />
    </div>

    <div class="grid grid-cols-2 gap-5 mb-5">
        <x-card title="Order Details">
            <dl class="flex flex-col gap-4">
                @foreach([
                    'PO Number'     => $po->po_number,
                    'Supplier'      => $po->supplier->company_name,
                    'Branch'        => $po->branch->name,
                    'Created By'    => optional($po->creator)->full_name ?? '—',
                    'Approved By'   => optional($po->approver)->full_name ?? '—',
                    'Order Date'    => \Carbon\Carbon::parse($po->order_date)->format('F d, Y'),
                    'Expected Date' => $po->expected_date ? \Carbon\Carbon::parse($po->expected_date)->format('F d, Y') : '—',
                    'Received Date' => $po->received_date ? \Carbon\Carbon::parse($po->received_date)->format('F d, Y') : '—',
                    'Status'        => ucfirst($po->status),
                ] as $label => $value)
                    <div class="flex justify-between items-start border-b border-cream2 pb-3 last:border-0 last:pb-0">
                        <dt class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 pt-0.5 flex-shrink-0">{{ $label }}</dt>
                        <dd class="text-[12.5px] text-ink2 text-right ml-4">
                            @if($label === 'Status')
                                <x-badge variant="{{ match($po->status) { 'received' => 'ok', 'approved','sent' => 'navy', 'partial' => 'terra', 'draft' => 'muted', 'cancelled' => 'error', default => 'muted' } }}">
                                    {{ $value }}
                                </x-badge>
                            @else
                                {{ $value }}
                            @endif
                        </dd>
                    </div>
                @endforeach
            </dl>
            @if($po->notes)
                <div class="mt-4 pt-4 border-t border-cream2">
                    <p class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 mb-1">Notes</p>
                    <p class="text-[12.5px] text-ink3">{{ $po->notes }}</p>
                </div>
            @endif
        </x-card>

        <x-card title="Supplier Details">
            <dl class="flex flex-col gap-4">
                @foreach([
                    'Company'        => $po->supplier->company_name,
                    'Contact Person' => $po->supplier->contact_person ?? '—',
                    'Email'          => $po->supplier->email ?? '—',
                    'Phone'          => $po->supplier->phone ?? '—',
                    'TIN'            => $po->supplier->tin ?? '—',
                ] as $label => $value)
                    <div class="flex justify-between border-b border-cream2 pb-3 last:border-0 last:pb-0">
                        <dt class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4">{{ $label }}</dt>
                        <dd class="text-[12.5px] text-ink2">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-card>
    </div>

    <x-data-table title="Ordered Items">
        <x-slot:head>
            <x-th>SKU</x-th>
            <x-th>Product</x-th>
            <x-th>Unit Cost</x-th>
            <x-th>Qty Ordered</x-th>
            <x-th>Qty Received</x-th>
            <x-th>Remaining</x-th>
            <x-th>Subtotal</x-th>
            <x-th>Status</x-th>
        </x-slot:head>
        @foreach($po->items as $item)
            @php $remaining = max(0, $item->quantity_ordered - $item->quantity_received); @endphp
            <tr class="hover:bg-cream transition-colors">
                <x-td><span class="font-mono text-[11px] text-ink3">{{ $item->product->sku ?? '—' }}</span></x-td>
                <x-td class="font-medium">{{ $item->product->name ?? '—' }}</x-td>
                <x-td><span class="font-mono">₱{{ number_format($item->unit_cost, 2) }}</span></x-td>
                <x-td><span class="font-mono font-medium">{{ $item->quantity_ordered }}</span></x-td>
                <x-td><span class="font-mono {{ $item->quantity_received > 0 ? 'text-ok' : 'text-ink4' }}">{{ $item->quantity_received }}</span></x-td>
                <x-td><span class="font-mono {{ $remaining > 0 ? 'text-warn' : 'text-ok' }}">{{ $remaining }}</span></x-td>
                <x-td><span class="font-mono">₱{{ number_format($item->subtotal, 2) }}</span></x-td>
                <x-td>
                    @if($item->quantity_received >= $item->quantity_ordered)
                        <x-badge variant="ok">Fully Received</x-badge>
                    @elseif($item->quantity_received > 0)
                        <x-badge variant="terra">Partial</x-badge>
                    @else
                        <x-badge variant="muted">Pending</x-badge>
                    @endif
                </x-td>
            </tr>
        @endforeach
        <tr class="bg-cream/60 border-t-2 border-cream3">
            <x-td colspan="6" class="text-right font-serif text-[14px] font-bold text-ink">Order Total</x-td>
            <x-td colspan="2"><span class="font-serif text-[16px] font-bold text-terra">₱{{ number_format($po->total_amount, 2) }}</span></x-td>
        </tr>
    </x-data-table>

</x-app-layout>
