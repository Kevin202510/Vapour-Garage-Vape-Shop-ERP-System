<x-app-layout title="Branches">

    <x-page-header overline="Network" title="Our Branches"
                   subtitle="{{ $branches->count() }} locations · Metro Manila · All systems operational">
        <x-slot:actions>
            @can('create branches')
            <x-btn variant="primary" size="sm" :href="route('branches.create')">
                <x-icon name="plus" class="w-3 h-3" /> Add Branch
            </x-btn>
            @endcan
        </x-slot:actions>
    </x-page-header>

    @php $stripeColors = ['bg-terra','bg-navy','bg-gold','bg-sage']; @endphp

    <div class="grid grid-cols-2 gap-4 mb-6">
        @foreach($branches as $i => $branch)
            <div class="bg-white border border-cream3 rounded-lg overflow-hidden
                        hover:shadow-card hover:-translate-y-0.5 transition-all duration-200 cursor-pointer"
                 onclick="window.location='{{ route('branches.show', $branch) }}'">
                <div class="h-1 {{ $stripeColors[$i % 4] }}"></div>
                <div class="px-5 py-5">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="font-serif text-[16px] font-bold text-ink">{{ $branch->name }}</h3>
                            <p class="font-mono text-[10px] text-ink4 tracking-[0.04em] mt-0.5 uppercase">
                                {{ $branch->address }}, {{ $branch->city }}
                            </p>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full {{ $branch->is_active ? 'bg-ok' : 'bg-danger' }}"
                                  style="animation: {{ $branch->is_active ? 'pulse 2s infinite' : 'none' }}"></span>
                            <span class="font-mono text-[9px] {{ $branch->is_active ? 'text-ok' : 'text-danger' }}">
                                {{ $branch->is_active ? 'Live' : 'Inactive' }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-1 border-t border-cream2 pt-3.5">
                        <div class="py-2">
                            <p class="font-mono text-[8px] tracking-[0.14em] uppercase text-ink4 mb-1">Revenue Today</p>
                            <p class="font-serif text-[18px] font-bold {{ $stripeColors[$i % 4] === 'bg-terra' ? 'text-terra' : ($stripeColors[$i % 4] === 'bg-navy' ? 'text-navy' : ($stripeColors[$i % 4] === 'bg-gold' ? 'text-gold' : 'text-sage')) }}">
                                ₱{{ number_format($branch->today_revenue ?? 0) }}
                            </p>
                        </div>
                        <div class="py-2">
                            <p class="font-mono text-[8px] tracking-[0.14em] uppercase text-ink4 mb-1">Staff</p>
                            <p class="font-serif text-[18px] font-bold text-ink">{{ $branch->employees_count ?? $branch->employees->count() }}</p>
                        </div>
                        <div class="py-2">
                            <p class="font-mono text-[8px] tracking-[0.14em] uppercase text-ink4 mb-1">Transactions</p>
                            <p class="font-serif text-[18px] font-bold text-ink">{{ $branch->today_txn_count ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

</x-app-layout>
