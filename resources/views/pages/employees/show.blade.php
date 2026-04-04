<x-app-layout :title="$employee->full_name">

    <x-page-header
        overline="Human Resources · Employee"
        :title="$employee->full_name"
        :subtitle="$employee->employee_code . ' · ' . ucwords(str_replace('_', ' ', $employee->role))"
    >
        <x-slot:actions>
            <x-btn :href="route('employees.index')" variant="ghost" size="sm">← All Employees</x-btn>
            <x-btn :href="route('employees.edit', $employee)" variant="ghost" size="sm">Edit</x-btn>
            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'delete-employee' }))"
                    class="inline-flex items-center px-3 py-[5px] text-[11px] font-medium text-danger/70 border border-danger/20 rounded-md hover:bg-danger/5 transition-colors">
                Delete
            </button>
        </x-slot:actions>
    </x-page-header>

    <x-confirm-delete name="delete-employee"
                      :action="route('employees.destroy', $employee)"
                      :item-name="$employee->full_name" />

    <div class="grid grid-cols-3 gap-4 mb-6">
        <x-stat-box label="Base Salary" :value="'₱' . number_format($employee->base_salary, 0)" />
        <x-stat-box label="Days Present (Month)"
                    :value="$employee->attendance->where('work_date', '>=', now()->startOfMonth()->toDateString())->where('status', 'present')->count()" />
        <x-stat-box label="Overtime Hours (Month)"
                    :value="number_format($employee->attendance->where('work_date', '>=', now()->startOfMonth()->toDateString())->sum('overtime_hours'), 1) . 'h'" />
    </div>

    <div class="grid grid-cols-2 gap-5 mb-5">

        {{-- Details --}}
        <x-card title="Employee Details">
            <dl class="flex flex-col gap-4">
                @foreach([
                    'Employee Code'   => $employee->employee_code,
                    'Full Name'       => $employee->full_name,
                    'Email'           => $employee->email ?? '—',
                    'Phone'           => $employee->phone ?? '—',
                    'Branch'          => $employee->branch->name ?? '—',
                    'Department'      => $employee->department->name ?? '—',
                    'Role'            => ucwords(str_replace('_', ' ', $employee->role)),
                    'Employment Type' => ucwords(str_replace('_', ' ', $employee->employment_type)),
                    'Hire Date'       => $employee->hire_date->format('F d, Y'),
                    'Status'          => $employee->is_active ? 'Active' : 'Inactive',
                ] as $label => $value)
                    <div class="flex justify-between items-start border-b border-cream2 pb-3 last:border-0 last:pb-0">
                        <dt class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 pt-0.5 flex-shrink-0">{{ $label }}</dt>
                        <dd class="text-[12.5px] text-ink2 text-right ml-4">
                            @if($label === 'Status')
                                <x-badge variant="{{ $employee->is_active ? 'ok' : 'error' }}">{{ $value }}</x-badge>
                            @elseif($label === 'Role')
                                <x-badge variant="{{ match($employee->role) { 'branch_manager' => 'navy', 'cashier' => 'terra', default => 'muted' } }}">
                                    {{ $value }}
                                </x-badge>
                            @else
                                {{ $value }}
                            @endif
                        </dd>
                    </div>
                @endforeach
            </dl>
        </x-card>

        {{-- This month attendance summary --}}
        <x-card title="Attendance — {{ now()->format('F Y') }}">
            @php
                $thisMonth = $employee->attendance
                    ->where('work_date', '>=', now()->startOfMonth()->toDateString())
                    ->where('work_date', '<=', now()->toDateString());
            @endphp
            <div class="grid grid-cols-3 gap-3 mb-4">
                @foreach(['present' => ['ok', 'Present'], 'late' => ['warn', 'Late'], 'absent' => ['error', 'Absent'], 'leave' => ['gold', 'Leave'], 'half_day' => ['muted', 'Half Day']] as $status => [$variant, $label])
                    <div class="bg-cream rounded-lg px-3 py-2.5 text-center">
                        <p class="font-serif text-[20px] font-bold text-ink">
                            {{ $thisMonth->where('status', $status)->count() }}
                        </p>
                        <x-badge :variant="$variant" class="mt-1">{{ $label }}</x-badge>
                    </div>
                @endforeach
                <div class="bg-cream rounded-lg px-3 py-2.5 text-center">
                    <p class="font-serif text-[20px] font-bold text-ink">
                        {{ number_format($thisMonth->sum('overtime_hours'), 1) }}h
                    </p>
                    <x-badge variant="navy" class="mt-1">Overtime</x-badge>
                </div>
            </div>

            {{-- Last 7 attendance rows --}}
            <div class="flex flex-col gap-1.5">
                @foreach($employee->attendance->sortByDesc('work_date')->take(7) as $att)
                    <div class="flex items-center justify-between px-3 py-2 bg-cream rounded-md">
                        <span class="font-mono text-[10px] text-ink3">
                            {{ \Carbon\Carbon::parse($att->work_date)->format('D, M d') }}
                        </span>
                        <div class="flex items-center gap-3">
                            @if($att->time_in)
                                <span class="font-mono text-[10px] text-ink4">
                                    {{ \Carbon\Carbon::createFromTimeString($att->time_in)->format('g:i A') }}
                                    @if($att->time_out)
                                        → {{ \Carbon\Carbon::createFromTimeString($att->time_out)->format('g:i A') }}
                                    @endif
                                </span>
                            @endif
                            <x-badge variant="{{ match($att->status) { 'present' => 'ok', 'late' => 'warn', 'absent' => 'error', 'leave' => 'gold', default => 'muted' } }}">
                                {{ ucfirst($att->status) }}
                            </x-badge>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>
    </div>

    {{-- Payroll history --}}
    <x-data-table title="Payroll History">
        <x-slot:tableHeader>
            <x-btn :href="route('payroll.index', ['employee_id' => $employee->id])" variant="ghost" size="sm">View all →</x-btn>
        </x-slot:tableHeader>
        <x-slot:head>
            <x-th>Period</x-th>
            <x-th>Gross Pay</x-th>
            <x-th>SSS</x-th>
            <x-th>PhilHealth</x-th>
            <x-th>Pag-IBIG</x-th>
            <x-th>W. Tax</x-th>
            <x-th>Net Pay</x-th>
            <x-th>Status</x-th>
            <x-th></x-th>
        </x-slot:head>
        @forelse($employee->payroll()->latest('period_start')->take(6)->get() as $payroll)
            <tr class="hover:bg-cream transition-colors">
                <x-td>
                    <span class="font-mono text-[10px] text-ink4">
                        {{ \Carbon\Carbon::parse($payroll->period_start)->format('M d') }}
                        – {{ \Carbon\Carbon::parse($payroll->period_end)->format('M d, Y') }}
                    </span>
                </x-td>
                <x-td><span class="font-mono">₱{{ number_format($payroll->gross_pay, 2) }}</span></x-td>
                <x-td><span class="font-mono text-[11px] text-danger">₱{{ number_format($payroll->sss_contribution, 2) }}</span></x-td>
                <x-td><span class="font-mono text-[11px] text-danger">₱{{ number_format($payroll->philhealth_contribution, 2) }}</span></x-td>
                <x-td><span class="font-mono text-[11px] text-danger">₱{{ number_format($payroll->pagibig_contribution, 2) }}</span></x-td>
                <x-td><span class="font-mono text-[11px] text-danger">₱{{ number_format($payroll->withholding_tax, 2) }}</span></x-td>
                <x-td><span class="font-mono font-medium text-ok">₱{{ number_format($payroll->net_pay, 2) }}</span></x-td>
                <x-td>
                    <x-badge variant="{{ match($payroll->status) { 'released' => 'ok', 'approved' => 'warn', default => 'muted' } }}">
                        {{ ucfirst($payroll->status) }}
                    </x-badge>
                </x-td>
                <x-td>
                    <x-btn :href="route('payroll.show', $payroll)" variant="ghost" size="sm">Slip</x-btn>
                </x-td>
            </tr>
        @empty
            <tr><td colspan="9"><x-empty-state title="No payroll records" icon="credit-card" /></td></tr>
        @endforelse
    </x-data-table>

</x-app-layout>
