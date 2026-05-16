<x-app-layout title="Customers">

    <x-page-header overline="Loyalty & CRM" title="Customers"
                   :subtitle="$activeBranchLabel . ' · ' . $customers->total() . ' registered members'">
        <x-slot:actions>
            @can('create customers')
            <x-btn variant="primary" size="sm" :href="route('customers.create')">
                <x-icon name="plus" class="w-3 h-3" /> Add Customer
            </x-btn>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-3 gap-3 mb-6">
        <x-stat-box label="Total Members"     :value="$totalCount" />
        <x-stat-box label="Gold & Platinum"   :value="$loyalCount" />
        <x-stat-box label="Avg. Loyalty Pts"  :value="number_format($avgPoints)" />
    </div>

    {{-- Tier filter + search --}}
    <form method="GET" action="{{ route('customers.index') }}" class="flex items-center gap-3 mb-5">
        <div class="flex gap-1 bg-cream border border-cream3 rounded-md p-[3px]">
            @foreach(['all' => 'All', 'bronze' => 'Bronze', 'silver' => 'Silver', 'gold' => 'Gold', 'platinum' => 'Platinum'] as $val => $lbl)
                <a href="{{ route('customers.index', array_merge(request()->query(), ['tier' => $val])) }}"
                   class="px-3.5 py-[5px] rounded text-[11px] font-mono tracking-wide transition-all
                          {{ request('tier', 'all') === $val ? 'bg-white text-ink shadow-sm' : 'text-ink4 hover:text-ink' }}">
                    {{ $lbl }}
                </a>
            @endforeach
        </div>
        @if(auth()->user()->isSuperAdmin())
        <x-form-select name="branch_id" label="" placeholder="All Branches" class="min-w-[160px]">
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}" {{ $effectiveBranchId == $branch->id ? 'selected' : '' }}>
                    {{ $branch->name }}
                </option>
            @endforeach
        </x-form-select>
        @endif
        <div class="flex items-center gap-2 bg-white border border-cream3 rounded-md px-3 py-[7px] flex-1 max-w-xs focus-within:border-terra transition-colors">
            <x-icon name="filter" class="w-3.5 h-3.5 text-ink4 flex-shrink-0" />
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search name, email, phone…"
                   class="bg-transparent border-none outline-none text-[12px] font-sans text-ink placeholder-ink4 w-full">
        </div>
        <x-btn type="submit" variant="ghost" size="sm">Apply</x-btn>
    </form>

    <x-data-table title="Member Directory">
        <x-slot:tableHeader>
            <span class="font-mono text-[11px] text-ink4">{{ $customers->total() }} members</span>
        </x-slot:tableHeader>
        <x-slot:head>
            <x-th>Full Name</x-th>
            <x-th>Email</x-th>
            <x-th>Phone</x-th>
            <x-th>Home Branch</x-th>
            <x-th>Loyalty Pts</x-th>
            <x-th>Tier</x-th>
            <x-th>Joined</x-th>
            <x-th></x-th>
        </x-slot:head>

        @forelse($customers as $customer)
            <tr class="hover:bg-cream transition-colors cursor-pointer"
                onclick="window.location='{{ route('customers.show', $customer) }}'">
                <x-td class="font-semibold">{{ $customer->full_name }}</x-td>
                <x-td><span class="font-mono text-[10px] text-ink4">{{ $customer->email ?? '—' }}</span></x-td>
                <x-td><span class="font-mono text-[11px]">{{ $customer->phone ?? '—' }}</span></x-td>
                <x-td class="text-ink3">{{ $customer->registeredBranch->name ?? '—' }}</x-td>
                <x-td><span class="font-mono font-medium">{{ number_format($customer->loyalty_points) }}</span></x-td>
                <x-td>
                    <x-badge variant="{{ match($customer->tier) { 'platinum' => 'terra', 'gold' => 'gold', 'silver' => 'navy', default => 'muted' } }}">
                        {{ ucfirst($customer->tier) }}
                    </x-badge>
                </x-td>
                <x-td><span class="font-mono text-[10px] text-ink4">{{ $customer->created_at->format('M d, Y') }}</span></x-td>
                <x-td>
                    <div class="flex gap-2" @click.stop>
                        @can('edit customers')
                        <x-btn :href="route('customers.edit', $customer)" variant="ghost" size="sm">Edit</x-btn>
                        @endcan
                        @can('delete customers')
                        <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'delete-customer-{{ $customer->id }}' }))"
                                class="inline-flex items-center px-3 py-[5px] text-[11px] font-medium text-danger/70 border border-danger/20 rounded-md hover:bg-danger/5 transition-colors">
                            Delete
                        </button>
                        @endcan
                    </div>
                    @can('delete customers')
                    <x-confirm-delete :name="'delete-customer-' . $customer->id"
                                      :action="route('customers.destroy', $customer)"
                                      :item-name="$customer->full_name" />
                    @endcan
                </x-td>
            </tr>
        @empty
            <tr><td colspan="8">
                <x-empty-state title="No customers found" description="Add your first customer or adjust your filters." icon="users">
                    <x-slot:action><x-btn :href="route('customers.create')" variant="primary" size="sm">+ Add Customer</x-btn></x-slot:action>
                </x-empty-state>
            </td></tr>
        @endforelse

        <x-slot:pagination>{{ $customers->withQueryString()->links('vendor.pagination.simple') }}</x-slot:pagination>
    </x-data-table>

</x-app-layout>
