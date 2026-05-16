<x-app-layout :title="$transfer->transfer_number">

    <x-page-header
        overline="Inter-branch · Transfer"
        :title="$transfer->transfer_number"
        :subtitle="$transfer->fromBranch->name . ' → ' . $transfer->toBranch->name"
    >
        <x-slot:actions>
            <x-btn :href="route('transfers.index')" variant="ghost" size="sm">← All Transfers</x-btn>

            @if($transfer->status === 'pending')
                @can('approve stock transfers')
                <form method="POST" action="{{ route('transfers.approve', $transfer->id) }}" class="inline">
                    @csrf
                    <x-btn type="submit" variant="primary" size="sm">Approve</x-btn>
                </form>
                <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'reject-transfer' }))"
                        class="inline-flex items-center px-3 py-[5px] text-[11px] font-medium text-danger/70 border border-danger/20 rounded-md hover:bg-danger/5 transition-colors">
                    Reject
                </button>
                @endcan
                @can('create stock transfers')
                <form method="POST" action="{{ route('transfers.cancel', $transfer->id) }}" class="inline">
                    @csrf
                    <x-btn type="submit" variant="danger" size="sm"
                           onclick="return confirm('Cancel this transfer?')">Cancel</x-btn>
                </form>
                @endcan

            @elseif($transfer->status === 'approved')
                @can('approve stock transfers')
                <form method="POST" action="{{ route('transfers.dispatch', $transfer->id) }}" class="inline">
                    @csrf
                    <x-btn type="submit" variant="ghost" size="sm">Mark Dispatched</x-btn>
                </form>
                @endcan
                @can('receive stock transfers')
                <form method="POST" action="{{ route('transfers.receive', $transfer->id) }}" class="inline">
                    @csrf
                    <x-btn type="submit" variant="primary" size="sm">Mark Received</x-btn>
                </form>
                @endcan
                @can('approve stock transfers')
                <form method="POST" action="{{ route('transfers.cancel', $transfer->id) }}" class="inline">
                    @csrf
                    <x-btn type="submit" variant="danger" size="sm"
                           onclick="return confirm('Cancel this approved transfer?')">Cancel</x-btn>
                </form>
                @endcan

            @elseif($transfer->status === 'in_transit')
                @can('receive stock transfers')
                <form method="POST" action="{{ route('transfers.receive', $transfer->id) }}" class="inline">
                    @csrf
                    <x-btn type="submit" variant="primary" size="sm">Mark Received</x-btn>
                </form>
                @endcan
            @endif
        </x-slot:actions>
    </x-page-header>

    @if($errors->has('transfer'))
        <x-alert type="error" :message="$errors->first('transfer')" class="mb-5" />
    @endif
    @if(session('success'))
        <x-alert type="success" :message="session('success')" class="mb-5" />
    @endif

    {{-- Reject modal --}}
    <x-modal name="reject-transfer" title="Reject Transfer" max-width="sm">
        <form method="POST" action="{{ route('transfers.reject', $transfer->id) }}">
            @csrf
            <x-form-input label="Reason for rejection" name="reason" required class="mb-4"
                          placeholder="Explain why this transfer is being rejected…" />
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-cream3">
                <x-btn type="button" variant="ghost" size="sm"
                       x-on:click="$dispatch('close-modal', 'reject-transfer')">Cancel</x-btn>
                <x-btn type="submit" variant="danger" size="sm">Confirm Reject</x-btn>
            </div>
        </form>
    </x-modal>

    {{-- Status timeline --}}
    <div class="bg-white border border-cream3 rounded-lg px-6 py-5 mb-5">
        <div class="flex items-center gap-0">
            @php
                $steps = [
                    'pending'    => 'Requested',
                    'approved'   => 'Approved',
                    'in_transit' => 'In Transit',
                    'received'   => 'Received',
                ];
                $order  = array_keys($steps);
                $curIdx = array_search($transfer->status, $order) ?? -1;
                if ($transfer->status === 'rejected') $curIdx = -1;
            @endphp
            @foreach($steps as $key => $label)
                @php
                    $idx    = array_search($key, $order);
                    $done   = $idx <= $curIdx;
                    $active = $idx === $curIdx;
                @endphp
                <div class="flex items-center {{ $loop->last ? '' : 'flex-1' }}">
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-[11px] font-bold
                            {{ $done ? 'bg-ok text-white' : 'bg-cream2 text-ink4' }}
                            {{ $active ? 'ring-2 ring-ok/30' : '' }}">
                            {{ $done ? '✓' : ($idx + 1) }}
                        </div>
                        <p class="font-mono text-[9px] uppercase tracking-wide mt-1.5
                            {{ $active ? 'text-ok font-medium' : 'text-ink4' }}">
                            {{ $label }}
                        </p>
                    </div>
                    @if(!$loop->last)
                        <div class="flex-1 h-0.5 mx-2 mb-4 {{ $idx < $curIdx ? 'bg-ok' : 'bg-cream3' }}"></div>
                    @endif
                </div>
            @endforeach
        </div>
        @if($transfer->status === 'rejected')
            <div class="mt-4 pt-4 border-t border-cream3">
                <p class="font-mono text-[9px] tracking-[0.14em] uppercase text-danger mb-1">Rejection Reason</p>
                <p class="text-[12.5px] text-ink3">{{ $transfer->rejection_reason }}</p>
            </div>
        @elseif($transfer->status === 'cancelled')
            <div class="mt-4 pt-4 border-t border-cream3">
                <p class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 mb-1">Cancelled</p>
                <p class="text-[12.5px] text-ink4">This transfer was cancelled. All reserved stock has been released.</p>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-2 gap-5 mb-5">

        {{-- Transfer details --}}
        <x-card title="Transfer Details">
            <dl class="flex flex-col gap-4">
                @foreach([
                    'Transfer Number' => $transfer->transfer_number,
                    'From Branch'     => $transfer->fromBranch->name,
                    'To Branch'       => $transfer->toBranch->name,
                    'Requested By'    => optional($transfer->requestedBy)->full_name ?? '—',
                    'Approved By'     => optional($transfer->approvedBy)->full_name ?? '—',
                    'Status'          => ucwords(str_replace('_', ' ', $transfer->status)),
                    'Requested At'    => $transfer->requested_at->format('F d, Y · g:i A'),
                    'Approved At'     => $transfer->approved_at?->format('F d, Y · g:i A') ?? '—',
                    'Completed At'    => $transfer->completed_at?->format('F d, Y · g:i A') ?? '—',
                ] as $label => $value)
                    <div class="flex justify-between items-start border-b border-cream2 pb-3 last:border-0 last:pb-0">
                        <dt class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 pt-0.5 flex-shrink-0">{{ $label }}</dt>
                        <dd class="text-[12.5px] text-ink2 text-right ml-4">
                            @if($label === 'Status')
                                <x-badge variant="{{ match($transfer->status) { 'pending' => 'warn', 'approved','in_transit' => 'navy', 'received' => 'ok', 'rejected' => 'error', default => 'muted' } }}">
                                    {{ $value }}
                                </x-badge>
                            @else
                                {{ $value }}
                            @endif
                        </dd>
                    </div>
                @endforeach
            </dl>
            @if($transfer->notes)
                <div class="mt-4 pt-4 border-t border-cream2">
                    <p class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 mb-1">Notes</p>
                    <p class="text-[12.5px] text-ink3">{{ $transfer->notes }}</p>
                </div>
            @endif
        </x-card>

        {{-- Summary --}}
        <x-card title="Transfer Summary">
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div class="bg-cream rounded-lg p-3 text-center">
                    <p class="font-mono text-[9px] uppercase tracking-wide text-ink4 mb-1">Products</p>
                    <p class="font-serif text-[24px] font-bold text-ink">{{ $transfer->items->count() }}</p>
                </div>
                <div class="bg-cream rounded-lg p-3 text-center">
                    <p class="font-mono text-[9px] uppercase tracking-wide text-ink4 mb-1">Total Units</p>
                    <p class="font-serif text-[24px] font-bold text-ink">{{ $transfer->items->sum('quantity_requested') }}</p>
                </div>
                <div class="bg-cream rounded-lg p-3 text-center">
                    <p class="font-mono text-[9px] uppercase tracking-wide text-ink4 mb-1">Units Sent</p>
                    <p class="font-serif text-[24px] font-bold {{ $transfer->status === 'received' ? 'text-ok' : 'text-ink4' }}">
                        {{ $transfer->items->sum('quantity_sent') }}
                    </p>
                </div>
                <div class="bg-cream rounded-lg p-3 text-center">
                    <p class="font-mono text-[9px] uppercase tracking-wide text-ink4 mb-1">Transfer Value</p>
                    <p class="font-serif text-[24px] font-bold text-terra">
                        ₱{{ number_format($transfer->items->sum(fn ($i) => $i->unit_cost * $i->quantity_requested)) }}
                    </p>
                </div>
            </div>
        </x-card>
    </div>

    {{-- Items --}}
    <x-data-table title="Transfer Items">
        <x-slot:head>
            <x-th>SKU</x-th>
            <x-th>Product</x-th>
            <x-th>Unit Cost</x-th>
            <x-th>Requested Qty</x-th>
            <x-th>Sent Qty</x-th>
            <x-th>Line Value</x-th>
        </x-slot:head>
        @foreach($transfer->items as $item)
            <tr class="hover:bg-cream transition-colors">
                <x-td><span class="font-mono text-[11px] text-ink3">{{ $item->product->sku ?? '—' }}</span></x-td>
                <x-td class="font-medium">{{ $item->product->name ?? '—' }}</x-td>
                <x-td><span class="font-mono">₱{{ number_format($item->unit_cost, 2) }}</span></x-td>
                <x-td><span class="font-mono font-medium">{{ $item->quantity_requested }}</span></x-td>
                <x-td>
                    <span class="font-mono {{ $item->quantity_sent >= $item->quantity_requested ? 'text-ok' : 'text-ink4' }}">
                        {{ $item->quantity_sent }}
                    </span>
                </x-td>
                <x-td><span class="font-mono">₱{{ number_format($item->unit_cost * $item->quantity_requested, 2) }}</span></x-td>
            </tr>
        @endforeach
    </x-data-table>

</x-app-layout>
