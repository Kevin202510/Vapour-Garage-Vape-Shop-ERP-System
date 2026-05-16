<x-app-layout title="Inventory">

    <x-page-header overline="Stock Control" title="Inventory"
                   :subtitle="$totalRecords . ' records · ' . $criticalCount . ' critical · ' . $lowCount . ' low'">
        <x-slot:actions>
            @can('adjust inventory')
            <x-btn variant="ghost" size="sm" x-on:click="$dispatch('open-modal', 'adjust-stock')">Adjust Stock</x-btn>
            @endcan
            @can('create stock transfers')
            <x-btn variant="primary" size="sm" x-on:click="$dispatch('open-modal', 'request-transfer')">
                <x-icon name="plus" class="w-3 h-3" /> Request Transfer
            </x-btn>
            @endcan
        </x-slot:actions>
    </x-page-header>

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
                                 style="width:{{ $branch->health_pct }}%;background:{{ $branch->health_pct >= 70 ? '#3a7a4a' : ($branch->health_pct >= 40 ? '#b8882a' : '#b83232') }}">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>

        {{-- Reorder alerts --}}
        <x-card title="Reorder Alerts" subtitle="IMMEDIATE ACTION REQUIRED">
            <div class="flex flex-col gap-2">
                @forelse($reorderAlerts as $alert)
                    <div class="flex items-center gap-3.5 px-3.5 py-3 rounded-md border-l-[3px]
                                {{ $alert->available <= 0 ? 'border-danger bg-danger/[0.04]' : 'border-warn bg-warn/[0.04]' }}">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0
                                    {{ $alert->available <= 0 ? 'bg-danger/10' : 'bg-warn/10' }}">
                            <x-icon name="alert-triangle"
                                    class="w-3.5 h-3.5 {{ $alert->available <= 0 ? 'text-danger' : 'text-warn' }}" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[12.5px] font-semibold text-ink truncate">{{ $alert->product_name }}</p>
                            <p class="font-mono text-[10px] text-ink4 mt-0.5">
                                {{ $alert->sku }} · {{ $alert->branch_name }} · {{ $alert->available }} of {{ $alert->reorder_level }} min
                            </p>
                        </div>
                        <x-btn :href="route('purchasing.create', ['product_id' => $alert->product_id])" variant="ghost" size="sm">
                            Order Now
                        </x-btn>
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
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
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
            <x-th>Status</x-th>
        </x-slot:head>

        @forelse($inventory as $item)
            @php $available = $item->quantity_on_hand - $item->quantity_reserved; @endphp
            <tr class="hover:bg-cream transition-colors">
                <x-td><span class="font-mono text-[11px] text-ink3">{{ $item->product->sku }}</span></x-td>
                <x-td class="font-medium">{{ $item->product->name }}</x-td>
                <x-td class="text-ink3">{{ $item->branch->name }}</x-td>
                <x-td><span class="font-mono">{{ $item->quantity_on_hand }}</span></x-td>
                <x-td><span class="font-mono text-ink4">{{ $item->quantity_reserved }}</span></x-td>
                <x-td><span class="font-mono font-medium">{{ $available }}</span></x-td>
                <x-td><span class="font-mono text-ink4">{{ $item->product->reorder_level }}</span></x-td>
                <x-td>
                    @if($available <= 0)
                        <x-badge variant="error">Critical</x-badge>
                    @elseif($available <= $item->product->reorder_level)
                        <x-badge variant="warn">Low</x-badge>
                    @else
                        <x-badge variant="ok">OK</x-badge>
                    @endif
                </x-td>
            </tr>
        @empty
            <tr><td colspan="8"><x-empty-state title="No inventory records" icon="list" /></td></tr>
        @endforelse

        <x-slot:pagination>{{ $inventory->withQueryString()->links('vendor.pagination.simple') }}</x-slot:pagination>
    </x-data-table>

</x-app-layout>
