<x-app-layout :title="$customer->full_name">

    <x-page-header
        overline="Loyalty & CRM · Customer"
        :title="$customer->full_name"
        :subtitle="$customer->email ?? $customer->phone ?? 'Walk-in customer'"
    >
        <x-slot:actions>
            <x-btn :href="route('customers.index')" variant="ghost" size="sm">← All Customers</x-btn>
            <x-btn :href="route('customers.edit', $customer)" variant="ghost" size="sm">Edit</x-btn>
            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'delete-customer' }))"
                    class="inline-flex items-center px-3 py-[5px] text-[11px] font-medium text-danger/70 border border-danger/20 rounded-md hover:bg-danger/5 transition-colors">
                Delete
            </button>
        </x-slot:actions>
    </x-page-header>

    <x-confirm-delete name="delete-customer"
                      :action="route('customers.destroy', $customer)"
                      :item-name="$customer->full_name" />

    <div class="grid grid-cols-3 gap-4 mb-6">
        <x-stat-box label="Loyalty Points" :value="number_format($customer->loyalty_points)" />
        <x-stat-box label="Total Purchases" :value="'₱' . number_format($customer->transactions->where('status','completed')->sum('total_amount'))" />
        <x-stat-box label="Visit Count"     :value="$customer->transactions->where('status','completed')->count()" />
    </div>

    <div class="grid grid-cols-2 gap-5 mb-5">

        {{-- Profile --}}
        <x-card title="Customer Profile">
            <dl class="flex flex-col gap-4">
                @foreach([
                    'Full Name'    => $customer->full_name,
                    'Email'        => $customer->email ?? '—',
                    'Phone'        => $customer->phone ?? '—',
                    'Date of Birth'=> $customer->birth_date ? $customer->birth_date->format('F d, Y') . ' (Age ' . $customer->birth_date->age . ')' : '—',
                    'Home Branch'  => $customer->registeredBranch->name ?? '—',
                    'Member Since' => $customer->created_at->format('F d, Y'),
                ] as $label => $value)
                    <div class="flex justify-between items-start border-b border-cream2 pb-3 last:border-0 last:pb-0">
                        <dt class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 pt-0.5">{{ $label }}</dt>
                        <dd class="text-[12.5px] text-ink2 text-right max-w-[60%]">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-card>

        {{-- Loyalty info --}}
        <x-card title="Loyalty Status">
            {{-- Tier badge --}}
            <div class="flex items-center gap-3 mb-5 p-4 bg-cream rounded-lg">
                <div class="w-12 h-12 rounded-full flex items-center justify-center font-serif font-black text-[18px]
                    {{ match($customer->tier) { 'platinum' => 'bg-terra/15 text-terra', 'gold' => 'bg-gold/15 text-gold', 'silver' => 'bg-navy/10 text-navy', default => 'bg-cream2 text-ink4' } }}">
                    {{ strtoupper(substr($customer->tier, 0, 1)) }}
                </div>
                <div>
                    <p class="font-serif text-[16px] font-bold text-ink">{{ ucfirst($customer->tier) }} Member</p>
                    <p class="font-mono text-[10px] text-ink4 mt-0.5">{{ number_format($customer->loyalty_points) }} points</p>
                </div>
            </div>

            {{-- Tier thresholds --}}
            @php
                $tiers = [
                    ['Bronze',   0,     1000,  'muted'],
                    ['Silver',   1000,  5000,  'navy'],
                    ['Gold',     5000,  10000, 'gold'],
                    ['Platinum', 10000, null,  'terra'],
                ];
            @endphp
            <div class="flex flex-col gap-3">
                @foreach($tiers as [$name, $min, $max, $variant])
                    @php $active = $customer->tier === strtolower($name); @endphp
                    <div class="flex items-center justify-between p-2.5 rounded-md {{ $active ? 'bg-cream border border-cream3' : '' }}">
                        <div class="flex items-center gap-2">
                            <x-badge :variant="$variant">{{ $name }}</x-badge>
                            <span class="font-mono text-[10px] text-ink4">
                                {{ number_format($min) }}{{ $max ? ' – ' . number_format($max) : '+' }} pts
                            </span>
                        </div>
                        @if($active)
                            <x-icon name="check-circle" class="w-4 h-4 text-ok" />
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Add points form --}}
            <div class="mt-4 pt-4 border-t border-cream2">
                <p class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 mb-2">Manually Add Points</p>
                <form method="POST" action="{{ route('customers.add-points', $customer) }}"
                      class="flex gap-2">
                    @csrf
                    <input type="number" name="points" min="1" placeholder="Enter points"
                           class="flex-1 bg-cream border border-cream3 rounded-md px-3 py-2 text-[12px] font-sans text-ink
                                  outline-none focus:border-terra transition-colors">
                    <x-btn type="submit" variant="primary" size="sm">Add</x-btn>
                </form>
            </div>
        </x-card>
    </div>

    {{-- Transaction history --}}
    <x-data-table title="Purchase History">
        <x-slot:head>
            <x-th>TXN Number</x-th>
            <x-th>Branch</x-th>
            <x-th>Items</x-th>
            <x-th>Payment</x-th>
            <x-th>Total</x-th>
            <x-th>Date</x-th>
            <x-th>Status</x-th>
        </x-slot:head>
        @forelse($customer->transactions()->latest('transaction_date')->paginate(10) as $txn)
            <tr class="hover:bg-cream transition-colors cursor-pointer"
                onclick="window.location='{{ route('sales.show', $txn) }}'">
                <x-td><span class="font-mono text-[11px] text-ink3">{{ $txn->transaction_number }}</span></x-td>
                <x-td>{{ $txn->branch->name ?? '—' }}</x-td>
                <x-td><span class="font-mono">{{ $txn->items->count() }}</span></x-td>
                <x-td>
                    <x-badge variant="{{ match($txn->payment_method) { 'gcash','maya' => 'navy', 'card' => 'terra', default => 'muted' } }}">
                        {{ ucfirst($txn->payment_method) }}
                    </x-badge>
                </x-td>
                <x-td><span class="font-mono font-medium">₱{{ number_format($txn->total_amount, 2) }}</span></x-td>
                <x-td><span class="font-mono text-[10px] text-ink4">{{ $txn->transaction_date->format('M d, Y · g:i A') }}</span></x-td>
                <x-td>
                    <x-badge variant="{{ $txn->status === 'completed' ? 'ok' : 'error' }}">
                        {{ ucfirst($txn->status) }}
                    </x-badge>
                </x-td>
            </tr>
        @empty
            <tr><td colspan="7"><x-empty-state title="No purchase history" icon="activity" /></td></tr>
        @endforelse
    </x-data-table>

</x-app-layout>
