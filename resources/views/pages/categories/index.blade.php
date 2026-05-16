<x-app-layout title="Product Categories">

    <x-page-header overline="Catalogue" title="Product Categories"
                   :subtitle="$total . ' categories · ' . $productTotal . ' products assigned'">
        <x-slot:actions>
            @can('create categories')
            <x-btn variant="primary" size="sm" :href="route('categories.create')">
                <x-icon name="plus" class="w-3 h-3" /> Add Category
            </x-btn>
            @endcan
        </x-slot:actions>
    </x-page-header>

    @if(session('success'))
        <x-alert type="success" :message="session('success')" class="mb-5" />
    @endif
    @if($errors->any())
        <x-alert type="error" :message="$errors->first()" class="mb-5" />
    @endif

    <div class="grid grid-cols-3 gap-3 mb-6">
        <x-stat-box label="Total Categories" :value="$total" />
        <x-stat-box label="Root Categories"  :value="$rootCount" />
        <x-stat-box label="Products Assigned" :value="$productTotal" value-class="text-ok" />
    </div>

    {{-- Tree view --}}
    @php
        $roots    = $categories->whereNull('parent_category_id');
        $children = $categories->whereNotNull('parent_category_id')->groupBy('parent_category_id');
    @endphp

    <x-data-table title="Category Tree">
        <x-slot:head>
            <x-th>Category</x-th>
            <x-th>Description</x-th>
            <x-th>Parent</x-th>
            <x-th>Products</x-th>
            <x-th>Subcategories</x-th>
            <x-th></x-th>
        </x-slot:head>

        @forelse($categories->sortBy('name') as $cat)
        <tr class="hover:bg-cream transition-colors {{ $cat->parent_category_id ? 'bg-cream/30' : '' }}">
            <x-td>
                <div class="flex items-center gap-2">
                    @if($cat->parent_category_id)
                        <span class="w-3 h-3 flex-shrink-0 border-l-2 border-b-2 border-cream3 rounded-bl ml-3"></span>
                    @else
                        <span class="w-2 h-2 rounded-full bg-purple flex-shrink-0"></span>
                    @endif
                    <span class="font-semibold text-ink text-[13px]">{{ $cat->name }}</span>
                </div>
            </x-td>
            <x-td class="text-ink3 text-[12px]">{{ Str::limit($cat->description ?? '—', 50) }}</x-td>
            <x-td>
                @if($cat->parent)
                    <span class="font-mono text-[10px] bg-purple/8 text-purple px-2 py-0.5 rounded">
                        {{ $cat->parent->name }}
                    </span>
                @else
                    <span class="text-ink4 text-[11px]">Root</span>
                @endif
            </x-td>
            <x-td>
                <span class="font-mono font-semibold text-[12px] {{ $cat->products_count > 0 ? 'text-ok' : 'text-ink4' }}">
                    {{ $cat->products_count }}
                </span>
            </x-td>
            <x-td>
                <span class="font-mono text-[12px] text-ink3">
                    {{ $cat->children->count() ?: '—' }}
                </span>
            </x-td>
            <x-td>
                <div class="flex gap-2" @click.stop>
                    @can('edit categories')
                    <x-btn :href="route('categories.edit', $cat)" variant="ghost" size="sm">Edit</x-btn>
                    @endcan
                    @can('delete categories')
                    @if($cat->products_count === 0 && $cat->children->count() === 0)
                    <form method="POST" action="{{ route('categories.destroy', $cat) }}">
                        @csrf @method('DELETE')
                        <x-btn type="submit" variant="danger" size="sm"
                               onclick="return confirm('Delete \"{{ $cat->name }}\"?')">Delete</x-btn>
                    </form>
                    @else
                    <span class="font-mono text-[9px] text-ink4 self-center px-2">
                        {{ $cat->products_count > 0 ? 'Has products' : 'Has children' }}
                    </span>
                    @endif
                    @endcan
                </div>
            </x-td>
        </tr>
        @empty
            <tr><td colspan="6">
                <x-empty-state title="No categories yet" description="Add your first product category to get started." icon="list">
                    <x-slot:action>
                        <x-btn :href="route('categories.create')" variant="primary" size="sm">+ Add Category</x-btn>
                    </x-slot:action>
                </x-empty-state>
            </td></tr>
        @endforelse
    </x-data-table>

</x-app-layout>
