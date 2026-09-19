<x-app-layout title="My Payslips">

    <x-page-header overline="Compensation" title="My Payslips" subtitle="Your payroll history">
    </x-page-header>

    @if(is_null($payrolls))
        <x-empty-state title="No employee record linked" icon="credit-card"
            description="Your account isn't linked to an employee record, so there's no payroll history to show. Contact an administrator if this looks wrong." />
    @else
        <x-data-table title="Payroll History">
            <x-slot:tableHeader>
                <span class="font-mono text-[11px] text-ink4">{{ $payrolls->total() }} records</span>
            </x-slot:tableHeader>
            <x-slot:head>
                <x-th>Period</x-th>
                <x-th>Gross Pay</x-th>
                <x-th>Total Deductions</x-th>
                <x-th>Net Pay</x-th>
                <x-th>Status</x-th>
                <x-th></x-th>
            </x-slot:head>

            @forelse($payrolls as $payroll)
                <tr class="hover:bg-cream transition-colors cursor-pointer"
                    onclick="window.location='{{ route('payroll.show', $payroll) }}'">
                    <x-td>
                        <span class="font-mono text-[11px] text-ink3">
                            {{ \Carbon\Carbon::parse($payroll->period_start)->format('M d') }}–{{ \Carbon\Carbon::parse($payroll->period_end)->format('M d, Y') }}
                        </span>
                    </x-td>
                    <x-td><span class="font-mono text-[12px]">₱{{ number_format($payroll->gross_pay, 2) }}</span></x-td>
                    <x-td><span class="font-mono text-[11px] text-danger">− ₱{{ number_format($payroll->total_deductions, 2) }}</span></x-td>
                    <x-td><span class="font-mono text-[12px] font-medium text-ok">₱{{ number_format($payroll->net_pay, 2) }}</span></x-td>
                    <x-td>
                        <x-badge variant="{{ match($payroll->status) { 'released' => 'ok', 'approved' => 'warn', 'draft' => 'muted', default => 'muted' } }}">
                            {{ ucfirst($payroll->status) }}
                        </x-badge>
                    </x-td>
                    <x-td>
                        <x-btn :href="route('payroll.show', $payroll)" variant="ghost" size="sm" @click.stop>View Payslip</x-btn>
                    </x-td>
                </tr>
            @empty
                <tr><td colspan="6">
                    <x-empty-state title="No payslips yet" description="Payroll records will appear here once they're generated for you." icon="credit-card" />
                </td></tr>
            @endforelse

            <x-slot:pagination>{{ $payrolls->withQueryString()->links('vendor.pagination.simple') }}</x-slot:pagination>
        </x-data-table>
    @endif

</x-app-layout>
