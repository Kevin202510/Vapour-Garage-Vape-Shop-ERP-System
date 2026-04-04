<x-app-layout :title="$product->name">

    <x-page-header
        overline="Catalogue · Product"
        :title="$product->name"
        :subtitle="'SKU: ' . $product->sku"
    >
        <x-slot:actions>
            <x-btn :href="route('products.index')" variant="ghost" size="sm">← All Products</x-btn>
            <x-btn :href="route('products.edit', $product)" variant="primary" size="sm">Edit Product</x-btn>
        </x-slot:actions>
    </x-page-header>

    @php
        $margin = $product->selling_price > 0
            ? round((($product->selling_price - $product->unit_cost) / $product->selling_price) * 100, 1)
            : 0;
        $totalStock = $product->inventory->sum('quantity_on_hand');
    @endphp

    <div class="grid grid-cols-4 gap-4 mb-6">
        <x-kpi-card label="Selling Price"  :value="'₱' . number_format($product->selling_price, 2)" color="terra" />
        <x-kpi-card label="Unit Cost"      :value="'₱' . number_format($product->unit_cost, 2)"     color="navy" />
        <x-kpi-card label="Gross Margin"   :value="$margin . '%'"                                    color="sage" />
        <x-kpi-card label="Total Stock"    :value="number_format($totalStock) . ' ' . $product->unit_of_measure" color="gold" />
    </div>

    <div class="grid grid-cols-2 gap-5 mb-5">

        {{-- Product info --}}
        <x-card title="Product Details">
            <dl class="flex flex-col gap-4">
                @foreach([
                    'SKU'              => $product->sku,
                    'Name'             => $product->name,
                    'Category'         => $product->category->name ?? '—',
                    'Supplier'         => $product->supplier->company_name ?? '—',
                    'Unit of Measure'  => strtoupper($product->unit_of_measure),
                    'Tax Rate'         => $product->tax_rate . '%',
                    'Reorder Level'    => $product->reorder_level . ' ' . $product->unit_of_measure,
                    'Age Verification' => $product->requires_age_verification ? 'Required (18+)' : 'Not required',
                    'Status'           => $product->is_active ? 'Active' : 'Archived',
                ] as $label => $value)
                    <div class="flex justify-between items-start border-b border-cream2 pb-3 last:border-0 last:pb-0">
                        <dt class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 pt-0.5 flex-shrink-0">{{ $label }}</dt>
                        <dd class="text-[12.5px] text-ink2 text-right ml-4">
                            @if($label === 'Status')
                                <x-badge variant="{{ $product->is_active ? 'ok' : 'muted' }}">{{ $value }}</x-badge>
                            @elseif($label === 'Age Verification')
                                <x-badge variant="{{ $product->requires_age_verification ? 'warn' : 'muted' }}">{{ $value }}</x-badge>
                            @else
                                {{ $value }}
                            @endif
                        </dd>
                    </div>
                @endforeach
            </dl>
            @if($product->description)
                <div class="mt-4 pt-4 border-t border-cream2">
                    <p class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 mb-2">Description</p>
                    <p class="text-[12.5px] text-ink3 leading-relaxed">{{ $product->description }}</p>
                </div>
            @endif
        </x-card>

        {{-- Pricing breakdown --}}
        <x-card title="Pricing Breakdown">
            <div class="flex flex-col gap-3 mb-5">
                @foreach([
                    ['Unit Cost',           '₱' . number_format($product->unit_cost, 2),                                       'text-ink3'],
                    ['VAT (' . $product->tax_rate . '%)', '₱' . number_format($product->selling_price * ($product->tax_rate / 100), 2), 'text-ink3'],
                    ['Selling Price',        '₱' . number_format($product->selling_price, 2),                                  'font-bold text-ink'],
                    ['Gross Profit / Unit', '₱' . number_format($product->selling_price - $product->unit_cost, 2),             'text-ok'],
                    ['Margin',              $margin . '%',                                                                      'text-ok font-bold'],
                ] as [$lbl, $val, $cls])
                    <div class="flex justify-between items-center border-b border-cream2 pb-3 last:border-0 last:pb-0">
                        <span class="font-mono text-[10px] uppercase tracking-[0.1em] text-ink4">{{ $lbl }}</span>
                        <span class="font-mono text-[13px] {{ $cls }}">{{ $val }}</span>
                    </div>
                @endforeach
            </div>

            {{-- Price with tax --}}
            <div class="bg-cream rounded-lg p-4 text-center">
                <p class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 mb-1">VAT-Inclusive Price</p>
                <p class="font-serif text-[28px] font-black text-terra">
                    ₱{{ number_format($product->selling_price * (1 + $product->tax_rate / 100), 2) }}
                </p>
            </div>
        </x-card>
    </div>

    {{-- Inventory by branch --}}
    <x-data-table title="Stock by Branch" class="mb-5">
        <x-slot:head>
            <x-th>Branch</x-th>
            <x-th>On Hand</x-th>
            <x-th>Reserved</x-th>
            <x-th>Available</x-th>
            <x-th>Reorder Level</x-th>
            <x-th>Stock Value</x-th>
            <x-th>Status</x-th>
        </x-slot:head>
        @forelse($product->inventory as $inv)
            @php $available = $inv->quantity_on_hand - $inv->quantity_reserved; @endphp
            <tr class="hover:bg-cream transition-colors">
                <x-td class="font-medium">{{ $inv->branch->name ?? '—' }}</x-td>
                <x-td><span class="font-mono">{{ $inv->quantity_on_hand }}</span></x-td>
                <x-td><span class="font-mono text-ink4">{{ $inv->quantity_reserved }}</span></x-td>
                <x-td><span class="font-mono font-medium">{{ $available }}</span></x-td>
                <x-td><span class="font-mono text-ink4">{{ $product->reorder_level }}</span></x-td>
                <x-td><span class="font-mono text-[12px]">₱{{ number_format($available * $product->unit_cost, 2) }}</span></x-td>
                <x-td>
                    @if($available <= 0)
                        <x-badge variant="error">Critical</x-badge>
                    @elseif($available <= $product->reorder_level)
                        <x-badge variant="warn">Low</x-badge>
                    @else
                        <x-badge variant="ok">OK</x-badge>
                    @endif
                </x-td>
            </tr>
        @empty
            <tr><td colspan="7"><x-empty-state title="No inventory records" icon="box" /></td></tr>
        @endforelse
    </x-data-table>

    {{-- Delete zone --}}
    @can('delete products')
    <div class="border border-danger/20 rounded-lg p-5 bg-danger/[0.03]">
        <p class="font-serif text-[14px] font-bold text-ink mb-1">Danger Zone</p>
        <p class="text-[12px] text-ink4 mb-3">Deleting this product will soft-delete it and hide it from POS. Inventory records are preserved.</p>
        <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'delete-product' }))"
                class="inline-flex items-center px-4 py-2 text-[12px] font-medium text-danger border border-danger/30 rounded-md hover:bg-danger/5 transition-colors">
            Delete Product
        </button>
    </div>
    <x-confirm-delete name="delete-product"
                      :action="route('products.destroy', $product)"
                      :item-name="$product->name" />
    @endcan

</x-app-layout>
