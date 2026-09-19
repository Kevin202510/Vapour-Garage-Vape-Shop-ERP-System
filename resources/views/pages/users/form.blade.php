<x-app-layout :title="isset($user) ? 'Edit User' : 'New User'">

    <x-page-header
        overline="Human Resources · Access"
        :title="isset($user) ? 'Edit User' : 'New User'"
        :subtitle="isset($user) ? $user->email : 'Create an ERP login account'"
    >
        <x-slot:actions>
            <x-btn :href="route('users.index')" variant="ghost" size="sm">← Back</x-btn>
        </x-slot:actions>
    </x-page-header>

    <form method="POST"
          action="{{ isset($user) ? route('users.update', $user) : route('users.store') }}"
          class="max-w-3xl">
        @csrf
        @if(isset($user)) @method('PUT') @endif

        <div class="grid grid-cols-2 gap-6 mb-6">
            <x-form-input label="Full Name" name="name" :value="old('name', $user->name ?? '')" required />
            <x-form-input label="Email" name="email" type="email" :value="old('email', $user->email ?? '')" required />
        </div>

        <div class="grid grid-cols-2 gap-6 mb-6">
            <x-form-input label="{{ isset($user) ? 'New Password' : 'Password' }}" name="password" type="password"
                          :required="!isset($user)"
                          :help="isset($user) ? 'Leave blank to keep the current password.' : 'Min. 8 characters, mixed case + numbers.'" />
            <x-form-select label="Branch" name="branch_id" placeholder="All Branches (Admin)">
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ old('branch_id', $user->branch_id ?? '') == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </x-form-select>
        </div>

        @if(isset($user))
        <div class="mb-6">
            <label class="flex items-center gap-2.5 cursor-pointer">
                <input type="checkbox" name="is_active" value="1"
                       {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-cream3 text-terra focus:ring-terra/30">
                <span class="text-[13px] text-ink2">Account is active</span>
            </label>
        </div>
        @endif

        {{-- Role assignment — gated separately from basic profile edits, since
             granting roles is a distinct, higher-privilege action. --}}
        @can('manage roles')
        <div class="border border-cream3 rounded-lg p-5 mb-6">
            <p class="erp-label mb-3">Roles</p>
            @php $assigned = old('roles', isset($user) ? $user->roles->pluck('name')->toArray() : []); @endphp
            <div class="grid grid-cols-3 gap-3">
                @foreach($roles as $role)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                               {{ in_array($role->name, $assigned) ? 'checked' : '' }}
                               class="w-4 h-4 rounded border-cream3 text-terra focus:ring-terra/30">
                        <span class="text-[12.5px] text-ink2">{{ ucwords(str_replace('_', ' ', $role->name)) }}</span>
                    </label>
                @endforeach
            </div>
        </div>
        @elseif(isset($user) && $user->roles->isNotEmpty())
        <div class="mb-6">
            <p class="erp-label mb-2">Roles</p>
            <div class="flex flex-wrap gap-1.5">
                @foreach($user->roles as $role)
                    <x-badge variant="navy">{{ ucwords(str_replace('_', ' ', $role->name)) }}</x-badge>
                @endforeach
            </div>
            <p class="font-mono text-[9px] text-ink4 mt-1.5">You don't have permission to change role assignments.</p>
        </div>
        @endif

        <div class="flex items-center gap-3 pt-2">
            <x-btn type="submit" variant="primary">
                {{ isset($user) ? 'Save Changes' : 'Create User' }}
            </x-btn>
            <x-btn :href="route('users.index')" variant="ghost">Cancel</x-btn>
        </div>
    </form>

</x-app-layout>
