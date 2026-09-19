<x-app-layout title="Payroll">

    <x-page-header overline="Compensation" title="Payroll"
                   :subtitle="$activeBranchLabel . ' · Period: ' . $periodLabel . ' · ' . $totalCount . ' records'">
        <x-slot:actions>
            @can('process payroll')
            <x-btn variant="ghost" size="sm" x-on:click="$dispatch('open-modal', 'generate-payroll')">
                Generate Payroll
            </x-btn>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-3 gap-3 mb-6">
        <x-stat-box label="Total Net Pay"     :value="'₱' . number_format($stats['total_net'])" />
        <x-stat-box label="Released"          :value="$stats['released_count']"   value-class="text-ok" />
        <x-stat-box label="Pending Approval"  :value="$stats['pending_count']"    value-class="text-warn" />
    </div>

    {{-- Period filter --}}
    <form method="GET" action="{{ route('payroll.index') }}" class="flex items-center gap-3 flex-wrap mb-5">
        <x-form-input name="period_start" type="date" label="" :value="request('period_start', now()->startOfMonth()->format('Y-m-d'))" class="min-w-[140px]" />
        <span class="text-ink4 text-sm">to</span>
        <x-form-input name="period_end"   type="date" label="" :value="request('period_end',   now()->format('Y-m-d'))"               class="min-w-[140px]" />
        @if(auth()->user()->isSuperAdmin())
            <x-form-select name="branch_id" label="" placeholder="All Branches" class="min-w-[160px]">
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ $effectiveBranchId == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </x-form-select>
        @endif
        <div class="flex gap-1 bg-cream border border-cream3 rounded-md p-[3px]">
            @foreach(['all' => 'All', 'draft' => 'Draft', 'approved' => 'Approved', 'released' => 'Released'] as $val => $lbl)
                <a href="{{ route('payroll.index', array_merge(request()->query(), ['status' => $val])) }}"
                   class="px-3.5 py-[5px] rounded text-[11px] font-mono tracking-wide transition-all
                          {{ request('status', 'all') === $val ? 'bg-white text-ink shadow-sm' : 'text-ink4 hover:text-ink' }}">
                    {{ $lbl }}
                </a>
            @endforeach
        </div>
        <x-btn type="submit" variant="ghost" size="sm">Apply</x-btn>
    </form>

    <x-data-table title="Payroll Records">
        <x-slot:tableHeader>
            <span class="font-mono text-[11px] text-ink4">{{ $payrolls->total() }} records</span>
        </x-slot:tableHeader>
        <x-slot:head>
            <x-th>Employee</x-th>
            <x-th>Branch</x-th>
            <x-th>Period</x-th>
            <x-th>Gross Pay</x-th>
            <x-th>SSS</x-th>
            <x-th>PhilHealth</x-th>
            <x-th>Pag-IBIG</x-th>
            <x-th>Net Pay</x-th>
            <x-th>Status</x-th>
            <x-th></x-th>
        </x-slot:head>

        @forelse($payrolls as $payroll)
            <tr class="hover:bg-cream transition-colors">
                <x-td class="font-semibold">{{ $payroll->employee->full_name ?? '—' }}</x-td>
                <x-td class="text-ink3">{{ $payroll->employee->branch->name ?? '—' }}</x-td>
                <x-td>
                    <span class="font-mono text-[10px] text-ink4">
                        {{ \Carbon\Carbon::parse($payroll->period_start)->format('M d') }}–{{ \Carbon\Carbon::parse($payroll->period_end)->format('M d') }}
                    </span>
                </x-td>
                <x-td><span class="font-mono text-[12px]">₱{{ number_format($payroll->gross_pay, 2) }}</span></x-td>
                <x-td><span class="font-mono text-[11px] text-danger">₱{{ number_format($payroll->sss_contribution, 2) }}</span></x-td>
                <x-td><span class="font-mono text-[11px] text-danger">₱{{ number_format($payroll->philhealth_contribution, 2) }}</span></x-td>
                <x-td><span class="font-mono text-[11px] text-danger">₱{{ number_format($payroll->pagibig_contribution, 2) }}</span></x-td>
                <x-td><span class="font-mono text-[12px] font-medium text-ok">₱{{ number_format($payroll->net_pay, 2) }}</span></x-td>
                <x-td>
                    <x-badge variant="{{ match($payroll->status) { 'released' => 'ok', 'approved' => 'warn', 'draft' => 'muted', default => 'muted' } }}">
                        {{ ucfirst($payroll->status) }}
                    </x-badge>
                </x-td>
                <x-td>
                    <div class="flex gap-2">
                        @if($payroll->status === 'draft')
                            @can('approve payroll')
                            <form method="POST" action="{{ route('payroll.approve', $payroll) }}">
                                @csrf @method('POST')
                                <x-btn type="submit" variant="primary" size="sm">Approve</x-btn>
                            </form>
                            @endcan
                        @elseif($payroll->status === 'approved')
                            @can('approve payroll')
                            <form method="POST" action="{{ route('payroll.release', $payroll) }}">
                                @csrf @method('POST')
                                <x-btn type="submit" variant="primary" size="sm">Release</x-btn>
                            </form>
                            @endcan
                        @else
                            <x-btn :href="route('payroll.show', $payroll)" variant="ghost" size="sm">Payslip</x-btn>
                        @endif
                    </div>
                </x-td>
            </tr>
        @empty
            <tr><td colspan="10">
                <x-empty-state title="No payroll records" description="Generate payroll for the selected period." icon="credit-card" />
            </td></tr>
        @endforelse

        <x-slot:pagination>{{ $payrolls->withQueryString()->links('vendor.pagination.simple') }}</x-slot:pagination>
    </x-data-table>

    {{-- Generate Payroll Modal --}}
    <x-modal name="generate-payroll" title="Generate Payroll" max-width="md">
        <form method="POST" action="{{ route('payroll.generate') }}">
            @csrf
            <div class="flex flex-col gap-4">
                <x-form-select label="Branch" name="branch_id" required>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </x-form-select>
                <div class="grid grid-cols-2 gap-4">
                    <x-form-input label="Period Start" name="period_start" type="date"
                                  :value="now()->startOfMonth()->format('Y-m-d')" required />
                    <x-form-input label="Period End" name="period_end" type="date"
                                  :value="now()->format('Y-m-d')" required />
                </div>
                <x-form-input label="Period Label" name="period_label"
                              :value="now()->format('Y-m') . ' First Half'" required
                              placeholder="e.g. 2026-04 First Half" />
            </div>
            <div class="flex items-center justify-end gap-3 pt-3 mt-3 border-t border-cream3">
                <x-btn variant="ghost" size="sm" type="button"
                       x-on:click="$dispatch('close-modal', 'generate-payroll')">Cancel</x-btn>
                <x-btn variant="primary" size="sm" type="submit">Generate</x-btn>
            </div>
        </form>
    </x-modal>

</x-app-layout>
