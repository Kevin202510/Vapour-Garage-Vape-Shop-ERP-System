<x-app-layout title="Inventory">

<div x-data="{
    adjustOpen: false,
    adj: { branch_id: null, branch_name: '', product_id: null, product_name: '', sku: '', current_qty: 0 },
    openAdjust(data) { this.adj = data; this.adjustOpen = true; },
    closeAdjust() { this.adjustOpen = false; }
}" @keydown.escape.window="adjustOpen && closeAdjust()">

    <x-page-header overline="Stock Control" title="Inventory"
                   :subtitle="$activeBranchLabel . ' · ' . $totalRecords . ' records · ' . $criticalCount . ' critical · ' . $lowCount . ' low'">
        <x-slot:actions>
            @can('adjust inventory')
            <x-btn variant="ghost" size="sm" @click="openAdjust({ branch_id: {{ auth()->user()->branch_id ?? 'null' }}, branch_name: '{{ auth()->user()->branch?->name }}', product_id: null, product_name: '', sku: '', current_qty: 0 })">
                Adjust Stock
            </x-btn>
            @endcan
            @can('create stock transfers')
            <x-btn variant="primary" size="sm" x-on:click="$dispatch('open-modal', 'request-transfer')">
                <x-icon name="plus" class="w-3 h-3" /> Request Transfer
            </x-btn>
            @endcan
        </x-slot:actions>
    </x-page-header>

    {{-- Flash errors from adjust --}}
    @if($errors->has('adjust'))
        <x-alert type="error" :message="$errors->first('adjust')" class="mb-5" />
    @endif

    <div class="grid grid-cols-2 gap-4 mb-5">

        {{-- Branch health --}}
        <x-card title="Branch Health" subtitle="STOCK COVERAGE VS. REORDER LEVELS">
            <div class="flex flex-col gap-3.5">
                @foreach($branchHealth as $branch)
                <div class="flex flex-col gap-1.5">
                    <div class="flex justify-between items-center">
                        <span class="text-[12px] font-medium text-ink2">{{ $branch->name }}</span>
                        <span class="font-mono text-[10px] text-ink4">{{ $branch->health_pct }}%</span>
                    </div>
                    <div class="h-[5px] bg-cream2 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-700"
                             style="width:{{ $branch->health_pct }}%;background:{{ $branch->health_pct >= 70 ? 'var(--ok)' : ($branch->health_pct >= 40 ? 'var(--warn)' : 'var(--danger)') }}">
                        </div>
                    </div>
                </div>
                @endforeach
                @if($branchHealth->isEmpty())
                    <x-empty-state title="No branches" icon="home" />
                @endif
            </div>
        </x-card>

        {{-- Reorder alerts --}}
        <x-card title="Reorder Alerts" subtitle="IMMEDIATE ACTION REQUIRED">
            <div class="flex flex-col gap-2">
                @forelse($reorderAlerts as $alert)
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-3.5 px-3.5 py-3 rounded-md border-l-[3px]
                            {{ $alert->available <= 0 ? 'border-danger bg-danger/[0.04]' : 'border-warn bg-warn/[0.04]' }}">
                    <div class="flex items-center gap-3.5 flex-1 min-w-0">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0
                                    {{ $alert->available <= 0 ? 'bg-danger/10' : 'bg-warn/10' }}">
                            <x-icon name="alert-triangle" class="w-3.5 h-3.5 {{ $alert->available <= 0 ? 'text-danger' : 'text-warn' }}" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[12.5px] font-semibold text-ink truncate" title="{{ $alert->product_name }}">{{ $alert->product_name }}</p>
                            <p class="font-mono text-[10px] text-ink4 mt-0.5">
                                {{ $alert->sku }} · {{ $alert->branch_name }} · {{ $alert->available }} / {{ $alert->reorder_level }} min
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-2 flex-shrink-0 pl-[calc(1.75rem+0.875rem)] sm:pl-0">
                        @can('adjust inventory')
                        <x-btn variant="ghost" size="sm"
                            @click="openAdjust({ branch_id: {{ $alert->branch_id }}, branch_name: '{{ addslashes($alert->branch_name) }}', product_id: {{ $alert->product_id }}, product_name: '{{ addslashes($alert->product_name) }}', sku: '{{ $alert->sku }}', current_qty: {{ $alert->quantity_on_hand }} })">
                            Adjust
                        </x-btn>
                        @endcan
                        <x-btn :href="route('purchasing.create', ['product_id' => $alert->product_id])" variant="ghost" size="sm">
                            Order Now
                        </x-btn>
                    </div>
                </div>
                @empty
                    <x-empty-state title="No alerts" description="All stock levels are healthy." icon="check-circle" />
                @endforelse
            </div>
        </x-card>
    </div>

    {{-- Inventory table --}}
    <x-data-table title="Stock by Branch">
        <x-slot:tableHeader>
            <form method="GET" action="{{ route('inventory.index') }}" class="flex gap-2 items-center">
                <select name="branch_id" onchange="this.form.submit()"
                        class="bg-cream border border-cream3 text-ink2 text-[12px] px-3 py-[5px] rounded-md outline-none focus:border-terra font-sans">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $effectiveBranchId == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Product name or SKU…"
                       class="bg-cream border border-cream3 text-ink2 text-[12px] px-3 py-[5px] rounded-md outline-none focus:border-terra font-sans min-w-[200px]">
                <x-btn type="submit" variant="ghost" size="sm">Search</x-btn>
                @if(request()->hasAny(['search','branch_id']))
                    <a href="{{ route('inventory.index') }}" class="text-[11px] font-mono text-ink4 hover:text-ink">Clear</a>
                @endif
            </form>
        </x-slot:tableHeader>
        <x-slot:head>
            <x-th>SKU</x-th>
            <x-th>Product</x-th>
            <x-th>Branch</x-th>
            <x-th>On Hand</x-th>
            <x-th>Reserved</x-th>
            <x-th>Available</x-th>
            <x-th>Reorder Lvl</x-th>
            <x-th>Last Updated</x-th>
            <x-th>Status</x-th>
            @can('adjust inventory')<x-th></x-th>@endcan
        </x-slot:head>

        @forelse($inventory as $item)
            @php
                $available   = $item->quantity_on_hand - $item->quantity_reserved;
                $product     = $item->product;
                if (! $product) continue; // guard against any remaining orphan rows
            @endphp
            <tr class="hover:bg-cream transition-colors">
                <x-td><span class="font-mono text-[11px] text-ink3">{{ $product->sku }}</span></x-td>
                <x-td class="font-medium text-ink">{{ $product->name }}</x-td>
                <x-td class="text-ink3">{{ $item->branch->name }}</x-td>
                <x-td><span class="font-mono font-semibold text-ink">{{ $item->quantity_on_hand }}</span></x-td>
                <x-td><span class="font-mono text-ink4">{{ $item->quantity_reserved }}</span></x-td>
                <x-td>
                    <span class="font-mono font-semibold {{ $available <= 0 ? 'text-danger' : ($available <= $product->reorder_level ? 'text-warn' : 'text-ok') }}">
                        {{ $available }}
                    </span>
                </x-td>
                <x-td><span class="font-mono text-ink4">{{ $product->reorder_level }}</span></x-td>
                <x-td>
                    <span class="font-mono text-[10px] text-ink4">
                        {{ $item->last_updated ? $item->last_updated->diffForHumans() : '—' }}
                    </span>
                </x-td>
                <x-td>
                    @if($available <= 0)
                        <x-badge variant="error">Critical</x-badge>
                    @elseif($available <= $product->reorder_level)
                        <x-badge variant="warn">Low</x-badge>
                    @else
                        <x-badge variant="ok">OK</x-badge>
                    @endif
                </x-td>
                @can('adjust inventory')
                <x-td>
                    <x-btn variant="ghost" size="sm"
                        @click="openAdjust({ branch_id: {{ $item->branch_id }}, branch_name: '{{ addslashes($item->branch->name) }}', product_id: {{ $product->id }}, product_name: '{{ addslashes($product->name) }}', sku: '{{ $product->sku }}', current_qty: {{ $item->quantity_on_hand }} })">
                        Adjust
                    </x-btn>
                </x-td>
                @endcan
            </tr>
        @empty
            <tr>
                <td colspan="10">
                    <x-empty-state title="No inventory records" description="Add a product to automatically create inventory rows." icon="list" />
                </td>
            </tr>
        @endforelse

        <x-slot:pagination>{{ $inventory->withQueryString()->links('vendor.pagination.simple') }}</x-slot:pagination>
    </x-data-table>

    {{-- ═══════════════════════════════════════════════════════
         ADJUST STOCK MODAL (custom Alpine, not x-modal)
    ═══════════════════════════════════════════════════════ --}}
    <div x-show="adjustOpen" x-cloak
         class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-ink/40 backdrop-blur-sm"
         @click.self="closeAdjust()">
        <div class="bg-white rounded-xl border border-cream3 shadow-card w-full max-w-md overflow-hidden"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             @click.stop>

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-cream3 bg-cream">
                <div>
                    <h2 class="font-display text-[13px] tracking-wide text-ink uppercase">Adjust Stock</h2>
                    <p class="font-mono text-[10px] text-ink4 mt-0.5" x-text="adj.product_name ? adj.product_name + ' · ' + adj.branch_name : 'Select product and branch'"></p>
                </div>
                <button @click="closeAdjust()" class="text-ink4 hover:text-ink transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            {{-- Body --}}
            <form method="POST" action="{{ route('inventory.adjust') }}" class="p-6 flex flex-col gap-4">
                @csrf

                <input type="hidden" name="branch_id" :value="adj.branch_id">
                <input type="hidden" name="product_id" :value="adj.product_id">

                {{-- Branch selector (shown when no branch pre-selected, admin only) --}}
                @if(auth()->user()->isSuperAdmin())
                <div x-show="!adj.branch_id || adj.branch_id === 'null'">
                    <label class="erp-label">Branch</label>
                    <select class="erp-select" @change="adj.branch_id = $event.target.value; adj.branch_name = $event.target.options[$event.target.selectedIndex].text">
                        <option value="">Select branch…</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div x-show="adj.branch_id && adj.branch_id !== 'null'" class="hidden" x-cloak>
                    <label class="erp-label">Branch</label>
                    <p class="text-[13px] font-semibold text-ink" x-text="adj.branch_name"></p>
                </div>
                @else
                <div>
                    <label class="erp-label">Branch</label>
                    <p class="text-[13px] font-semibold text-ink">{{ auth()->user()->branch?->name ?? '—' }}</p>
                </div>
                @endif

                {{-- Product selector (shown when no product pre-selected) --}}
                <div x-show="!adj.product_id">
                    <label class="erp-label">Product</label>
                    <select class="erp-select" @change="adj.product_id = $event.target.value; adj.product_name = $event.target.options[$event.target.selectedIndex].text">
                        <option value="">Select product…</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                        @endforeach
                    </select>
                </div>
                <div x-show="adj.product_id" x-cloak>
                    <label class="erp-label">Product</label>
                    <p class="text-[13px] font-semibold text-ink" x-text="adj.product_name"></p>
                    <p class="font-mono text-[10px] text-ink4 mt-0.5" x-text="adj.sku"></p>
                </div>

                {{-- Current quantity display --}}
                <div x-show="adj.product_id" x-cloak class="flex items-center gap-3 px-4 py-3 bg-cream rounded-lg border border-cream3">
                    <div>
                        <p class="font-mono text-[9px] tracking-widest uppercase text-ink4">Current On-Hand</p>
                        <p class="font-display text-2xl text-ink mt-0.5" x-text="adj.current_qty"></p>
                    </div>
                </div>

                {{-- New quantity --}}
                <div>
                    <label class="erp-label" for="adj_qty">New Quantity <span class="erp-required">*</span></label>
                    <input id="adj_qty" type="number" name="quantity" min="0" required
                           class="erp-input" placeholder="Enter the corrected on-hand quantity">
                    <p class="erp-field-help">Set the actual physical count. This replaces the current value.</p>
                </div>

                {{-- Reason --}}
                <div>
                    <label class="erp-label" for="adj_reason">Reason <span class="erp-required">*</span></label>
                    <input id="adj_reason" type="text" name="reason" required
                           class="erp-input" placeholder="e.g. Physical count, damaged goods, shrinkage…">
                </div>

                {{-- Footer --}}
                <div class="flex justify-end gap-3 pt-2 border-t border-cream3">
                    <x-btn type="button" variant="ghost" size="sm" @click="closeAdjust()">Cancel</x-btn>
                    <x-btn type="submit" variant="primary" size="sm">Save Adjustment</x-btn>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         REQUEST TRANSFER MODAL
    ═══════════════════════════════════════════════════════ --}}
    @can('create stock transfers')
    <x-modal name="request-transfer" title="Request Stock Transfer" max-width="lg">
        <form method="POST" action="{{ route('transfers.store') }}" x-data="{ items: [{ product_id: '', qty: 1 }] }">
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

            <p class="erp-label mb-2">Items to Transfer</p>
            <div class="flex flex-col gap-2 mb-3">
                <template x-for="(item, i) in items" :key="i">
                    <div class="flex gap-2 items-start">
                        <select :name="'items[' + i + '][product_id]'" required class="erp-select flex-1">
                            <option value="">Select product…</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                            @endforeach
                        </select>
                        <input :name="'items[' + i + '][quantity_requested]'" type="number" min="1" x-model="item.qty"
                               class="erp-input w-20" placeholder="Qty">
                        <button type="button" @click="items.splice(i, 1)" x-show="items.length > 1"
                                class="mt-1 text-ink4 hover:text-danger transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>
            <button type="button" @click="items.push({ product_id: '', qty: 1 })"
                    class="text-[11px] font-medium text-purple hover:underline mb-4">+ Add item</button>

            <x-form-input label="Notes (optional)" name="notes" placeholder="Reason for transfer…" />

            <div class="flex items-center justify-end gap-3 pt-3 mt-2 border-t border-cream3">
                <x-btn type="button" variant="ghost" size="sm" x-on:click="$dispatch('close-modal', 'request-transfer')">Cancel</x-btn>
                <x-btn type="submit" variant="primary" size="sm">Submit Request</x-btn>
            </div>
        </form>
    </x-modal>
    @endcan

</div>{{-- end x-data="inventoryPage" --}}

</x-app-layout>
