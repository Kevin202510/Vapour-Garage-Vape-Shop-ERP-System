<x-app-layout title="Suppliers">

    <x-page-header overline="Procurement" title="Suppliers"
                   :subtitle="$totalActive . ' active · ' . $totalAll . ' total'">
        <x-slot:actions>
            @can('create suppliers')
            <x-btn variant="primary" size="sm" :href="route('suppliers.create')">
                <x-icon name="plus" class="w-3 h-3" /> Add Supplier
            </x-btn>
            @endcan
        </x-slot:actions>
    </x-page-header>

    @if(session('success'))
        <x-alert type="success" :message="session('success')" class="mb-5" />
    @endif

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('suppliers.index') }}" class="flex items-center gap-3 flex-wrap mb-5">
        <div class="flex gap-1 bg-cream border border-cream3 rounded-md p-[3px]">
            @foreach(['active' => 'Active', 'inactive' => 'Inactive', 'all' => 'All'] as $val => $lbl)
            <a href="{{ route('suppliers.index', array_merge(request()->only('search'), ['is_active' => $val])) }}"
               class="px-3.5 py-[5px] rounded text-[11px] font-mono tracking-wide transition-all
                      {{ request('is_active', 'active') === $val ? 'bg-white text-ink shadow-sm' : 'text-ink4 hover:text-ink' }}">
                {{ $lbl }}
            </a>
            @endforeach
        </div>
        <div class="flex items-center gap-2 bg-white border border-cream3 rounded-md px-3 py-[7px] flex-1 max-w-xs focus-within:border-purple transition-colors">
            <x-icon name="filter" class="w-3.5 h-3.5 text-ink4 flex-shrink-0" />
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search name, contact, email…"
                   class="bg-transparent border-none outline-none text-[12px] font-sans text-ink placeholder-ink4 w-full">
        </div>
        <x-btn type="submit" variant="ghost" size="sm">Apply</x-btn>
        @if(request()->hasAny(['search']))
            <a href="{{ route('suppliers.index') }}" class="font-mono text-[11px] text-ink4 hover:text-ink">Clear</a>
        @endif
    </form>

    <x-data-table title="Supplier Directory">
        <x-slot:tableHeader>
            <span class="font-mono text-[11px] text-ink4">{{ $suppliers->total() }} suppliers</span>
        </x-slot:tableHeader>
        <x-slot:head>
            <x-th>Company</x-th>
            <x-th>Contact Person</x-th>
            <x-th>Email</x-th>
            <x-th>Phone</x-th>
            <x-th>TIN</x-th>
            <x-th>Status</x-th>
            <x-th></x-th>
        </x-slot:head>

        @forelse($suppliers as $supplier)
        <tr class="hover:bg-cream transition-colors cursor-pointer"
            onclick="window.location='{{ route('suppliers.show', $supplier) }}'">
            <x-td class="font-semibold text-ink">{{ $supplier->company_name }}</x-td>
            <x-td class="text-ink3">{{ $supplier->contact_person ?? '—' }}</x-td>
            <x-td>
                @if($supplier->email)
                    <a href="mailto:{{ $supplier->email }}" class="font-mono text-[10px] text-purple hover:underline" @click.stop>
                        {{ $supplier->email }}
                    </a>
                @else
                    <span class="text-ink4">—</span>
                @endif
            </x-td>
            <x-td><span class="font-mono text-[11px] text-ink3">{{ $supplier->phone ?? '—' }}</span></x-td>
            <x-td><span class="font-mono text-[10px] text-ink4">{{ $supplier->tin ?? '—' }}</span></x-td>
            <x-td>
                <x-badge variant="{{ $supplier->is_active ? 'ok' : 'muted' }}">
                    {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                </x-badge>
            </x-td>
            <x-td>
                <div class="flex gap-2" @click.stop>
                    <x-btn :href="route('suppliers.show', $supplier)" variant="ghost" size="sm">View</x-btn>
                    @can('edit suppliers')
                    <x-btn :href="route('suppliers.edit', $supplier)" variant="ghost" size="sm">Edit</x-btn>
                    @endcan
                    @can('delete suppliers')
                    @if($supplier->is_active)
                    <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}">
                        @csrf @method('DELETE')
                        <x-btn type="submit" variant="danger" size="sm"
                               onclick="return confirm('Deactivate \"{{ $supplier->company_name }}\"?')">Deactivate</x-btn>
                    </form>
                    @endif
                    @endcan
                </div>
            </x-td>
        </tr>
        @empty
            <tr><td colspan="7">
                <x-empty-state title="No suppliers found" description="Add your first supplier to start managing procurement." icon="shopping-bag">
                    <x-slot:action>
                        <x-btn :href="route('suppliers.create')" variant="primary" size="sm">+ Add Supplier</x-btn>
                    </x-slot:action>
                </x-empty-state>
            </td></tr>
        @endforelse

        <x-slot:pagination>{{ $suppliers->withQueryString()->links('vendor.pagination.simple') }}</x-slot:pagination>
    </x-data-table>

</x-app-layout>
