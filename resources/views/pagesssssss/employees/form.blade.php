<x-app-layout :title="isset($employee) ? 'Edit Employee' : 'New Employee'">

    <x-page-header
        overline="Human Resources"
        :title="isset($employee) ? 'Edit Employee' : 'New Employee'"
        :subtitle="isset($employee) ? $employee->employee_code : 'Add a new staff member'"
    >
        <x-slot:actions>
            <x-btn :href="route('employees.index')" variant="ghost" size="sm">← Back</x-btn>
        </x-slot:actions>
    </x-page-header>

    <form method="POST"
          action="{{ isset($employee) ? route('employees.update', $employee) : route('employees.store') }}"
          class="max-w-3xl">
        @csrf
        @if(isset($employee)) @method('PUT') @endif

        <div class="grid grid-cols-2 gap-6 mb-6">
            <x-form-input label="First Name" name="first_name" :value="old('first_name', $employee->first_name ?? '')" required />
            <x-form-input label="Last Name"  name="last_name"  :value="old('last_name',  $employee->last_name  ?? '')" required />
        </div>

        <div class="grid grid-cols-2 gap-6 mb-6">
            <x-form-input label="Employee Code" name="employee_code"
                          :value="old('employee_code', $employee->employee_code ?? '')" required
                          :placeholder="isset($employee) ? '' : 'e.g. EMP-00025'" />
            <x-form-input label="Email" name="email" type="email"
                          :value="old('email', $employee->email ?? '')" />
        </div>

        <div class="grid grid-cols-2 gap-6 mb-6">
            <x-form-input label="Phone" name="phone" :value="old('phone', $employee->phone ?? '')" placeholder="09XXXXXXXXX" />
            <x-form-input label="Hire Date" name="hire_date" type="date"
                          :value="old('hire_date', isset($employee) ? $employee->hire_date->format('Y-m-d') : '')" required />
        </div>

        <div class="grid grid-cols-2 gap-6 mb-6">
            <x-form-select label="Branch" name="branch_id" required>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ old('branch_id', $employee->branch_id ?? '') == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </x-form-select>
            <x-form-select label="Department" name="department_id">
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ old('department_id', $employee->department_id ?? '') == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </x-form-select>
        </div>

        <div class="grid grid-cols-3 gap-6 mb-6">
            <x-form-select label="Role" name="role" required>
                @foreach(['branch_manager' => 'Branch Manager', 'supervisor' => 'Supervisor', 'cashier' => 'Cashier', 'inventory_clerk' => 'Inventory Clerk'] as $val => $lbl)
                    <option value="{{ $val }}" {{ old('role', $employee->role ?? '') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
            </x-form-select>
            <x-form-select label="Employment Type" name="employment_type" required>
                @foreach(['full_time' => 'Full-time', 'part_time' => 'Part-time', 'contractual' => 'Contractual'] as $val => $lbl)
                    <option value="{{ $val }}" {{ old('employment_type', $employee->employment_type ?? 'full_time') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
            </x-form-select>
            <x-form-input label="Base Salary (₱)" name="base_salary" type="number" step="0.01" min="0"
                          :value="old('base_salary', $employee->base_salary ?? '')" required />
        </div>

        @if(isset($employee))
            <div class="grid grid-cols-2 gap-6 mb-6">
                <x-form-input label="Termination Date" name="termination_date" type="date"
                              :value="old('termination_date', $employee->termination_date?->format('Y-m-d') ?? '')"
                              help="Leave blank if still active." />
                <div class="flex items-end pb-2">
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active', $employee->is_active ?? true) ? 'checked' : '' }}
                               class="w-4 h-4 rounded border-cream3 text-terra focus:ring-terra/30">
                        <span class="text-[13px] text-ink2">Employee is active</span>
                    </label>
                </div>
            </div>
        @endif

        <div class="flex items-center gap-3 pt-2">
            <x-btn type="submit" variant="primary">
                {{ isset($employee) ? 'Save Changes' : 'Create Employee' }}
            </x-btn>
            <x-btn :href="route('employees.index')" variant="ghost">Cancel</x-btn>
        </div>
    </form>

</x-app-layout>
