<x-app-layout :title="'User — ' . $user->name">

    <x-page-header overline="Human Resources · Access" :title="$user->name" :subtitle="$user->email">
        <x-slot:actions>
            <x-btn :href="route('users.index')" variant="ghost" size="sm">← All Users</x-btn>
            @can('edit users')
            <x-btn :href="route('users.edit', $user)" variant="primary" size="sm">Edit</x-btn>
            @endcan
        </x-slot:actions>
    </x-page-header>

    @if(session('success'))
        <x-alert type="success" :message="session('success')" class="mb-5" />
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-4">

        <x-card title="Account Details">
            <div class="grid grid-cols-2 gap-5">
                <div>
                    <p class="erp-label mb-1">Full Name</p>
                    <p class="text-[13px] font-medium text-ink">{{ $user->name }}</p>
                </div>
                <div>
                    <p class="erp-label mb-1">Email</p>
                    <p class="text-[13px] font-medium text-ink">{{ $user->email }}</p>
                </div>
                <div>
                    <p class="erp-label mb-1">Branch</p>
                    <p class="text-[13px] font-medium text-ink">{{ $user->branch->name ?? 'All Branches' }}</p>
                </div>
                <div>
                    <p class="erp-label mb-1">Linked Employee</p>
                    <p class="text-[13px] font-medium text-ink">{{ $user->employee->full_name ?? '— Not linked' }}</p>
                </div>
                <div>
                    <p class="erp-label mb-1">Status</p>
                    <x-badge variant="{{ $user->is_active ? 'ok' : 'error' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</x-badge>
                </div>
                <div>
                    <p class="erp-label mb-1">Last Login</p>
                    <p class="text-[13px] font-medium text-ink">{{ $user->last_login_at?->format('M d, Y g:i A') ?? 'Never' }}</p>
                </div>
            </div>

            <div class="mt-6 pt-5 border-t border-cream3 flex items-center gap-2">
                @can('manage users')
                    @if($user->id !== auth()->id())
                        @if($user->is_active)
                        <form method="POST" action="{{ route('users.deactivate', $user) }}">
                            @csrf
                            <x-btn type="submit" variant="ghost" size="sm">Deactivate Account</x-btn>
                        </form>
                        @else
                        <form method="POST" action="{{ route('users.activate', $user) }}">
                            @csrf
                            <x-btn type="submit" variant="ghost" size="sm" class="!text-ok !border-ok/30 hover:!bg-ok/10">Activate Account</x-btn>
                        </form>
                        @endif
                    @endif
                @endcan
                @can('delete users')
                    @if($user->id !== auth()->id())
                    <x-btn variant="danger" size="sm" x-on:click="$dispatch('open-modal', 'delete-user')">Delete Account</x-btn>
                    <x-confirm-delete name="delete-user" :action="route('users.destroy', $user)" :item-name="$user->name" />
                    @endif
                @endcan
            </div>
        </x-card>

        <x-card title="Roles">
            <div class="flex flex-wrap gap-1.5 mb-1">
                @forelse($user->roles as $role)
                    <x-badge variant="{{ in_array($role->name, ['super_admin','admin']) ? 'purple' : 'navy' }}">
                        {{ ucwords(str_replace('_', ' ', $role->name)) }}
                    </x-badge>
                @empty
                    <span class="font-mono text-[10px] text-ink4">No role assigned</span>
                @endforelse
            </div>
            @can('edit users')
            <x-btn :href="route('users.edit', $user)" variant="ghost" size="sm" class="mt-3">Manage Roles</x-btn>
            @endcan
        </x-card>
    </div>

    <x-card title="Effective Permissions" class="mt-4">
        <p class="font-mono text-[9px] text-ink4 mb-3 tracking-wide">Granted via assigned role(s) — {{ $user->getAllPermissions()->count() }} total.</p>
        <div class="flex flex-wrap gap-1.5">
            @forelse($user->getAllPermissions() as $permission)
                <span class="bg-cream2 text-ink3 font-mono text-[9.5px] px-2 py-1 rounded">{{ $permission->name }}</span>
            @empty
                <span class="font-mono text-[10px] text-ink4">No permissions.</span>
            @endforelse
        </div>
    </x-card>

</x-app-layout>
