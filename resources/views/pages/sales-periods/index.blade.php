<x-app-layout title="Sales Period History">

    <x-page-header overline="Sales · Reset Schedule" title="Period History"
                   :subtitle="ucfirst($periodType) . ' period snapshots · ' . $summaries->total() . ' records'">
        <x-slot:actions>
            @can('view sales reports')
            <x-btn variant="ghost" size="sm" x-on:click="$dispatch('open-modal', 'close-period')">
                Close Period Now
            </x-btn>
            @endcan
            <x-btn :href="route('sales.index')" variant="ghost" size="sm">Sales Transactions →</x-btn>
        </x-slot:actions>
    </x-page-header>

    @if(session('success'))
        <x-alert type="success" :message="session('success')" class="mb-5" />
    @endif

    {{-- Period type tabs --}}
    <form method="GET" action="{{ route('sales-periods.index') }}" class="flex items-center gap-3 mb-5 flex-wrap">
        <div class="flex gap-1 bg-cream border border-cream3 rounded-md p-[3px]">
            @foreach(['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly', 'yearly' => 'Yearly'] as $val => $lbl)
            <a href="{{ route('sales-periods.index', array_merge(request()->query(), ['period_type' => $val])) }}"
               class="px-3.5 py-[5px] rounded text-[11px] font-mono tracking-wide transition-all
                      {{ $periodType === $val ? 'bg-white text-ink shadow-sm' : 'text-ink4 hover:text-ink' }}">
                {{ $lbl }}
            </a>
            @endforeach
        </div>

        @if(auth()->user()->isSuperAdmin())
        <x-form-select name="branch_id" label="" placeholder="All Branches (aggregate)" class="min-w-[200px]">
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}" {{ $effectiveBranchId == $branch->id ? 'selected' : '' }}>
                    {{ $branch->name }}
                </option>
            @endforeach
        </x-form-select>
        <x-btn type="submit" variant="ghost" size="sm">Apply</x-btn>
        @endif
    </form>

    {{-- Info banner about how this works --}}
    <div class="flex items-start gap-3 px-4 py-3 bg-purple/5 border border-purple/15 rounded-lg mb-5">
        <x-icon name="info" class="w-4 h-4 text-purple flex-shrink-0 mt-0.5" />
        <div>
            <p class="text-[12.5px] font-semibold text-ink mb-0.5">How Period Closing Works</p>
            <p class="text-[12px] text-ink3 leading-relaxed">
                Each period close creates an immutable snapshot of sales performance — revenue, transactions, top products, payment breakdown.
                The scheduler runs automatically (daily at 00:05 · weekly Mon 00:10 · monthly 1st 00:15 · yearly Jan 1st 00:20).
                You can also trigger a manual close at any time using <strong>Close Period Now</strong>.
                No data is deleted — transactions remain in full detail in <a href="{{ route('sales.index') }}" class="text-purple underline underline-offset-2">Sales History</a>.
            </p>
        </div>
    </div>

    {{-- Period table --}}
    <x-data-table :title="ucfirst($periodType) . ' Periods'">
        <x-slot:tableHeader>
            <span class="font-mono text-[11px] text-ink4">{{ $summaries->total() }} closed periods</span>
        </x-slot:tableHeader>
        <x-slot:head>
            <x-th>Period</x-th>
            <x-th>Date Range</x-th>
            @if(! $effectiveBranchId)
                <x-th>Branch</x-th>
            @endif
            <x-th>Revenue</x-th>
            <x-th>Transactions</x-th>
            <x-th>Items Sold</x-th>
            <x-th>Avg Basket</x-th>
            <x-th>Voided</x-th>
            <x-th>Top Product</x-th>
            <x-th>Closed</x-th>
        </x-slot:head>

        @forelse($summaries as $summary)
        <tr class="hover:bg-cream transition-colors" x-data="{ expanded: false }">
            <x-td>
                <div class="font-semibold text-ink text-[12.5px]">{{ $summary->period_label }}</div>
                <div class="font-mono text-[9px] text-ink4 mt-0.5 uppercase tracking-wide">
                    {{ ucfirst($summary->period_type) }}
                    @if($summary->closed_by === 'manual')
                        · <span class="text-purple">Manual</span>
                    @else
                        · Scheduled
                    @endif
                </div>
            </x-td>
            <x-td>
                <span class="font-mono text-[10px] text-ink3">
                    {{ $summary->date_from->format('M d') }}
                    @if($summary->date_from->toDateString() !== $summary->date_to->toDateString())
                        – {{ $summary->date_to->format('M d, Y') }}
                    @else
                        , {{ $summary->date_from->format('Y') }}
                    @endif
                </span>
            </x-td>
            @if(! $effectiveBranchId)
            <x-td class="text-ink3 text-[12px]">{{ $summary->branch?->name ?? 'All Branches' }}</x-td>
            @endif
            <x-td>
                <span class="font-mono text-[12px] font-semibold text-ok">
                    ₱{{ number_format($summary->total_revenue, 2) }}
                </span>
            </x-td>
            <x-td>
                <span class="font-mono text-[12px] text-ink2">{{ number_format($summary->total_transactions) }}</span>
            </x-td>
            <x-td>
                <span class="font-mono text-[12px] text-ink2">{{ number_format($summary->total_items_sold) }}</span>
            </x-td>
            <x-td>
                <span class="font-mono text-[12px] text-ink2">₱{{ number_format($summary->avg_basket, 2) }}</span>
            </x-td>
            <x-td>
                @if($summary->voided_transactions > 0)
                    <span class="font-mono text-[11px] text-danger">
                        {{ $summary->voided_transactions }} · ₱{{ number_format($summary->voided_amount, 0) }}
                    </span>
                @else
                    <span class="font-mono text-[11px] text-ink4">—</span>
                @endif
            </x-td>
            <x-td>
                @if($summary->top_product_name)
                    <div class="text-[11px] font-medium text-ink2 truncate max-w-[140px]" title="{{ $summary->top_product_name }}">
                        {{ $summary->top_product_name }}
                    </div>
                    <div class="font-mono text-[9px] text-ink4 mt-0.5">₱{{ number_format($summary->top_product_revenue, 0) }}</div>
                @else
                    <span class="text-ink4">—</span>
                @endif
            </x-td>
            <x-td>
                <div class="font-mono text-[9px] text-ink4">{{ $summary->closed_at->format('M d, Y') }}</div>
                @if($summary->closed_by_name)
                    <div class="font-mono text-[8px] text-ink4 mt-0.5">by {{ $summary->closed_by_name }}</div>
                @endif
            </x-td>
        </tr>

        {{-- Payment breakdown expandable row --}}
        @if($summary->payment_breakdown)
        <tr class="bg-cream/30 border-b border-cream3">
            <td colspan="{{ $effectiveBranchId ? 9 : 10 }}" class="px-4 py-2">
                <div class="flex items-center gap-6 flex-wrap">
                    <span class="font-mono text-[8px] text-ink4 uppercase tracking-widest">Payment Mix:</span>
                    @foreach($summary->payment_breakdown as $method => $data)
                    <span class="flex items-center gap-1.5">
                        <span class="font-mono text-[9px] text-ink3 uppercase">{{ str_replace('_', ' ', $method) }}</span>
                        <span class="font-mono text-[9px] text-ink2">{{ $data['count'] }}×</span>
                        <span class="font-mono text-[9px] font-semibold text-ok">₱{{ number_format($data['revenue'], 0) }}</span>
                    </span>
                    @endforeach
                </div>
            </td>
        </tr>
        @endif
        @empty
            <tr>
                <td colspan="{{ $effectiveBranchId ? 9 : 10 }}">
                    <x-empty-state
                        title="No {{ $periodType }} periods closed yet"
                        description="Periods are closed automatically by the scheduler, or you can trigger one manually."
                        icon="activity" />
                </td>
            </tr>
        @endforelse

        <x-slot:pagination>{{ $summaries->withQueryString()->links('vendor.pagination.simple') }}</x-slot:pagination>
    </x-data-table>

    {{-- Manual Close Modal --}}
    @can('view sales reports')
    <x-modal name="close-period" title="Close Period Now" max-width="sm">
        <form method="POST" action="{{ route('sales-periods.close') }}">
            @csrf
            <p class="text-[13px] text-ink3 mb-4 leading-relaxed">
                Manually close a period to create a snapshot of current sales performance.
                If a snapshot already exists for the period it will be <strong>overwritten</strong>.
            </p>
            <div class="flex flex-col gap-4">
                <x-form-select label="Period Type" name="period" required>
                    @foreach(['daily' => 'Daily (yesterday)', 'weekly' => 'Weekly (last week)', 'monthly' => 'Monthly (last month)', 'yearly' => 'Yearly (last year)'] as $val => $lbl)
                        <option value="{{ $val }}" {{ $periodType === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </x-form-select>

                @if(auth()->user()->isSuperAdmin())
                <x-form-select label="Branch (optional)" name="branch_id">
                    <option value="">All Branches (aggregate)</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $effectiveBranchId == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </x-form-select>
                @endif
            </div>
            <div class="flex items-center justify-end gap-3 pt-3 mt-3 border-t border-cream3">
                <x-btn type="button" variant="ghost" size="sm" x-on:click="$dispatch('close-modal', 'close-period')">Cancel</x-btn>
                <x-btn type="submit" variant="primary" size="sm">Close Period</x-btn>
            </div>
        </form>
    </x-modal>
    @endcan

</x-app-layout>
