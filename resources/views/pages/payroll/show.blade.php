<x-app-layout :title="'Payslip — ' . $payroll->employee->full_name">

    <div class="no-print">
        <x-page-header
            overline="Compensation · Payslip"
            :title="$payroll->employee->full_name"
            :subtitle="$payroll->period_label"
        >
            <x-slot:actions>
                @can('view payroll')
                    <x-btn :href="route('payroll.index')" variant="ghost" size="sm">← All Payroll</x-btn>
                @else
                    <x-btn :href="route('profile.mine')" variant="ghost" size="sm">← My Profile</x-btn>
                @endcan
                <button onclick="window.print()"
                        class="inline-flex items-center gap-1.5 px-4 py-[7px] text-[12px] font-medium bg-sidebar text-white rounded-md hover:opacity-90 transition-opacity">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                        <rect x="6" y="14" width="12" height="8"/>
                    </svg>
                    Print Payslip
                </button>

                @if($payroll->status === 'draft')
                    @can('approve payroll')
                        <form method="POST" action="{{ route('payroll.approve', $payroll) }}" class="inline">
                            @csrf @method('POST')
                            <x-btn type="submit" variant="primary" size="sm">Approve</x-btn>
                        </form>
                    @endcan
                @elseif($payroll->status === 'approved')
                    @can('approve payroll')
                        <form method="POST" action="{{ route('payroll.release', $payroll) }}" class="inline">
                            @csrf @method('POST')
                            <x-btn type="submit" variant="primary" size="sm">Release</x-btn>
                        </form>
                    @endcan
                @endif
            </x-slot:actions>
        </x-page-header>
    </div>

    {{-- Printable payslip --}}
    <div class="max-w-2xl mx-auto" id="payslip">

        {{-- Header --}}
        <div class="bg-sidebar text-white rounded-t-xl px-8 py-6 flex items-start justify-between print:rounded-none">
            <div>
                <p class="font-mono text-[9px] tracking-[0.2em] uppercase text-white/40 mb-1">Payroll Slip</p>
                <h2 class="font-serif text-[22px] font-bold">VapeHub ERP</h2>
                <p class="font-mono text-[10px] text-white/50 mt-0.5">{{ $payroll->employee->branch->name ?? '' }}</p>
            </div>
            <div class="text-right">
                <x-badge variant="{{ match($payroll->status) { 'released' => 'ok', 'approved' => 'warn', default => 'muted' } }}"
                         class="mb-2">
                    {{ strtoupper($payroll->status) }}
                </x-badge>
                <p class="font-mono text-[10px] text-white/50 mt-1">{{ $payroll->period_label }}</p>
            </div>
        </div>

        {{-- Employee info --}}
        <div class="bg-cream border-x border-cream3 px-8 py-4 grid grid-cols-2 gap-4">
            @foreach([
                'Employee Name' => $payroll->employee->full_name,
                'Employee Code' => $payroll->employee->employee_code,
                'Role'          => ucwords(str_replace('_', ' ', $payroll->employee->role)),
                'Department'    => $payroll->employee->department->name ?? '—',
                'Period'        => \Carbon\Carbon::parse($payroll->period_start)->format('M d') . ' – ' . \Carbon\Carbon::parse($payroll->period_end)->format('M d, Y'),
                'Pay Date'      => $payroll->processed_at ? $payroll->processed_at->format('F d, Y') : '—',
            ] as $label => $value)
                <div>
                    <p class="font-mono text-[8px] tracking-[0.14em] uppercase text-ink4 mb-0.5">{{ $label }}</p>
                    <p class="text-[12.5px] font-medium text-ink">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        {{-- Earnings --}}
        <div class="bg-white border-x border-cream3 px-8 py-5">
            <p class="font-mono text-[9px] tracking-[0.14em] uppercase text-ok mb-3">Earnings</p>
            <div class="flex flex-col gap-2.5">
                @foreach([
                    ['Basic Pay',      $payroll->base_pay],
                    ['Overtime Pay',   $payroll->overtime_pay],
                    ['Allowances',     $payroll->allowances],
                ] as [$label, $amount])
                    <div class="flex justify-between items-center">
                        <span class="text-[12.5px] text-ink3">{{ $label }}</span>
                        <span class="font-mono text-[12px] {{ $amount > 0 ? 'text-ink' : 'text-ink4' }}">
                            ₱{{ number_format($amount, 2) }}
                        </span>
                    </div>
                @endforeach
                <div class="flex justify-between items-center border-t border-cream2 pt-2.5 mt-1">
                    <span class="font-semibold text-[13px] text-ink">Gross Pay</span>
                    <span class="font-mono font-medium text-[13px] text-ink">₱{{ number_format($payroll->gross_pay, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Deductions --}}
        <div class="bg-white border-x border-cream3 px-8 py-5 border-t border-cream2">
            <p class="font-mono text-[9px] tracking-[0.14em] uppercase text-danger mb-3">Deductions</p>
            <div class="flex flex-col gap-2.5">
                @foreach([
                    ['SSS Contribution',        $payroll->sss_contribution],
                    ['PhilHealth Contribution', $payroll->philhealth_contribution],
                    ['Pag-IBIG Contribution',   $payroll->pagibig_contribution],
                    ['Withholding Tax',         $payroll->withholding_tax],
                    ['Other Deductions',        $payroll->other_deductions],
                ] as [$label, $amount])
                    @if($amount > 0)
                    <div class="flex justify-between items-center">
                        <span class="text-[12.5px] text-ink3">{{ $label }}</span>
                        <span class="font-mono text-[12px] text-danger">− ₱{{ number_format($amount, 2) }}</span>
                    </div>
                    @endif
                @endforeach
                <div class="flex justify-between items-center border-t border-cream2 pt-2.5 mt-1">
                    <span class="font-semibold text-[13px] text-ink">Total Deductions</span>
                    <span class="font-mono font-medium text-[13px] text-danger">
                        − ₱{{ number_format($payroll->sss_contribution + $payroll->philhealth_contribution + $payroll->pagibig_contribution + $payroll->withholding_tax + $payroll->other_deductions, 2) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Net pay --}}
        <div class="bg-sidebar text-white rounded-b-xl px-8 py-5 flex items-center justify-between print:rounded-none">
            <div>
                <p class="font-mono text-[9px] tracking-[0.2em] uppercase text-white/40 mb-1">Net Pay</p>
                <p class="font-serif text-[32px] font-black text-white leading-none">
                    ₱{{ number_format($payroll->net_pay, 2) }}
                </p>
            </div>
            <div class="text-right">
                <p class="font-mono text-[9px] text-white/40 mb-1">Processed by</p>
                <p class="text-[12px] text-white/70">{{ optional($payroll->processor)->full_name ?? 'System' }}</p>
                <p class="font-mono text-[10px] text-white/40 mt-0.5">
                    {{ $payroll->processed_at?->format('M d, Y') ?? '—' }}
                </p>
            </div>
        </div>

    </div>

    {{-- Danger zone --}}
    @if($payroll->status === 'draft')
        @can('process payroll')
        <div class="no-print max-w-2xl mx-auto mt-6 border border-danger/20 rounded-lg p-4 bg-danger/[0.03] flex items-center justify-between">
            <div>
                <p class="text-[12.5px] font-medium text-ink">Delete this payroll record</p>
                <p class="text-[11px] text-ink4 mt-0.5">Only draft records can be deleted.</p>
            </div>
            <form method="POST" action="{{ route('payroll.destroy', $payroll) }}">
                @csrf @method('DELETE')
                <button type="submit" onclick="return confirm('Delete this payroll record?')"
                        class="inline-flex items-center px-3 py-1.5 text-[11px] font-medium text-danger border border-danger/30 rounded-md hover:bg-danger/5 transition-colors">
                    Delete Draft
                </button>
            </form>
        </div>
        @endcan
    @endif

    @push('styles')
    <style>
    @media print {
        body { background: white !important; }
        header, aside, nav, .no-print, .erp-alert { display: none !important; }
        main { padding: 0 !important; }
        #payslip { max-width: 100% !important; margin: 0 !important; }
        .print\:rounded-none { border-radius: 0 !important; }
    }
    </style>
    @endpush

</x-app-layout>
