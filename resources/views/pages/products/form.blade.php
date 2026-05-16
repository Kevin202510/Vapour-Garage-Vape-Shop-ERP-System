<x-app-layout :title="isset($product) ? 'Edit Product' : 'New Product'">

    <x-page-header
        overline="Catalogue"
        :title="isset($product) ? 'Edit Product' : 'New Product'"
        :subtitle="isset($product) ? 'SKU: ' . $product->sku : 'Add a new product to your catalogue'"
    >
        <x-slot:actions>
            <x-btn :href="route('products.index')" variant="ghost" size="sm">← Back</x-btn>
        </x-slot:actions>
    </x-page-header>

    <form method="POST"
          action="{{ isset($product) ? route('products.update', $product) : route('products.store') }}"
          class="max-w-3xl">
        @csrf
        @if(isset($product)) @method('PUT') @endif

        <div class="grid grid-cols-2 gap-6 mb-6">
            <x-form-input label="SKU" name="sku" :value="old('sku', $product->sku ?? '')" required placeholder="e.g. DIS-001" />
            <x-form-input label="Product Name" name="name" :value="old('name', $product->name ?? '')" required />
        </div>

        <div class="grid grid-cols-2 gap-6 mb-6">
            <x-form-select label="Category" name="category_id" required>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </x-form-select>
            <x-form-select label="Supplier" name="supplier_id">
                @foreach($suppliers as $sup)
                    <option value="{{ $sup->id }}" {{ old('supplier_id', $product->supplier_id ?? '') == $sup->id ? 'selected' : '' }}>
                        {{ $sup->company_name }}
                    </option>
                @endforeach
            </x-form-select>
        </div>

        <div class="grid grid-cols-3 gap-6 mb-6">
            <x-form-input label="Unit Cost (₱)" name="unit_cost" type="number" step="0.01" min="0"
                          :value="old('unit_cost', $product->unit_cost ?? '')" required />
            <x-form-input label="Selling Price (₱)" name="selling_price" type="number" step="0.01" min="0"
                          :value="old('selling_price', $product->selling_price ?? '')" required />
            <x-form-input label="Tax Rate (%)" name="tax_rate" type="number" step="0.01" min="0" max="100"
                          :value="old('tax_rate', $product->tax_rate ?? '12')" />
        </div>

        <div class="grid grid-cols-3 gap-6 mb-6">
            <x-form-input label="Reorder Level" name="reorder_level" type="number" min="0"
                          :value="old('reorder_level', $product->reorder_level ?? '10')" />
            <x-form-select label="Unit of Measure" name="unit_of_measure">
                @foreach(['pcs' => 'Pieces', 'bottle' => 'Bottle', 'pack' => 'Pack', 'spool' => 'Spool', 'box' => 'Box'] as $val => $lbl)
                    <option value="{{ $val }}" {{ old('unit_of_measure', $product->unit_of_measure ?? 'pcs') === $val ? 'selected' : '' }}>
                        {{ $lbl }}
                    </option>
                @endforeach
            </x-form-select>
        </div>

        <div class="flex flex-wrap items-center gap-6 mb-6">
            <label class="flex items-center gap-2.5 cursor-pointer">
                <input type="checkbox" name="requires_age_verification" value="1"
                       {{ old('requires_age_verification', $product->requires_age_verification ?? true) ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-cream3 text-terra focus:ring-terra/30">
                <span class="text-[13px] text-ink2">Requires age verification (18+)</span>
            </label>
            <label class="flex items-center gap-2.5 cursor-pointer">
                <input type="checkbox" name="is_active" value="1"
                       {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-cream3 text-terra focus:ring-terra/30">
                <span class="text-[13px] text-ink2">Active (visible in POS &amp; shop)</span>
            </label>
            <label class="flex items-center gap-2.5 cursor-pointer">
                <input type="checkbox" name="is_new_arrival" value="1"
                       {{ old('is_new_arrival', $product->is_new_arrival ?? false) ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-cream3 text-terra focus:ring-terra/30">
                <span class="text-[13px] text-ink2">New Arrival</span>
            </label>
            <label class="flex items-center gap-2.5 cursor-pointer">
                <input type="checkbox" name="is_best_seller" value="1"
                       {{ old('is_best_seller', $product->is_best_seller ?? false) ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-cream3 text-terra focus:ring-terra/30">
                <span class="text-[13px] text-ink2">Best Seller</span>
            </label>
            <label class="flex items-center gap-2.5 cursor-pointer">
                <input type="checkbox" name="is_hot" value="1"
                       {{ old('is_hot', $product->is_hot ?? false) ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-cream3 text-terra focus:ring-terra/30">
                <span class="text-[13px] text-ink2">Hot / Trending</span>
            </label>
        </div>

        {{-- Sale pricing --}}
        <div class="bg-warn/5 border border-warn/20 rounded-lg p-5 mb-6" x-data="{ onSale: {{ old('is_on_sale', $product->is_on_sale ?? false) ? 'true' : 'false' }} }">
            <label class="flex items-center gap-2.5 cursor-pointer mb-4">
                <input type="checkbox" name="is_on_sale" value="1" x-model="onSale"
                       {{ old('is_on_sale', $product->is_on_sale ?? false) ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-warn/40 text-warn focus:ring-warn/20">
                <span class="text-[13px] font-semibold text-ink">On Sale</span>
                <span class="font-mono text-[9px] text-ink4 uppercase tracking-widest">— show discounted price on shop</span>
            </label>
            <div x-show="onSale" x-cloak>
                <x-form-input label="Sale Price (₱)" name="sale_price" type="number" step="0.01" min="0"
                              :value="old('sale_price', $product->sale_price ?? '')"
                              placeholder="Discounted selling price" />
            </div>
        </div>

        {{-- Wholesale --}}
        <div class="bg-purple/[0.04] border border-purple/20 rounded-lg p-5 mb-6" x-data="{ wholesale: {{ old('is_wholesale', $product->is_wholesale ?? false) ? 'true' : 'false' }} }">
            <label class="flex items-center gap-2.5 cursor-pointer mb-4">
                <input type="checkbox" name="is_wholesale" value="1" x-model="wholesale"
                       {{ old('is_wholesale', $product->is_wholesale ?? false) ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-purple/40 text-purple focus:ring-purple/20">
                <span class="text-[13px] font-semibold text-ink">Wholesale / Bulk</span>
                <span class="font-mono text-[9px] text-ink4 uppercase tracking-widest">— show in wholesale section</span>
            </label>
            <div x-show="wholesale" x-cloak>
                <x-form-input label="Minimum Order Quantity" name="min_order_qty" type="number" min="1"
                              :value="old('min_order_qty', $product->min_order_qty ?? '')"
                              placeholder="e.g. 10 pieces minimum" />
            </div>
        </div>

        <div class="mb-8">
            <label class="block font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 mb-1.5">Description</label>
            <textarea name="description" rows="3"
                      class="w-full bg-cream border border-cream3 rounded-md px-3 py-2 text-[13px] font-sans text-ink
                             placeholder-ink4 outline-none focus:border-terra focus:ring-1 focus:ring-terra/20 resize-none transition-colors"
                      placeholder="Optional product description…">{{ old('description', $product->description ?? '') }}</textarea>
        </div>

        <div class="flex items-center gap-3">
            <x-btn type="submit" variant="primary">
                {{ isset($product) ? 'Save Changes' : 'Create Product' }}
            </x-btn>
            <x-btn :href="route('products.index')" variant="ghost">Cancel</x-btn>
        </div>
    </form>

</x-app-layout>
