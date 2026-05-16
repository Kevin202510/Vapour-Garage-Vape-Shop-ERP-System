<x-app-layout title="Branches">

    <x-page-header overline="Network" title="Our Branches"
                   :subtitle="$branches->count() . ' locations · All systems operational'">
        <x-slot:actions>
            @can('create branches')
            <x-btn variant="primary" size="sm" :href="route('branches.create')">
                <x-icon name="plus" class="w-3 h-3" /> Add Branch
            </x-btn>
            @endcan
        </x-slot:actions>
    </x-page-header>

    @php
    $palette = [
        ['hex' => '#7c22d4'],   // purple  (brand)
        ['hex' => '#16a34a'],   // green   (ok)
        ['hex' => '#d97706'],   // amber   (warn/gold)
        ['hex' => '#0891b2'],   // cyan    (info)
    ];
    @endphp

    <div class="grid grid-cols-2 gap-4 mb-6">
        @foreach($branches as $i => $branch)
            @php $p = $palette[$i % 4]; @endphp
            <div class="bg-white border border-cream3 rounded-lg overflow-hidden
                        hover:shadow-card hover:-translate-y-0.5 transition-all duration-200 cursor-pointer"
                 onclick="window.location='{{ route('branches.show', $branch) }}'">

                {{-- Colour accent stripe --}}
                <div class="h-1" style="background:{{ $p['hex'] }}"></div>

                <div class="px-5 py-5">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="font-display text-[15px] text-ink font-bold tracking-wide">{{ $branch->name }}</h3>
                            <p class="font-mono text-[10px] text-ink4 mt-0.5 uppercase tracking-[0.04em]">
                                {{ $branch->address }}, {{ $branch->city }}
                            </p>
                        </div>
                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            <span class="w-2 h-2 rounded-full {{ $branch->is_active ? 'bg-ok' : 'bg-danger' }}"></span>
                            <span class="font-mono text-[9px] {{ $branch->is_active ? 'text-ok' : 'text-danger' }} uppercase tracking-wide">
                                {{ $branch->is_active ? 'Live' : 'Inactive' }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-1 border-t border-cream3 pt-4">
                        <div class="py-1">
                            <p class="font-mono text-[8px] tracking-[0.14em] uppercase text-ink4 mb-1.5">Revenue Today</p>
                            <p class="font-display text-[18px] font-bold" style="color:{{ $p['hex'] }}">
                                ₱{{ number_format($branch->today_revenue ?? 0) }}
                            </p>
                        </div>
                        <div class="py-1">
                            <p class="font-mono text-[8px] tracking-[0.14em] uppercase text-ink4 mb-1.5">Active Staff</p>
                            <p class="font-display text-[18px] font-bold text-ink">
                                {{ $branch->employees_count ?? 0 }}
                            </p>
                        </div>
                        <div class="py-1">
                            <p class="font-mono text-[8px] tracking-[0.14em] uppercase text-ink4 mb-1.5">Transactions</p>
                            <p class="font-display text-[18px] font-bold text-ink">
                                {{ $branch->today_txn_count ?? 0 }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

</x-app-layout>
