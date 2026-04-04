<x-app-layout title="Purchasing">

    <x-page-header overline="Procurement" title="Purchase Orders"
                   :subtitle="$suppliers->count() . ' suppliers · ' . $activePoCount . ' active POs'">
        <x-slot:actions>
            @can('create purchase orders')
            <x-btn variant="primary" size="sm" :href="route('purchasing.create')">
                <x-icon name="plus" class="w-3 h-3" /> New PO
            </x-btn>
            @endcan
        </x-slot:actions>
    </x-page-header>

    {{-- Status tabs --}}
    <div class="flex gap-1 bg-cream border border-cream3 rounded-md p-[3px] mb-5 w-fit">
        @foreach(['all' => 'All', 'draft' => 'Draft', 'approved' => 'Approved', 'partial' => 'Partial', 'received' => 'Received', 'cancelled' => 'Cancelled'] as $val => $lbl)
            <a href="{{ route('purchasing.index', array_merge(request()->query(), ['status' => $val])) }}"
               class="px-3.5 py-[5px] rounded text-[11px] font-mono tracking-wide transition-all
                      {{ request('status', 'all') === $val ? 'bg-white text-ink shadow-sm' : 'text-ink4 hover:text-ink' }}">
                {{ $lbl }}
            </a>
        @endforeach
    </div>

    <x-data-table title="Purchase Orders">
        <x-slot:tableHeader>
            <span class="font-mono text-[11px] text-ink4">{{ $purchaseOrders->total() }} orders</span>
        </x-slot:tableHeader>
        <x-slot:head>
            <x-th>PO Number</x-th>
            <x-th>Supplier</x-th>
            <x-th>Branch</x-th>
            <x-th>Line Items</x-th>
            <x-th>Total</x-th>
            <x-th>Order Date</x-th>
            <x-th>Expected</x-th>
            <x-th>Status</x-th>
            <x-th></x-th>
        </x-slot:head>

        @forelse($purchaseOrders as $po)
            <tr class="hover:bg-cream transition-colors cursor-pointer"
                onclick="window.location='{{ route('purchasing.show', $po) }}'">
                <x-td><span class="font-mono text-[11px] text-ink3">{{ $po->po_number }}</span></x-td>
                <x-td class="font-medium">{{ $po->supplier->company_name ?? '—' }}</x-td>
                <x-td class="text-ink3">{{ $po->branch->name ?? '—' }}</x-td>
                <x-td><span class="font-mono">{{ $po->items->count() }}</span></x-td>
                <x-td><span class="font-mono font-medium">₱{{ number_format($po->total_amount, 2) }}</span></x-td>
                <x-td><span class="font-mono text-[10px] text-ink4">{{ \Carbon\Carbon::parse($po->order_date)->format('M d, Y') }}</span></x-td>
                <x-td>
                    @if($po->expected_date)
                        <span class="font-mono text-[10px] {{ \Carbon\Carbon::parse($po->expected_date)->isPast() && $po->status !== 'received' ? 'text-danger' : 'text-ink4' }}">
                            {{ \Carbon\Carbon::parse($po->expected_date)->format('M d, Y') }}
                        </span>
                    @else
                        <span class="text-ink4">—</span>
                    @endif
                </x-td>
                <x-td>
                    <x-badge variant="{{ match($po->status) { 'received' => 'ok', 'approved','sent' => 'navy', 'partial' => 'terra', 'draft' => 'muted', 'cancelled' => 'error', default => 'muted' } }}">
                        {{ ucfirst($po->status) }}
                    </x-badge>
                </x-td>
                <x-td>
                    <div class="flex gap-2" @click.stop>
                        @if($po->status === 'draft' && auth()->user()->can('approve purchase orders'))
                            <form method="POST" action="{{ route('purchasing.approve', $po) }}">
                                @csrf @method('POST')
                                <x-btn type="submit" variant="primary" size="sm">Approve</x-btn>
                            </form>
                        @elseif(in_array($po->status, ['approved','sent','partial']) && auth()->user()->can('receive purchase orders'))
                            <x-btn :href="route('purchasing.receive-form', $po)" variant="primary" size="sm">Receive</x-btn>
                        @else
                            <x-btn :href="route('purchasing.show', $po)" variant="ghost" size="sm">View</x-btn>
                        @endif
                    </div>
                </x-td>
            </tr>
        @empty
            <tr><td colspan="9">
                <x-empty-state title="No purchase orders found" icon="shopping-bag">
                    <x-slot:action><x-btn :href="route('purchasing.create')" variant="primary" size="sm">+ New PO</x-btn></x-slot:action>
                </x-empty-state>
            </td></tr>
        @endforelse

        <x-slot:pagination>{{ $purchaseOrders->withQueryString()->links('vendor.pagination.simple') }}</x-slot:pagination>
    </x-data-table>

</x-app-layout>
