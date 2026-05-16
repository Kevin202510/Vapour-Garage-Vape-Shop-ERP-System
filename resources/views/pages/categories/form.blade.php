<x-app-layout :title="isset($category) ? 'Edit Category' : 'New Category'">

    <x-page-header
        overline="Catalogue · Categories"
        :title="isset($category) ? 'Edit Category' : 'New Category'"
        :subtitle="isset($category) ? $category->name : 'Add a new product category'"
    >
        <x-slot:actions>
            <x-btn :href="route('categories.index')" variant="ghost" size="sm">← All Categories</x-btn>
        </x-slot:actions>
    </x-page-header>

    <form method="POST"
          action="{{ isset($category) ? route('categories.update', $category) : route('categories.store') }}"
          class="max-w-xl">
        @csrf
        @if(isset($category)) @method('PUT') @endif

        <div class="bg-white border border-cream3 rounded-lg p-6 flex flex-col gap-5">

            <x-form-input label="Category Name" name="name"
                          :value="old('name', $category->name ?? '')"
                          required placeholder="e.g. Disposables, EJuice, Box Mods" />

            <div class="mb-0">
                <label class="erp-label mb-1.5 block">Description <span class="text-ink4 font-sans normal-case tracking-normal text-[11px]">(optional)</span></label>
                <textarea name="description" rows="2"
                          class="erp-input resize-none"
                          placeholder="Brief description of what products belong here…">{{ old('description', $category->description ?? '') }}</textarea>
                @error('description') <p class="erp-field-error mt-1">{{ $message }}</p> @enderror
            </div>

            <x-form-select label="Parent Category (optional)" name="parent_category_id">
                <option value="">— None (root category) —</option>
                @foreach($parents as $parent)
                    <option value="{{ $parent->id }}"
                        {{ old('parent_category_id', $category->parent_category_id ?? '') == $parent->id ? 'selected' : '' }}>
                        {{ $parent->name }}
                    </option>
                @endforeach
            </x-form-select>

            @if(!empty($parents))
            <p class="font-mono text-[9px] text-ink4 tracking-wide -mt-3">
                Nesting makes this a subcategory of the selected parent. Leave blank for a top-level category.
            </p>
            @endif

        </div>

        <div class="flex items-center gap-3 mt-5">
            <x-btn type="submit" variant="primary">
                {{ isset($category) ? 'Save Changes' : 'Create Category' }}
            </x-btn>
            <x-btn :href="route('categories.index')" variant="ghost">Cancel</x-btn>
        </div>
    </form>

</x-app-layout>
