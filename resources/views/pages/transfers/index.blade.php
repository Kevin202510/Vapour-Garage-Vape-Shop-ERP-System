<x-app-layout title="Stock Transfers">

    <x-page-header overline="Inter-branch" title="Stock Transfers"
                   :subtitle="$activeBranchLabel . ' · ' . $pendingCount . ' pending · ' . $inTransitCount . ' in transit'">
        <x-slot:actions>
            @can('create stock transfers')
            <x-btn variant="primary" size="sm" x-on:click="$dispatch('open-modal', 'request-transfer')">
                <x-icon name="plus" class="w-3 h-3" /> Request Transfer
            </x-btn>
            @endcan
        </x-slot:actions>
    </x-page-header>

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
            <a href="{{ route('transfers.index', array_merge(request()->only('status'))) }}" class="text-[11px] font-mono text-ink4 hover:text-ink">Clear</a>
        @endif
    </form>

    {{-- Status filter --}}
    <div class="flex gap-1 bg-cream border border-cream3 rounded-md p-[3px] mb-5 w-fit">
        @foreach(['all' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'in_transit' => 'In Transit', 'received' => 'Received', 'rejected' => 'Rejected'] as $val => $lbl)
            <a href="{{ route('transfers.index', array_merge(request()->query(), ['status' => $val])) }}"
               class="px-3.5 py-[5px] rounded text-[11px] font-mono tracking-wide transition-all
                      {{ request('status', 'all') === $val ? 'bg-white text-ink shadow-sm' : 'text-ink4 hover:text-ink' }}">
                {{ $lbl }}
            </a>
        @endforeach
    </div>

    {{-- Transfer cards --}}
    <div class="flex flex-col gap-3">
        @forelse($transfers as $transfer)
            <div class="bg-white border border-cream3 rounded-lg px-5 py-4 flex items-center gap-4
                        hover:shadow-card hover:border-cream2 transition-all cursor-pointer"
                 onclick="window.location='{{ route('transfers.show', $transfer) }}'">

                {{-- From branch --}}
                <div class="text-center min-w-[70px]">
                    <p class="font-serif text-[14px] font-bold text-ink">{{ $transfer->fromBranch->city ?? $transfer->fromBranch->name }}</p>
                    <p class="font-mono text-[9px] text-ink4 uppercase tracking-[0.1em] mt-0.5">Origin</p>
                </div>

                {{-- Arrow --}}
                <div class="text-ink4">
                    <x-icon name="arrow-right" class="w-5 h-5" />
                </div>

                {{-- To branch --}}
                <div class="text-center min-w-[70px]">
                    <p class="font-serif text-[14px] font-bold text-ink">{{ $transfer->toBranch->city ?? $transfer->toBranch->name }}</p>
                    <p class="font-mono text-[9px] text-ink4 uppercase tracking-[0.1em] mt-0.5">Destination</p>
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

                {{-- Status --}}
                <x-badge variant="{{ match($transfer->status) { 'pending' => 'warn', 'approved' => 'navy', 'in_transit' => 'navy', 'received' => 'ok', 'rejected' => 'error', default => 'muted' } }}">
                    {{ ucwords(str_replace('_', ' ', $transfer->status)) }}
                </x-badge>

                {{-- Actions --}}
                <div class="flex gap-2" @click.stop>
                    @if($transfer->status === 'pending' && auth()->user()->can('approve stock transfers'))
                        <form method="POST" action="{{ route('transfers.approve', $transfer) }}">
                            @csrf @method('POST')
                            <x-btn type="submit" variant="primary" size="sm">Approve</x-btn>
                        </form>
                        <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'reject-{{ $transfer->id }}' }))"
                                class="inline-flex items-center px-3 py-[5px] text-[11px] font-medium text-ink3 border border-cream3 rounded-md hover:bg-cream transition-colors">
                            Reject
                        </button>
                    @elseif(in_array($transfer->status, ['approved','in_transit']) && auth()->user()->can('receive stock transfers'))
                        <form method="POST" action="{{ route('transfers.receive', $transfer) }}">
                            @csrf @method('POST')
                            <x-btn type="submit" variant="primary" size="sm">Receive</x-btn>
                        </form>
                    @else
                        <x-btn :href="route('transfers.show', $transfer)" variant="ghost" size="sm">View</x-btn>
                    @endif
                </div>
            </div>

            {{-- Reject modal per transfer --}}
            @if($transfer->status === 'pending')
                <x-modal :name="'reject-' . $transfer->id" title="Reject Transfer" max-width="sm">
                    <form method="POST" action="{{ route('transfers.reject', $transfer) }}">
                        @csrf @method('POST')
                        <x-form-input label="Reason for rejection" name="reason" required class="mb-4"
                                      placeholder="Explain why this transfer is being rejected…" />
                        <x-slot:footer>
                            <x-btn type="button" variant="ghost" size="sm"
                                   x-on:click="$dispatch('close-modal', 'reject-{{ $transfer->id }}')">Cancel</x-btn>
                            <x-btn type="submit" variant="danger" size="sm">Confirm Reject</x-btn>
                        </x-slot:footer>
                    </form>
                </x-modal>
            @endif
        @empty
            <x-empty-state title="No transfers found" description="No stock transfers match the selected status." icon="repeat" />
        @endforelse
    </div>

    @if($transfers instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="mt-5">{{ $transfers->withQueryString()->links('vendor.pagination.simple') }}</div>
    @endif

    {{-- Request Transfer Modal --}}
    <x-modal name="request-transfer" title="Request Stock Transfer" max-width="lg">
        <form method="POST" action="{{ route('transfers.store') }}" id="transfer-form" x-data="{ items: [{ product_id: '', qty: 1 }] }">
            @csrf
            <div class="grid grid-cols-2 gap-4 mb-4">
                <x-form-select label="From Branch" name="from_branch_id" required>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ auth()->user()->branch_id == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </x-form-select>
                <x-form-select label="To Branch" name="to_branch_id" required>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </x-form-select>
            </div>

            <p class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 mb-2">Items to Transfer</p>
            <div class="flex flex-col gap-2 mb-3">
                <template x-for="(item, i) in items" :key="i">
                    <div class="flex gap-2 items-start">
                        <select :name="'items[' + i + '][product_id]'" required
                                class="flex-1 bg-cream border border-cream3 rounded-md px-3 py-2 text-[12px] font-sans text-ink outline-none focus:border-terra transition-colors">
                            <option value="">Select product…</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                            @endforeach
                        </select>
                        <input :name="'items[' + i + '][quantity_requested]'" type="number" min="1" x-model="item.qty"
                               class="w-20 bg-cream border border-cream3 rounded-md px-3 py-2 text-[12px] font-sans text-ink outline-none focus:border-terra transition-colors"
                               placeholder="Qty">
                        <button type="button" @click="items.splice(i, 1)" x-show="items.length > 1"
                                class="mt-2 text-ink4 hover:text-danger transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>
                </template>
            </div>
            <button type="button" @click="items.push({ product_id: '', qty: 1 })"
                    class="text-[11px] font-medium text-terra hover:underline mb-4">+ Add item</button>

            <x-form-input label="Notes (optional)" name="notes" placeholder="Reason for transfer…" />

            <x-slot:footer>
                <x-btn type="button" variant="ghost" size="sm" x-on:click="$dispatch('close-modal', 'request-transfer')">Cancel</x-btn>
                <x-btn type="submit" variant="primary" size="sm">Submit Request</x-btn>
            </x-slot:footer>
        </form>
    </x-modal>

</x-app-layout>
