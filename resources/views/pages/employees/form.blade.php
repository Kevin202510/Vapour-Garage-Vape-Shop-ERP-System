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

        {{-- ERP Login Account (create mode only) --}}
        @if(!isset($employee))
        <div class="border border-purple/20 rounded-lg p-5 mb-6 bg-purple/[0.03]"
             x-data="{ createAccount: {{ old('create_account') ? 'true' : 'false' }} }">

            <label class="flex items-center gap-3 cursor-pointer select-none mb-1">
                <input type="checkbox" name="create_account" value="1"
                       x-model="createAccount"
                       class="w-4 h-4 rounded border-purple/40 text-purple focus:ring-purple/20">
                <span class="text-[13px] font-semibold text-ink">Create ERP Login Account</span>
            </label>
            <p class="font-mono text-[9px] text-ink4 tracking-wide ml-7 mb-4">
                Lets this employee sign in to the ERP dashboard.
            </p>

            <div x-show="createAccount" x-cloak class="flex flex-col gap-4">

                <x-form-input label="Login Email" name="login_email" type="email"
                              :value="old('login_email')"
                              placeholder="employee@vapourgarage.ph"
                              help="This will be the username to log in." />

                <div class="grid grid-cols-2 gap-4">
                    <x-form-input label="Password" name="login_password" type="password"
                                  placeholder="Min. 8 characters" />
                    <x-form-input label="Confirm Password" name="login_password_confirmation" type="password"
                                  placeholder="Re-enter password" />
                </div>

                <div class="flex items-start gap-2 px-3 py-2.5 bg-warn/5 border border-warn/20 rounded-md">
                    <svg class="w-4 h-4 text-warn flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                    <p class="font-mono text-[9px] text-warn leading-relaxed tracking-wide">
                        Save the credentials before submitting — the password will only be shown once after creation.
                        The employee's role in the ERP will match their job role.
                    </p>
                </div>
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
