<x-app-layout title="Stock Transfers">

    <x-page-header overline="Inter-branch" title="Stock Transfers"
                   :subtitle="$activeBranchLabel . ' · ' . $pendingCount . ' pending · ' . $inTransitCount . ' in transit'">
        <x-slot:actions>
            @can('view stock transfers')
            <x-btn :href="route('transfers.export', request()->query())" variant="ghost" size="sm">
                <x-icon name="download" class="w-3 h-3" /> Export CSV
            </x-btn>
            @endcan
            @can('create stock transfers')
            <x-btn variant="primary" size="sm" x-on:click="$dispatch('open-modal', 'request-transfer')">
                <x-icon name="plus" class="w-3 h-3" /> Request Transfer
            </x-btn>
            @endcan
        </x-slot:actions>
    </x-page-header>

    @if($errors->has('transfer'))
        <x-alert type="error" :message="$errors->first('transfer')" class="mb-5" />
    @endif
    @if(session('success'))
        <x-alert type="success" :message="session('success')" class="mb-5" />
    @endif

    {{-- Search & date filters --}}
    <form method="GET" action="{{ route('transfers.index') }}" class="flex items-center gap-3 flex-wrap mb-4">
        <x-form-input name="search" type="text" label="" placeholder="Search transfer #…" :value="request('search')" class="min-w-[180px]" />
        @if(auth()->user()->isSuperAdmin())
        <x-form-select name="branch_id" label="" placeholder="All Branches" class="min-w-[160px]">
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}" {{ $effectiveBranchId == $branch->id ? 'selected' : '' }}>
                    {{ $branch->name }}
                </option>
            @endforeach
        </x-form-select>
        @endif
        <x-form-input name="date_from" type="date" label="" :value="request('date_from')" class="min-w-[130px]" />
        <x-form-input name="date_to"   type="date" label="" :value="request('date_to')"   class="min-w-[130px]" />
        <x-btn type="submit" variant="ghost" size="sm">Apply</x-btn>
        @if(request()->hasAny(['search','branch_id','date_from','date_to']))
            <a href="{{ route('transfers.index', array_merge(request()->only('status'))) }}"
               class="text-[11px] font-mono text-ink4 hover:text-ink">Clear</a>
        @endif
    </form>

    {{-- Status tabs --}}
    <div class="flex gap-1 bg-cream border border-cream3 rounded-md p-[3px] mb-5 w-fit">
        @foreach(['all' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'in_transit' => 'In Transit', 'received' => 'Received', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled'] as $val => $lbl)
            <a href="{{ route('transfers.index', array_merge(request()->query(), ['status' => $val])) }}"
               class="px-3.5 py-[5px] rounded text-[11px] font-mono tracking-wide transition-all
                      {{ request('status', 'all') === $val ? 'bg-white text-ink shadow-sm' : 'text-ink4 hover:text-ink' }}">
                {{ $lbl }}
            </a>
        @endforeach
    </div>

    {{-- Transfer list --}}
    <div class="flex flex-col gap-3">
        @forelse($transfers as $transfer)
            <div class="bg-white border border-cream3 rounded-lg px-5 py-4 flex items-center gap-4
                        hover:shadow-card hover:border-cream2 transition-all cursor-pointer"
                 onclick="window.location='{{ route('transfers.show', $transfer) }}'">

                {{-- From branch --}}
                <div class="text-center min-w-[80px]">
                    <p class="font-display text-[13px] font-bold text-ink leading-tight">
                        {{ Str::limit($transfer->fromBranch->city ?? $transfer->fromBranch->name, 12) }}
                    </p>
                    <p class="font-mono text-[8px] text-ink4 uppercase tracking-[0.1em] mt-0.5">Origin</p>
                </div>

                {{-- Arrow --}}
                <div class="text-ink4 flex-shrink-0">
                    <x-icon name="arrow-right" class="w-5 h-5" />
                </div>

                {{-- To branch --}}
                <div class="text-center min-w-[80px]">
                    <p class="font-display text-[13px] font-bold text-ink leading-tight">
                        {{ Str::limit($transfer->toBranch->city ?? $transfer->toBranch->name, 12) }}
                    </p>
                    <p class="font-mono text-[8px] text-ink4 uppercase tracking-[0.1em] mt-0.5">Destination</p>
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <p class="text-[12.5px] font-medium text-ink">
                        {{ $transfer->items->count() }} product{{ $transfer->items->count() !== 1 ? 's' : '' }} ·
                        {{ $transfer->items->sum('quantity_requested') }} units total
                    </p>
                    <p class="font-mono text-[10px] text-ink4 mt-0.5">
                        {{ $transfer->transfer_number }} · {{ $transfer->requested_at->diffForHumans() }}
                    </p>
                </div>

                {{-- Status badge --}}
                <x-badge variant="{{ match($transfer->status) {
                    'pending'    => 'warn',
                    'approved'   => 'navy',
                    'in_transit' => 'purple',
                    'received'   => 'ok',
                    'rejected'   => 'error',
                    'cancelled'  => 'muted',
                    default      => 'muted'
                } }}">
                    {{ ucwords(str_replace('_', ' ', $transfer->status)) }}
                </x-badge>

                {{-- Actions --}}
                <div class="flex gap-2 flex-shrink-0" @click.stop>

                    {{-- View --}}
                    <x-btn :href="route('transfers.show', $transfer)" variant="ghost" size="sm">View</x-btn>

                    @if($transfer->status === 'pending')
                        @can('approve stock transfers')
                        <form method="POST" action="{{ route('transfers.approve', $transfer) }}">
                            @csrf
                            <x-btn type="submit" variant="primary" size="sm">Approve</x-btn>
                        </form>
                        <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'reject-{{ $transfer->id }}' }))"
                                class="inline-flex items-center px-3 py-[5px] text-[11px] font-medium text-ink3 border border-cream3 rounded-md hover:bg-cream transition-colors">
                            Reject
                        </button>
                        @endcan
                        @can('create stock transfers')
                        <form method="POST" action="{{ route('transfers.cancel', $transfer) }}">
                            @csrf
                            <x-btn type="submit" variant="danger" size="sm"
                                   onclick="return confirm('Cancel this transfer request?')">Cancel</x-btn>
                        </form>
                        @endcan

                    @elseif($transfer->status === 'approved')
                        @can('approve stock transfers')
                        <form method="POST" action="{{ route('transfers.dispatch', $transfer) }}">
                            @csrf
                            <x-btn type="submit" variant="ghost" size="sm">Mark Dispatched</x-btn>
                        </form>
                        @endcan
                        @can('receive stock transfers')
                        <form method="POST" action="{{ route('transfers.receive', $transfer) }}">
                            @csrf
                            <x-btn type="submit" variant="primary" size="sm">Receive</x-btn>
                        </form>
                        @endcan
                        @can('approve stock transfers')
                        <form method="POST" action="{{ route('transfers.cancel', $transfer) }}">
                            @csrf
                            <x-btn type="submit" variant="danger" size="sm"
                                   onclick="return confirm('Cancel this approved transfer?')">Cancel</x-btn>
                        </form>
                        @endcan

                    @elseif($transfer->status === 'in_transit')
                        @can('receive stock transfers')
                        <form method="POST" action="{{ route('transfers.receive', $transfer) }}">
                            @csrf
                            <x-btn type="submit" variant="primary" size="sm">Mark Received</x-btn>
                        </form>
                        @endcan
                    @endif
                </div>
            </div>

            {{-- Reject modal per pending transfer --}}
            @if($transfer->status === 'pending')
                <x-modal :name="'reject-' . $transfer->id" title="Reject Transfer" max-width="sm">
                    <form method="POST" action="{{ route('transfers.reject', $transfer) }}">
                        @csrf
                        <x-form-input label="Reason for rejection" name="reason" required class="mb-4"
                                      placeholder="Explain why this transfer is being rejected…" />
                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-cream3">
                            <x-btn type="button" variant="ghost" size="sm"
                                   x-on:click="$dispatch('close-modal', 'reject-{{ $transfer->id }}')">Cancel</x-btn>
                            <x-btn type="submit" variant="danger" size="sm">Confirm Reject</x-btn>
                        </div>
                    </form>
                </x-modal>
            @endif
        @empty
            <x-empty-state title="No transfers found"
                           description="No stock transfers match the selected filters."
                           icon="repeat" />
        @endforelse
    </div>

    @if($transfers instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="mt-5">{{ $transfers->withQueryString()->links('vendor.pagination.simple') }}</div>
    @endif

    {{-- Request Transfer Modal --}}
    @can('create stock transfers')
    <x-modal name="request-transfer" title="Request Stock Transfer" max-width="lg">
        <form method="POST" action="{{ route('transfers.store') }}"
              x-data="{ items: [{ product_id: '', qty: 1 }] }">
            @csrf

            @if($errors->has('transfer'))
                <div class="mb-4 px-3 py-2 bg-danger/5 border border-danger/20 rounded text-[12px] text-danger font-mono">
                    {{ $errors->first('transfer') }}
                </div>
            @endif

            <div class="grid grid-cols-2 gap-4 mb-4">
                <x-form-select label="From Branch" name="from_branch_id" required>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}"
                            {{ (old('from_branch_id') ?? auth()->user()->activeBranchId()) == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </x-form-select>
                <x-form-select label="To Branch" name="to_branch_id" required>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('to_branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </x-form-select>
            </div>

            <p class="erp-label mb-2">Items to Transfer</p>
            <div class="flex flex-col gap-2 mb-3">
                <template x-for="(item, i) in items" :key="i">
                    <div class="flex gap-2 items-center">
                        <select :name="'items[' + i + '][product_id]'" required
                                class="erp-select flex-1 text-[12px]">
                            <option value="">Select product…</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">
                                    {{ $product->name }} ({{ $product->sku }})
                                    @if($product->category) · {{ $product->category->name }} @endif
                                </option>
                            @endforeach
                        </select>
                        <div class="flex items-center gap-1 flex-shrink-0">
                            <span class="font-mono text-[10px] text-ink4">Qty:</span>
                            <input :name="'items[' + i + '][quantity_requested]'"
                                   type="number" min="1" x-model="item.qty"
                                   class="erp-input w-20 text-center text-[13px]">
                        </div>
                        <button type="button" @click="items.splice(i, 1)" x-show="items.length > 1"
                                class="text-ink4 hover:text-danger transition-colors flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>

            <button type="button" @click="items.push({ product_id: '', qty: 1 })"
                    class="text-[11px] font-medium text-purple hover:underline mb-4">
                + Add another item
            </button>

            <x-form-input label="Notes (optional)" name="notes"
                          placeholder="Reason for transfer, urgency, etc." />

            {{-- Buttons INSIDE the form so type="submit" actually submits --}}
            <div class="flex items-center justify-end gap-3 pt-4 mt-2 border-t border-cream3">
                <x-btn type="button" variant="ghost" size="sm"
                       x-on:click="$dispatch('close-modal', 'request-transfer')">Cancel</x-btn>
                <x-btn type="submit" variant="primary" size="sm">Submit Request</x-btn>
            </div>
        </form>
    </x-modal>
    @endcan

</x-app-layout>
