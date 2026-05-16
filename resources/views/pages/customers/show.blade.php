<x-app-layout :title="$customer->full_name">

    <x-page-header
        overline="Loyalty & CRM · Customer"
        :title="$customer->full_name"
        :subtitle="$customer->email ?? $customer->phone ?? 'Walk-in customer'"
    >
        <x-slot:actions>
            <x-btn :href="route('customers.index')" variant="ghost" size="sm">← All Customers</x-btn>
            @can('edit customers')
            <x-btn :href="route('customers.edit', $customer)" variant="ghost" size="sm">Edit Profile</x-btn>
            @endcan
            @can('delete customers')
            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'delete-customer' }))"
                    class="inline-flex items-center px-3 py-[5px] text-[11px] font-medium text-danger/70 border border-danger/20 rounded-md hover:bg-danger/5 transition-colors">
                Delete
            </button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-confirm-delete name="delete-customer"
                      :action="route('customers.destroy', $customer)"
                      :item-name="$customer->full_name" />

    @if(session('success'))
        <x-alert type="success" :message="session('success')" class="mb-5" />
    @endif
    @if($errors->has('deduct'))
        <x-alert type="error" :message="$errors->first('deduct')" class="mb-5" />
    @endif

    {{-- KPI row --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <x-kpi-card label="Loyalty Points"  :value="number_format($customer->loyalty_points)"    color="purple" />
        <x-kpi-card label="Total Spent"     :value="'₱' . number_format($totalSpent, 2)"         color="terra" />
        <x-kpi-card label="Total Visits"    :value="number_format($visitCount)"                   color="navy" />
    </div>

    <div class="grid grid-cols-2 gap-5 mb-5">

        {{-- Customer profile --}}
        <x-card title="Customer Profile">
            <dl class="flex flex-col gap-4">
                @foreach([
                    'Full Name'     => $customer->full_name,
                    'Email'         => $customer->email ?? '—',
                    'Phone'         => $customer->phone ?? '—',
                    'Date of Birth' => $customer->birth_date
                        ? $customer->birth_date->format('F d, Y') . ' (Age ' . $customer->birth_date->age . ')'
                        : '—',
                    'Home Branch'   => $customer->registeredBranch?->name ?? '—',
                    'Member Since'  => $customer->created_at->format('F d, Y'),
                ] as $label => $value)
                <div class="flex justify-between items-start border-b border-cream3 pb-3 last:border-0 last:pb-0">
                    <dt class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 pt-0.5 flex-shrink-0">{{ $label }}</dt>
                    <dd class="text-[12.5px] text-ink2 text-right ml-4">{{ $value }}</dd>
                </div>
                @endforeach
            </dl>
        </x-card>

        {{-- Loyalty status --}}
        <x-card title="Loyalty Status">

            {{-- Current tier badge --}}
            @php
                $tierConfig = [
                    'bronze'   => ['icon' => 'B', 'bg' => 'bg-[#8B5E3C]/10', 'text' => 'text-[#8B5E3C]', 'label' => 'Bronze'],
                    'silver'   => ['icon' => 'S', 'bg' => 'bg-ink4/10',      'text' => 'text-ink3',       'label' => 'Silver'],
                    'gold'     => ['icon' => 'G', 'bg' => 'bg-gold/10',       'text' => 'text-gold',       'label' => 'Gold'],
                    'platinum' => ['icon' => 'P', 'bg' => 'bg-purple/10',     'text' => 'text-purple',     'label' => 'Platinum'],
                ];
                $tc = $tierConfig[$customer->tier] ?? $tierConfig['bronze'];
            @endphp
            <div class="flex items-center gap-3 mb-5 px-4 py-3 bg-cream rounded-lg border border-cream3">
                <div class="w-12 h-12 rounded-full flex items-center justify-center font-display font-black text-[18px]
                            {{ $tc['bg'] }} {{ $tc['text'] }}">
                    {{ $tc['icon'] }}
                </div>
                <div>
                    <p class="font-display text-[16px] text-ink">{{ $tc['label'] }} Member</p>
                    <p class="font-mono text-[10px] text-ink4 mt-0.5">{{ number_format($customer->loyalty_points) }} points</p>
                </div>
            </div>

            {{-- Tier progress --}}
            @php
                $tiers = [
                    ['Bronze',   0,     1000,  'muted',  'bronze'],
                    ['Silver',   1000,  5000,  'muted',  'silver'],
                    ['Gold',     5000,  10000, 'gold',   'gold'],
                    ['Platinum', 10000, null,  'purple', 'platinum'],
                ];
            @endphp
            <div class="flex flex-col gap-2 mb-5">
                @foreach($tiers as [$name, $min, $max, $variant, $key])
                    @php $active = $customer->tier === $key; @endphp
                    <div class="flex items-center justify-between px-3 py-2 rounded-md
                                {{ $active ? 'bg-purple/5 border border-purple/20' : 'border border-transparent' }}">
                        <div class="flex items-center gap-2.5">
                            <x-badge :variant="$variant">{{ $name }}</x-badge>
                            <span class="font-mono text-[10px] text-ink4">
                                {{ number_format($min) }}{{ $max ? ' – ' . number_format($max) . ' pts' : '+ pts' }}
                            </span>
                        </div>
                        @if($active)
                            <x-icon name="check-circle" class="w-4 h-4 text-ok" />
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Points management --}}
            @can('edit customers')
            <div class="border-t border-cream3 pt-4 flex flex-col gap-3">
                <p class="erp-label">Manage Points</p>

                {{-- Add points --}}
                <form method="POST" action="{{ route('customers.add-points', $customer) }}"
                      class="flex gap-2 items-center">
                    @csrf
                    <input type="number" name="points" min="1" max="100000"
                           placeholder="Points to add…"
                           class="erp-input flex-1 text-[12px]" required>
                    <x-btn type="submit" variant="primary" size="sm">+ Add</x-btn>
                </form>

                {{-- Deduct points --}}
                <form method="POST" action="{{ route('customers.deduct-points', $customer) }}"
                      class="flex gap-2 items-center">
                    @csrf
                    <input type="number" name="points" min="1" max="{{ $customer->loyalty_points }}"
                           placeholder="Points to deduct…"
                           class="erp-input flex-1 text-[12px]" required>
                    <x-btn type="submit" variant="danger" size="sm">− Deduct</x-btn>
                </form>
                @error('deduct')
                    <p class="erp-field-error">{{ $message }}</p>
                @enderror
            </div>
            @endcan
        </x-card>
    </div>

    {{-- Purchase history --}}
    <x-data-table title="Purchase History">
        <x-slot:tableHeader>
            <span class="font-mono text-[11px] text-ink4">{{ $transactions->total() }} transactions</span>
        </x-slot:tableHeader>
        <x-slot:head>
            <x-th>TXN Number</x-th>
            <x-th>Branch</x-th>
            <x-th>Items</x-th>
            <x-th>Payment</x-th>
            <x-th>Total</x-th>
            <x-th>Date</x-th>
            <x-th>Status</x-th>
        </x-slot:head>

        @forelse($transactions as $txn)
            <tr class="hover:bg-cream transition-colors cursor-pointer"
                onclick="window.location='{{ route('sales.show', $txn) }}'">
                <x-td><span class="font-mono text-[11px] text-purple">{{ $txn->transaction_number }}</span></x-td>
                <x-td class="text-ink3">{{ $txn->branch->name ?? '—' }}</x-td>
                <x-td><span class="font-mono">{{ $txn->items->count() }}</span></x-td>
                <x-td>
                    <x-badge variant="{{ match($txn->payment_method) { 'gcash','maya' => 'ok', 'card' => 'navy', 'bank_transfer' => 'purple', default => 'muted' } }}">
                        {{ ucfirst(str_replace('_',' ', $txn->payment_method)) }}
                    </x-badge>
                </x-td>
                <x-td><span class="font-mono font-semibold text-ink">₱{{ number_format($txn->total_amount, 2) }}</span></x-td>
                <x-td><span class="font-mono text-[10px] text-ink4">{{ $txn->transaction_date->format('M d, Y · g:i A') }}</span></x-td>
                <x-td>
                    <x-badge variant="{{ match($txn->status) { 'completed' => 'ok', 'voided' => 'error', default => 'muted' } }}">
                        {{ ucfirst($txn->status) }}
                    </x-badge>
                </x-td>
            </tr>
        @empty
            <tr><td colspan="7"><x-empty-state title="No purchase history yet" icon="activity" /></td></tr>
        @endforelse

        <x-slot:pagination>{{ $transactions->links('vendor.pagination.simple') }}</x-slot:pagination>
    </x-data-table>

</x-app-layout>
