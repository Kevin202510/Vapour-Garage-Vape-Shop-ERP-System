<x-app-layout :title="$branch->name">

    <x-page-header
        overline="Network · Branch"
        :title="$branch->name"
        :subtitle="$branch->address . ', ' . $branch->city"
    >
        <x-slot:actions>
            <x-btn :href="route('branches.index')" variant="ghost" size="sm">← All Branches</x-btn>
            @can('edit branches')
            <x-btn :href="route('branches.edit', $branch)" variant="ghost" size="sm">Edit</x-btn>
            @endcan
        </x-slot:actions>
    </x-page-header>

    {{-- Top metrics --}}
    <div class="grid grid-cols-4 gap-4 mb-6">
        <x-kpi-card label="Revenue Today"   :value="'₱' . number_format($branch->today_revenue ?? 0)"   color="terra" />
        <x-kpi-card label="Transactions"    :value="number_format($branch->today_txn_count ?? 0)"        color="navy" />
        <x-kpi-card label="Active Staff"    :value="$branch->employees->where('is_active', true)->count()" color="gold" />
        <x-kpi-card label="SKUs in Stock"   :value="$branch->inventory->count()"                         color="sage" />
    </div>

    <div class="grid grid-cols-2 gap-5 mb-5">

        {{-- Branch details --}}
        <x-card title="Branch Details">
            <dl class="flex flex-col gap-4">
                @foreach([
                    'Branch Name'    => $branch->name,
                    'Address'        => $branch->address,
                    'City'           => $branch->city,
                    'Contact Number' => $branch->contact_number ?? '—',
                    'Status'         => $branch->is_active ? 'Active' : 'Inactive',
                    'Created'        => $branch->created_at->format('F d, Y'),
                ] as $label => $value)
                    <div class="flex justify-between items-start border-b border-cream2 pb-3 last:border-0 last:pb-0">
                        <dt class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 pt-0.5">{{ $label }}</dt>
                        <dd class="text-[12.5px] text-ink2 text-right max-w-[60%]">
                            @if($label === 'Status')
                                <x-badge variant="{{ $branch->is_active ? 'ok' : 'error' }}">{{ $value }}</x-badge>
                            @else
                                {{ $value }}
                            @endif
                        </dd>
                    </div>
                @endforeach
            </dl>
        </x-card>

        {{-- Low stock alert --}}
        <x-card title="Low Stock Alerts" subtitle="ITEMS BELOW REORDER LEVEL">
            @php
                $lowStock = $branch->inventory->filter(fn ($i) =>
                    ($i->quantity_on_hand - $i->quantity_reserved) <= $i->product->reorder_level
                );
            @endphp
            @if($lowStock->isEmpty())
                <x-empty-state title="All stock levels healthy" icon="check-circle" />
            @else
                <div class="flex flex-col gap-2">
                    @foreach($lowStock->take(6) as $item)
                        @php $avail = $item->quantity_on_hand - $item->quantity_reserved; @endphp
                        <div class="flex items-center justify-between px-3 py-2.5 rounded-md
                                    {{ $avail <= 0 ? 'bg-danger/5 border border-danger/20' : 'bg-warn/5 border border-warn/20' }}">
                            <div>
                                <p class="text-[12px] font-medium text-ink">{{ $item->product->name }}</p>
                                <p class="font-mono text-[9px] text-ink4 mt-0.5">{{ $item->product->sku }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-serif text-[16px] font-bold {{ $avail <= 0 ? 'text-danger' : 'text-warn' }}">
                                    {{ $avail }}
                                </p>
                                <p class="font-mono text-[9px] text-ink4">/ {{ $item->product->reorder_level }} min</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>
    </div>

    {{-- Staff --}}
    <x-data-table title="Branch Staff" class="mb-5">
        <x-slot:tableHeader>
            @can('create employees')
            <x-btn :href="route('employees.create', ['branch_id' => $branch->id])" variant="ghost" size="sm">
                + Add Staff
            </x-btn>
            @endcan
        </x-slot:tableHeader>
        <x-slot:head>
            <x-th>Code</x-th>
            <x-th>Name</x-th>
            <x-th>Role</x-th>
            <x-th>Type</x-th>
            <x-th>Salary</x-th>
            <x-th>Today</x-th>
        </x-slot:head>
        @forelse($branch->employees->where('is_active', true) as $emp)
            @php
                $att    = $emp->attendance->where('work_date', now()->toDateString())->first();
                $status = $att?->status ?? 'absent';
            @endphp
            <tr class="hover:bg-cream transition-colors cursor-pointer"
                onclick="window.location='{{ route('employees.show', $emp) }}'">
                <x-td><span class="font-mono text-[11px] text-ink3">{{ $emp->employee_code }}</span></x-td>
                <x-td class="font-semibold">{{ $emp->full_name }}</x-td>
                <x-td>
                    <x-badge variant="{{ match($emp->role) { 'branch_manager' => 'navy', 'cashier' => 'terra', default => 'muted' } }}">
                        {{ ucwords(str_replace('_', ' ', $emp->role)) }}
                    </x-badge>
                </x-td>
                <x-td><span class="font-mono text-[10px] text-ink4">{{ ucwords(str_replace('_', ' ', $emp->employment_type)) }}</span></x-td>
                <x-td><span class="font-mono text-[12px]">₱{{ number_format($emp->base_salary, 0) }}</span></x-td>
                <x-td>
                    <x-badge variant="{{ match($status) { 'present' => 'ok', 'late' => 'warn', 'leave' => 'gold', default => 'muted' } }}">
                        {{ ucfirst($status) }}
                    </x-badge>
                </x-td>
            </tr>
        @empty
            <tr><td colspan="6"><x-empty-state title="No staff found" icon="user" /></td></tr>
        @endforelse
    </x-data-table>

    {{-- Recent transactions --}}
    <x-data-table title="Recent Transactions">
        <x-slot:tableHeader>
            <x-btn :href="route('sales.index', ['branch_id' => $branch->id])" variant="ghost" size="sm">View all →</x-btn>
        </x-slot:tableHeader>
        <x-slot:head>
            <x-th>TXN Number</x-th>
            <x-th>Cashier</x-th>
            <x-th>Customer</x-th>
            <x-th>Payment</x-th>
            <x-th>Total</x-th>
            <x-th>Date</x-th>
            <x-th>Status</x-th>
        </x-slot:head>
        @forelse($branch->salesTransactions()->latest('transaction_date')->limit(8)->get() as $txn)
            <tr class="hover:bg-cream transition-colors cursor-pointer"
                onclick="window.location='{{ route('sales.show', $txn) }}'">
                <x-td><span class="font-mono text-[11px] text-ink3">{{ $txn->transaction_number }}</span></x-td>
                <x-td>{{ optional($txn->cashier)->full_name ?? '—' }}</x-td>
                <x-td>{{ $txn->customer?->full_name ?? 'Walk-in' }}</x-td>
                <x-td>
                    <x-badge variant="{{ match($txn->payment_method) { 'gcash','maya' => 'navy', 'card' => 'terra', default => 'muted' } }}">
                        {{ ucfirst($txn->payment_method) }}
                    </x-badge>
                </x-td>
                <x-td><span class="font-mono font-medium">₱{{ number_format($txn->total_amount, 2) }}</span></x-td>
                <x-td><span class="font-mono text-[10px] text-ink4">{{ $txn->transaction_date->format('M d · g:i A') }}</span></x-td>
                <x-td>
                    <x-badge variant="{{ $txn->status === 'completed' ? 'ok' : 'error' }}">
                        {{ ucfirst($txn->status) }}
                    </x-badge>
                </x-td>
            </tr>
        @empty
            <tr><td colspan="7"><x-empty-state title="No transactions yet" icon="activity" /></td></tr>
        @endforelse
    </x-data-table>

</x-app-layout>
