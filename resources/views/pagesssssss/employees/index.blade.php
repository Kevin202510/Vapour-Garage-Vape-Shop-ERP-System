<x-app-layout title="Employees">

    <x-page-header overline="Human Resources" title="Employees"
                   :subtitle="$employees->total() . ' active staff · ' . $presentToday . ' present today · ' . $onLeave . ' on leave'">
        <x-slot:actions>
            <x-btn variant="primary" size="sm" :href="route('employees.create')">
                <x-icon name="plus" class="w-3 h-3" /> Add Employee
            </x-btn>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-3 gap-3 mb-6">
        <x-stat-box label="Total Active"    :value="$totalActive" />
        <x-stat-box label="Present Today"   :value="$presentToday"  value-class="text-ok" />
        <x-stat-box label="On Leave"        :value="$onLeave"       value-class="text-warn" />
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('employees.index') }}" class="flex items-center gap-3 mb-5">
        <div class="flex items-center gap-2 bg-white border border-cream3 rounded-md px-3 py-[7px] flex-1 max-w-xs focus-within:border-terra transition-colors">
            <x-icon name="filter" class="w-3.5 h-3.5 text-ink4 flex-shrink-0" />
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search name or code…"
                   class="bg-transparent border-none outline-none text-[12px] font-sans text-ink placeholder-ink4 w-full">
        </div>
        @if(auth()->user()->isSuperAdmin())
            <x-form-select name="branch_id" label="" placeholder="All Branches" class="min-w-[140px]">
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </x-form-select>
        @endif
        <x-form-select name="role" label="" placeholder="All Roles" class="min-w-[140px]">
            @foreach(['branch_manager' => 'Branch Manager', 'supervisor' => 'Supervisor', 'cashier' => 'Cashier', 'inventory_clerk' => 'Inventory Clerk'] as $val => $lbl)
                <option value="{{ $val }}" {{ request('role') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
            @endforeach
        </x-form-select>
        <x-btn type="submit" variant="ghost" size="sm">Apply</x-btn>
        @if(request()->hasAny(['search','branch_id','role']))
            <x-btn :href="route('employees.index')" variant="ghost" size="sm">Clear</x-btn>
        @endif
    </form>

    <x-data-table title="Staff Directory">
        <x-slot:tableHeader>
            <span class="font-mono text-[11px] text-ink4">{{ $employees->total() }} results</span>
        </x-slot:tableHeader>
        <x-slot:head>
            <x-th>Code</x-th>
            <x-th>Full Name</x-th>
            <x-th>Branch</x-th>
            <x-th>Department</x-th>
            <x-th>Role</x-th>
            <x-th>Type</x-th>
            <x-th>Base Salary</x-th>
            <x-th>Status</x-th>
            <x-th></x-th>
        </x-slot:head>

        @forelse($employees as $employee)
            <tr class="hover:bg-cream transition-colors cursor-pointer"
                onclick="window.location='{{ route('employees.show', $employee) }}'">
                <x-td><span class="font-mono text-[11px] text-ink3">{{ $employee->employee_code }}</span></x-td>
                <x-td class="font-semibold">{{ $employee->full_name }}</x-td>
                <x-td class="text-ink3">{{ $employee->branch->name ?? '—' }}</x-td>
                <x-td class="text-ink3">{{ $employee->department->name ?? '—' }}</x-td>
                <x-td>
                    <x-badge variant="{{ match($employee->role) { 'branch_manager' => 'navy', 'supervisor' => 'muted', 'cashier' => 'terra', default => 'muted' } }}">
                        {{ ucwords(str_replace('_', ' ', $employee->role)) }}
                    </x-badge>
                </x-td>
                <x-td>
                    <span class="bg-cream2 text-ink4 font-mono text-[9px] px-2 py-0.5 rounded">
                        {{ ucwords(str_replace('_', ' ', $employee->employment_type)) }}
                    </span>
                </x-td>
                <x-td><span class="font-mono text-[12px]">₱{{ number_format($employee->base_salary, 0) }}</span></x-td>
                <x-td>
                    @php
                        $attendance = $employee->attendance->where('work_date', now()->toDateString())->first();
                        $status     = $attendance?->status ?? 'absent';
                    @endphp
                    <x-badge variant="{{ match($status) { 'present' => 'ok', 'late' => 'warn', 'leave' => 'gold', default => $employee->is_active ? 'muted' : 'error' } }}">
                        {{ $employee->is_active ? ucfirst($status) : 'Inactive' }}
                    </x-badge>
                </x-td>
                <x-td>
                    <div class="flex gap-2" @click.stop>
                        <x-btn :href="route('employees.edit', $employee)" variant="ghost" size="sm">Edit</x-btn>
                    </div>
                </x-td>
            </tr>
        @empty
            <tr><td colspan="9">
                <x-empty-state title="No employees found" description="Try adjusting your filters." icon="user">
                    <x-slot:action><x-btn :href="route('employees.create')" variant="primary" size="sm">+ Add Employee</x-btn></x-slot:action>
                </x-empty-state>
            </td></tr>
        @endforelse

        <x-slot:pagination>{{ $employees->withQueryString()->links('vendor.pagination.simple') }}</x-slot:pagination>
    </x-data-table>

</x-app-layout>
