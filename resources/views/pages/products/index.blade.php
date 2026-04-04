<x-app-layout title="Products">

    <x-page-header overline="Catalogue" title="Products" :subtitle="$products->total() . ' SKUs · ' . $lowStockCount . ' low stock alerts'">
        <x-slot:actions>
            @can('export reports')
            <x-btn variant="ghost" size="sm">
                <x-icon name="download" class="w-3 h-3" /> Import CSV
            </x-btn>
            @endcan
            @can('create products')
            <x-btn variant="primary" size="sm" :href="route('products.create')">
                <x-icon name="plus" class="w-3 h-3" /> Add Product
            </x-btn>
            @endcan
        </x-slot:actions>
    </x-page-header>

    {{-- Filters bar --}}
    <form method="GET" action="{{ route('products.index') }}"
          class="flex items-center gap-3 mb-5" x-data>
        <div class="flex gap-1 bg-cream border border-cream3 rounded-md p-[3px]">
            @foreach(['all' => 'All SKUs', 'low_stock' => 'Low Stock', 'archived' => 'Archived'] as $val => $lbl)
                <a href="{{ route('products.index', array_merge(request()->query(), ['filter' => $val])) }}"
                   class="px-3.5 py-[5px] rounded text-[11px] font-mono tracking-wide transition-all
                          {{ request('filter', 'all') === $val ? 'bg-white text-ink shadow-sm' : 'text-ink4 hover:text-ink' }}">
                    {{ $lbl }}
                </a>
            @endforeach
        </div>
        <div class="flex items-center gap-2 bg-white border border-cream3 rounded-md px-3 py-[7px] flex-1 max-w-xs focus-within:border-terra transition-colors">
            <x-icon name="filter" class="w-3.5 h-3.5 text-ink4 flex-shrink-0" />
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search name or SKU…"
                   class="bg-transparent border-none outline-none text-[12px] font-sans text-ink placeholder-ink4 w-full">
        </div>
        <x-form-select name="category_id" label="" placeholder="All Categories" class="min-w-[150px]">
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
            @endforeach
        </x-form-select>
        <x-btn type="submit" variant="ghost" size="sm">Filter</x-btn>
    </form>

    <x-data-table>
        <x-slot:head>
            <x-th>SKU</x-th>
            <x-th>Product Name</x-th>
            <x-th>Category</x-th>
            <x-th>Supplier</x-th>
            <x-th>Cost</x-th>
            <x-th>Price</x-th>
            <x-th>Margin</x-th>
            <x-th>Age Ver.</x-th>
            <x-th>Status</x-th>
            <x-th></x-th>
        </x-slot:head>

        @forelse($products as $product)
            @php $margin = $product->selling_price > 0
                ? round((($product->selling_price - $product->unit_cost) / $product->selling_price) * 100, 1)
                : 0; @endphp
            <tr class="hover:bg-cream transition-colors cursor-pointer"
                onclick="window.location='{{ route('products.show', $product) }}'">
                <x-td><span class="font-mono text-[11px] text-ink3">{{ $product->sku }}</span></x-td>
                <x-td class="font-semibold max-w-[200px]">{{ $product->name }}</x-td>
                <x-td><span class="bg-cream2 text-ink4 font-mono text-[9px] px-2 py-0.5 rounded">{{ $product->category->name ?? '—' }}</span></x-td>
                <x-td class="text-ink3">{{ $product->supplier->company_name ?? '—' }}</x-td>
                <x-td><span class="font-mono text-[12px]">₱{{ number_format($product->unit_cost, 0) }}</span></x-td>
                <x-td><span class="font-mono text-[12px] font-medium">₱{{ number_format($product->selling_price, 0) }}</span></x-td>
                <x-td><span class="font-mono text-[12px] text-ok">{{ $margin }}%</span></x-td>
                <x-td>
                    <x-badge variant="{{ $product->requires_age_verification ? 'warn' : 'muted' }}">
                        {{ $product->requires_age_verification ? 'Required' : 'None' }}
                    </x-badge>
                </x-td>
                <x-td>
                    @if(!$product->is_active)
                        <x-badge variant="muted">Archived</x-badge>
                    @elseif($product->inventory->where('quantity_on_hand', '<=', $product->reorder_level)->count())
                        <x-badge variant="error">Low Stock</x-badge>
                    @else
                        <x-badge variant="ok">Active</x-badge>
                    @endif
                </x-td>
                <x-td>
                    <div class="flex gap-2" @click.stop>
                        @can('edit products')
                        <x-btn :href="route('products.edit', $product)" variant="ghost" size="sm">Edit</x-btn>
                        @endcan
                        @can('delete products')
                        <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'delete-product-{{ $product->id }}' }))"
                                class="inline-flex items-center px-3 py-[5px] text-[11px] font-medium text-danger/70 border border-danger/20 rounded-md hover:bg-danger/5 transition-colors">
                            Delete
                        </button>
                        @endcan
                    </div>
                    @can('delete products')
                    <x-confirm-delete :name="'delete-product-' . $product->id"
                                      :action="route('products.destroy', $product)"
                                      :item-name="$product->name" />
                    @endcan
                </x-td>
            </tr>
        @empty
            <tr><td colspan="10"><x-empty-state title="No products found" description="Try adjusting your filters or add a new product." icon="box">
                <x-slot:action><x-btn :href="route('products.create')" variant="primary" size="sm">+ Add Product</x-btn></x-slot:action>
            </x-empty-state></td></tr>
        @endforelse

        <x-slot:pagination>{{ $products->withQueryString()->links('vendor.pagination.simple') }}</x-slot:pagination>
    </x-data-table>

</x-app-layout>
