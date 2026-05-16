<x-app-layout title="Products">

@php $isArchived = request('filter') === 'archived'; @endphp

    <x-page-header overline="Catalogue" title="Products"
                   :subtitle="$products->total() . ($isArchived ? ' archived products' : ' SKUs · ' . $lowStockCount . ' low stock alerts')">
        <x-slot:actions>
            @if(!$isArchived)
                @can('create products')
                <x-btn variant="primary" size="sm" :href="route('products.create')">
                    <x-icon name="plus" class="w-3 h-3" /> Add Product
                </x-btn>
                @endcan
            @endif
        </x-slot:actions>
    </x-page-header>

    {{-- Filters bar --}}
    <form method="GET" action="{{ route('products.index') }}" class="flex items-center gap-3 mb-5" x-data>
        <div class="flex gap-1 bg-cream border border-cream3 rounded-md p-[3px]">
            @foreach(['all' => 'All SKUs', 'low_stock' => 'Low Stock', 'archived' => 'Archived'] as $val => $lbl)
                <a href="{{ route('products.index', array_merge(request()->only(['search','category_id']), ['filter' => $val])) }}"
                   class="px-3.5 py-[5px] rounded text-[11px] font-mono tracking-wide transition-all
                          {{ request('filter', 'all') === $val ? 'bg-white text-ink shadow-sm' : 'text-ink4 hover:text-ink' }}">
                    {{ $lbl }}
                </a>
            @endforeach
        </div>
        <div class="flex items-center gap-2 bg-white border border-cream3 rounded-md px-3 py-[7px] flex-1 max-w-xs focus-within:border-purple transition-colors">
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
        @if(request()->hasAny(['search','category_id']))
            <a href="{{ route('products.index', ['filter' => request('filter', 'all')]) }}"
               class="text-[11px] font-mono text-ink4 hover:text-ink">Clear</a>
        @endif
        <x-btn type="submit" variant="ghost" size="sm">Filter</x-btn>
    </form>

    {{-- Archived view banner --}}
    @if($isArchived)
    <div class="flex items-center gap-3 px-4 py-3 bg-warn/5 border border-warn/20 rounded-lg mb-4">
        <x-icon name="alert-triangle" class="w-4 h-4 text-warn flex-shrink-0" />
        <p class="text-[12px] text-ink3 flex-1">
            Archived products are hidden from the shop, POS, and active listings. Restore them to make them active again.
        </p>
        <x-btn :href="route('products.index')" variant="ghost" size="sm">← Back to Active</x-btn>
    </div>
    @endif

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
            @php
                $margin = $product->selling_price > 0
                    ? round((($product->selling_price - $product->unit_cost) / $product->selling_price) * 100, 1)
                    : 0;
                $isLowStock = $product->is_active &&
                    $product->inventory->where('quantity_on_hand', '<=', $product->reorder_level)->isNotEmpty();
            @endphp
            <tr class="hover:bg-cream transition-colors {{ !$isArchived ? 'cursor-pointer' : '' }}"
                @if(!$isArchived) onclick="window.location='{{ route('products.show', $product) }}'" @endif>
                <x-td><span class="font-mono text-[11px] text-ink3">{{ $product->sku }}</span></x-td>
                <x-td class="font-semibold max-w-[200px]">
                    {{ $product->name }}
                    @if($isArchived)
                        <span class="ml-1 font-mono text-[9px] text-ink4">#{{ $product->id }}</span>
                    @endif
                </x-td>
                <x-td>
                    <span class="bg-cream2 text-ink4 font-mono text-[9px] px-2 py-0.5 rounded">
                        {{ $product->category->name ?? '—' }}
                    </span>
                </x-td>
                <x-td class="text-ink3">{{ $product->supplier->company_name ?? '—' }}</x-td>
                <x-td><span class="font-mono text-[12px]">₱{{ number_format($product->unit_cost, 0) }}</span></x-td>
                <x-td><span class="font-mono text-[12px] font-medium">₱{{ number_format($product->selling_price, 0) }}</span></x-td>
                <x-td><span class="font-mono text-[12px] text-ok">{{ $margin }}%</span></x-td>
                <x-td>
                    <x-badge variant="{{ $product->requires_age_verification ? 'warn' : 'muted' }}">
                        {{ $product->requires_age_verification ? '18+' : 'None' }}
                    </x-badge>
                </x-td>
                <x-td>
                    @if($isArchived)
                        <x-badge variant="muted">Archived</x-badge>
                    @elseif($isLowStock)
                        <x-badge variant="warn">Low Stock</x-badge>
                    @else
                        <x-badge variant="ok">Active</x-badge>
                    @endif
                </x-td>
                <x-td>
                    <div class="flex gap-2" @click.stop>
                        @if($isArchived)
                            {{-- Archived view: Restore + Permanently Delete --}}
                            @can('edit products')
                            <form method="POST" action="{{ route('products.restore', $product->id) }}">
                                @csrf
                                <x-btn type="submit" variant="ghost" size="sm">Restore</x-btn>
                            </form>
                            @endcan
                            @can('delete products')
                            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'perm-delete-{{ $product->id }}' }))"
                                    class="inline-flex items-center px-3 py-[5px] text-[11px] font-medium text-danger/70 border border-danger/20 rounded-md hover:bg-danger/5 transition-colors">
                                Delete Forever
                            </button>
                            @endcan
                        @else
                            {{-- Active view: Edit + Archive --}}
                            @can('edit products')
                            <x-btn :href="route('products.edit', $product)" variant="ghost" size="sm">Edit</x-btn>
                            @endcan
                            @can('delete products')
                            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'archive-{{ $product->id }}' }))"
                                    class="inline-flex items-center px-3 py-[5px] text-[11px] font-medium text-ink3 border border-cream3 rounded-md hover:border-warn/40 hover:text-warn transition-colors">
                                Archive
                            </button>
                            @endcan
                        @endif
                    </div>

                    {{-- Archive confirmation modal (active view) --}}
                    @if(!$isArchived)
                    @can('delete products')
                    <div x-data="{ open: false }"
                         x-on:open-modal.window="$event.detail === 'archive-{{ $product->id }}' && (open = true)"
                         x-on:close-modal.window="$event.detail === 'archive-{{ $product->id }}' && (open = false)"
                         x-show="open" x-cloak
                         class="fixed inset-0 z-50 flex items-center justify-center p-4"
                         style="background:rgba(28,24,48,0.6);backdrop-filter:blur(3px)">
                        <div class="bg-white rounded-xl border border-cream3 shadow-card w-full max-w-sm overflow-hidden" @click.stop>
                            <div class="px-6 py-4 border-b border-cream3">
                                <h3 class="font-display text-[13px] uppercase tracking-wide text-ink">Archive Product</h3>
                            </div>
                            <div class="px-6 py-4">
                                <p class="text-[13px] text-ink3">
                                    Archive <strong class="text-ink">{{ $product->name }}</strong>?
                                    It will be hidden from the shop, POS, and inventory but can be restored at any time.
                                </p>
                            </div>
                            <div class="px-6 py-3 border-t border-cream3 flex justify-end gap-3 bg-cream/50">
                                <x-btn type="button" variant="ghost" size="sm" @click="open = false">Cancel</x-btn>
                                <form method="POST" action="{{ route('products.archive', $product->id) }}">
                                    @csrf
                                    <x-btn type="submit" variant="danger" size="sm">Archive</x-btn>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endcan
                    @endif

                    {{-- Permanently Delete confirmation modal (archived view) --}}
                    @if($isArchived)
                    @can('delete products')
                    <div x-data="{ open: false }"
                         x-on:open-modal.window="$event.detail === 'perm-delete-{{ $product->id }}' && (open = true)"
                         x-on:close-modal.window="$event.detail === 'perm-delete-{{ $product->id }}' && (open = false)"
                         x-show="open" x-cloak
                         class="fixed inset-0 z-50 flex items-center justify-center p-4"
                         style="background:rgba(28,24,48,0.6);backdrop-filter:blur(3px)">
                        <div class="bg-white rounded-xl border border-cream3 shadow-card w-full max-w-sm overflow-hidden" @click.stop>
                            <div class="px-6 py-4 border-b border-danger/20 bg-danger/5">
                                <h3 class="font-display text-[13px] uppercase tracking-wide text-danger">Permanently Delete</h3>
                            </div>
                            <div class="px-6 py-4">
                                <p class="text-[13px] text-ink3 mb-2">
                                    Permanently delete <strong class="text-ink">{{ $product->name }}</strong>?
                                </p>
                                <p class="text-[11px] text-danger font-mono">This cannot be undone. The product record will be removed from the system.</p>
                            </div>
                            <div class="px-6 py-3 border-t border-cream3 flex justify-end gap-3 bg-cream/50">
                                <x-btn type="button" variant="ghost" size="sm" @click="open = false">Cancel</x-btn>
                                <form method="POST" action="{{ route('products.destroy', $product->id) }}">
                                    @csrf @method('DELETE')
                                    <x-btn type="submit" variant="danger" size="sm">Delete Forever</x-btn>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endcan
                    @endif
                </x-td>
            </tr>
        @empty
            <tr>
                <td colspan="10">
                    <x-empty-state
                        :title="$isArchived ? 'No archived products' : 'No products found'"
                        :description="$isArchived ? 'Archived products will appear here.' : 'Try adjusting your filters or add a new product.'"
                        icon="box">
                        @if(!$isArchived)
                        <x-slot:action>
                            <x-btn :href="route('products.create')" variant="primary" size="sm">+ Add Product</x-btn>
                        </x-slot:action>
                        @endif
                    </x-empty-state>
                </td>
            </tr>
        @endforelse

        <x-slot:pagination>{{ $products->withQueryString()->links('vendor.pagination.simple') }}</x-slot:pagination>
    </x-data-table>

</x-app-layout>
