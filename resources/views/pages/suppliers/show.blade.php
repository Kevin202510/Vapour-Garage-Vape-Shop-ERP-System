<x-app-layout :title="$supplier->company_name">

    <x-page-header
        overline="Procurement · Supplier"
        :title="$supplier->company_name"
        :subtitle="$supplier->contact_person ? 'Contact: ' . $supplier->contact_person : ($supplier->email ?? 'No contact info')"
    >
        <x-slot:actions>
            <x-btn :href="route('suppliers.index')" variant="ghost" size="sm">← All Suppliers</x-btn>
            @can('edit suppliers')
            <x-btn :href="route('suppliers.edit', $supplier)" variant="ghost" size="sm">Edit</x-btn>
            @endcan
            @if(!$supplier->is_active)
            <x-badge variant="muted">Inactive</x-badge>
            @endif
        </x-slot:actions>
    </x-page-header>

    @if(session('success'))
        <x-alert type="success" :message="session('success')" class="mb-5" />
    @endif

    {{-- KPI row --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <x-kpi-card label="Total POs"        :value="number_format($totalOrders)"       color="navy" />
        <x-kpi-card label="Total Purchased"  :value="'₱' . number_format($totalSpent)"  color="terra" />
        <x-kpi-card label="Products Supplied" :value="number_format($productCount)"      color="sage" />
    </div>

    <div class="grid grid-cols-2 gap-5 mb-5">

        {{-- Supplier profile --}}
        <x-card title="Supplier Details">
            <dl class="flex flex-col gap-3">
                @foreach([
                    'Company'         => $supplier->company_name,
                    'Contact Person'  => $supplier->contact_person ?? '—',
                    'Email'           => $supplier->email ?? '—',
                    'Phone'           => $supplier->phone ?? '—',
                    'Address'         => $supplier->address ?? '—',
                    'TIN'             => $supplier->tin ?? '—',
                    'Status'          => $supplier->is_active ? 'Active' : 'Inactive',
                    'Registered'      => $supplier->created_at->format('F d, Y'),
                ] as $label => $value)
                <div class="flex justify-between items-start border-b border-cream3 pb-2.5 last:border-0 last:pb-0">
                    <dt class="erp-label pt-0.5 flex-shrink-0">{{ $label }}</dt>
                    <dd class="text-[12.5px] text-ink2 text-right ml-4">
                        @if($label === 'Status')
                            <x-badge variant="{{ $supplier->is_active ? 'ok' : 'muted' }}">{{ $value }}</x-badge>
                        @elseif($label === 'Email' && $supplier->email)
                            <a href="mailto:{{ $supplier->email }}" class="text-purple hover:underline font-mono text-[11px]">{{ $value }}</a>
                        @else
                            {{ $value }}
                        @endif
                    </dd>
                </div>
                @endforeach
            </dl>
        </x-card>

        {{-- Products from this supplier --}}
        <x-card title="Products Supplied" :subtitle="$productCount . ' ACTIVE PRODUCTS'">
            @if($supplier->products->isEmpty())
                <x-empty-state title="No products yet" icon="box" />
            @else
            <div class="flex flex-col gap-2">
                @foreach($supplier->products->take(8) as $product)
                <div class="flex items-center justify-between px-3 py-2 bg-cream rounded-md border border-cream3">
                    <div class="min-w-0 flex-1 mr-3">
                        <p class="text-[12px] font-semibold text-ink truncate">{{ $product->name }}</p>
                        <p class="font-mono text-[9px] text-ink4 mt-0.5">{{ $product->sku }}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="font-mono text-[12px] font-semibold text-ok">₱{{ number_format($product->selling_price, 0) }}</p>
                        <p class="font-mono text-[9px] text-ink4">cost ₱{{ number_format($product->unit_cost, 0) }}</p>
                    </div>
                </div>
                @endforeach
                @if($productCount > 8)
                <p class="font-mono text-[9px] text-ink4 text-center pt-1">+ {{ $productCount - 8 }} more products</p>
                @endif
            </div>
            @endif
        </x-card>
    </div>

    {{-- Purchase order history --}}
    <x-data-table title="Purchase Order History">
        <x-slot:tableHeader>
            <span class="font-mono text-[11px] text-ink4">{{ $purchaseOrders->total() }} orders</span>
        </x-slot:tableHeader>
        <x-slot:head>
            <x-th>PO Number</x-th>
            <x-th>Branch</x-th>
            <x-th>Order Date</x-th>
            <x-th>Expected</x-th>
            <x-th>Total</x-th>
            <x-th>Status</x-th>
            <x-th></x-th>
        </x-slot:head>

        @forelse($purchaseOrders as $po)
        <tr class="hover:bg-cream transition-colors cursor-pointer"
            onclick="window.location='{{ route('purchasing.show', $po) }}'">
            <x-td><span class="font-mono text-[11px] text-purple">{{ $po->po_number }}</span></x-td>
            <x-td class="text-ink3">{{ $po->branch->name ?? '—' }}</x-td>
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
            <x-td><span class="font-mono font-semibold text-ink">₱{{ number_format($po->total_amount, 2) }}</span></x-td>
            <x-td>
                <x-badge variant="{{ match($po->status) {
                    'received' => 'ok',
                    'approved','sent' => 'navy',
                    'partial' => 'gold',
                    'draft' => 'muted',
                    'cancelled' => 'error',
                    default => 'muted'
                } }}">
                    {{ ucfirst($po->status) }}
                </x-badge>
            </x-td>
            <x-td>
                <x-btn :href="route('purchasing.show', $po)" variant="ghost" size="sm" @click.stop>View</x-btn>
            </x-td>
        </tr>
        @empty
            <tr><td colspan="7"><x-empty-state title="No purchase orders yet" icon="shopping-bag" /></td></tr>
        @endforelse

        <x-slot:pagination>{{ $purchaseOrders->links('vendor.pagination.simple') }}</x-slot:pagination>
    </x-data-table>

</x-app-layout>
