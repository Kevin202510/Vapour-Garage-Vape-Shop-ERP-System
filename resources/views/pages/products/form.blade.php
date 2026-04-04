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

        <div class="flex items-center gap-6 mb-6">
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
                <span class="text-[13px] text-ink2">Active (visible in POS)</span>
            </label>
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
