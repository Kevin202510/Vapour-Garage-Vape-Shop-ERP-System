<x-app-layout title="Sales">

    <x-page-header overline="Commerce" title="Sales History" subtitle="Transaction log · {{ now()->format('F Y') }}">
        <x-slot:actions>
            <x-btn variant="ghost" size="sm">Filter</x-btn>
            @can('export reports')
            <x-btn variant="ghost" size="sm">
                <x-icon name="download" class="w-3 h-3" /> Export CSV
            </x-btn>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-3 gap-3 mb-6">
        <x-stat-box label="This Month Revenue"  :value="'₱' . number_format($stats['month_revenue'])" />
        <x-stat-box label="Total Transactions"  :value="number_format($stats['month_count'])" />
        <x-stat-box label="Avg. Basket Size"    :value="'₱' . number_format($stats['avg_basket'])" />
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('sales.index') }}" class="flex items-center gap-3 mb-5">
        <div class="flex gap-1 bg-cream border border-cream3 rounded-md p-[3px]">
            @foreach(['all' => 'All', 'completed' => 'Completed', 'voided' => 'Voided'] as $val => $lbl)
                <a href="{{ route('sales.index', array_merge(request()->query(), ['status' => $val])) }}"
                   class="px-3.5 py-[5px] rounded text-[11px] font-mono tracking-wide transition-all
                          {{ request('status', 'all') === $val ? 'bg-white text-ink shadow-sm' : 'text-ink4 hover:text-ink' }}">
                    {{ $lbl }}
                </a>
            @endforeach
        </div>
        @if(auth()->user()->isSuperAdmin())
            <x-form-select name="branch_id" label="" placeholder="All Branches" class="min-w-[140px]">
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </x-form-select>
        @endif
        <x-form-input name="date_from" type="date" label="" :value="request('date_from')" class="min-w-[130px]" />
        <x-form-input name="date_to"   type="date" label="" :value="request('date_to')"   class="min-w-[130px]" />
        <x-btn type="submit" variant="ghost" size="sm">Apply</x-btn>
    </form>

    <x-data-table title="Transactions">
        <x-slot:tableHeader>
            <div class="font-mono text-[11px] text-ink4">
                {{ $transactions->total() }} results
            </div>
        </x-slot:tableHeader>
        <x-slot:head>
            <x-th>TXN Number</x-th>
            <x-th>Date & Time</x-th>
            <x-th>Branch</x-th>
            <x-th>Cashier</x-th>
            <x-th>Customer</x-th>
            <x-th>Items</x-th>
            <x-th>Payment</x-th>
            <x-th>Total</x-th>
            <x-th>Status</x-th>
            <x-th></x-th>
        </x-slot:head>

        @forelse($transactions as $txn)
            <tr class="hover:bg-cream transition-colors cursor-pointer"
                onclick="window.location='{{ route('sales.show', $txn) }}'">
                <x-td><span class="font-mono text-[11px] text-ink3">{{ $txn->transaction_number }}</span></x-td>
                <x-td><span class="font-mono text-[10px] text-ink4">{{ $txn->transaction_date->format('M d · g:i A') }}</span></x-td>
                <x-td>{{ $txn->branch->name ?? '—' }}</x-td>
                <x-td>{{ optional($txn->cashier)->last_name }}, {{ optional($txn->cashier)->first_name }}</x-td>
                <x-td>{{ $txn->customer ? $txn->customer->full_name : 'Walk-in' }}</x-td>
                <x-td><span class="font-mono">{{ $txn->items_count ?? $txn->items->count() }}</span></x-td>
                <x-td>
                    <x-badge variant="{{ match($txn->payment_method) { 'gcash','maya' => 'navy', 'card' => 'terra', default => 'muted' } }}">
                        {{ ucfirst($txn->payment_method) }}
                    </x-badge>
                </x-td>
                <x-td><span class="font-mono text-[12px] font-medium">₱{{ number_format($txn->total_amount, 2) }}</span></x-td>
                <x-td>
                    <x-badge variant="{{ $txn->status === 'completed' ? 'ok' : ($txn->status === 'voided' ? 'error' : 'warn') }}">
                        {{ ucfirst($txn->status) }}
                    </x-badge>
                </x-td>
                <x-td><x-btn :href="route('sales.show', $txn)" variant="ghost" size="sm" @click.stop>View</x-btn></x-td>
            </tr>
        @empty
            <tr><td colspan="10"><x-empty-state title="No transactions found" icon="activity" /></td></tr>
        @endforelse

        <x-slot:pagination>{{ $transactions->withQueryString()->links('vendor.pagination.simple') }}</x-slot:pagination>
    </x-data-table>

</x-app-layout>
