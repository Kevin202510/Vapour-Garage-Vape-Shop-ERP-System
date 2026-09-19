<x-app-layout title="Roles">

    <x-page-header overline="Human Resources · Access" title="Roles" :subtitle="$roles->total() . ' roles configured'">
        <x-slot:actions>
            <x-btn :href="route('permissions.index')" variant="ghost" size="sm">View Permissions</x-btn>
            <x-btn variant="primary" size="sm" :href="route('roles.create')">
                <x-icon name="plus" class="w-3 h-3" /> Add Role
            </x-btn>
        </x-slot:actions>
    </x-page-header>

    @if(session('success'))
        <x-alert type="success" :message="session('success')" class="mb-5" />
    @endif
    @if(session('error'))
        <x-alert type="error" :message="session('error')" class="mb-5" />
    @endif

    <form method="GET" action="{{ route('roles.index') }}" class="flex items-center gap-2 mb-5">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search roles…"
               class="erp-input" style="width:240px">
        <x-btn type="submit" variant="ghost" size="sm">Apply</x-btn>
        @if(request('search'))
            <x-btn :href="route('roles.index')" variant="ghost" size="sm">Clear</x-btn>
        @endif
    </form>

    <x-data-table title="Roles">
        <x-slot:tableHeader>
            <span class="font-mono text-[11px] text-ink4">{{ $roles->total() }} results</span>
        </x-slot:tableHeader>
        <x-slot:head>
            <x-th>Role</x-th>
            <x-th>Permissions</x-th>
            <x-th>Users</x-th>
            <x-th>Type</x-th>
            <x-th></x-th>
        </x-slot:head>

        @forelse($roles as $role)
            <tr class="hover:bg-cream transition-colors">
                <x-td class="font-semibold">{{ ucwords(str_replace('_', ' ', $role->name)) }}</x-td>
                <x-td><span class="font-mono text-[12px] text-ink2">{{ $role->permissions_count }}</span></x-td>
                <x-td><span class="font-mono text-[12px] text-ink2">{{ $role->user_count }}</span></x-td>
                <x-td>
                    @if(in_array($role->name, $protected))
                        <x-badge variant="muted">System</x-badge>
                    @else
                        <x-badge variant="terra">Custom</x-badge>
                    @endif
                </x-td>
                <x-td>
                    <div class="flex gap-1.5">
                        <x-btn :href="route('roles.edit', $role)" variant="ghost" size="sm">
                            {{ in_array($role->name, $protected) ? 'Edit Permissions' : 'Edit' }}
                        </x-btn>
                        @if(!in_array($role->name, $protected))
                            <x-btn variant="danger" size="sm"
                                   x-on:click="$dispatch('open-modal', 'delete-role-{{ $role->id }}')">Delete</x-btn>
                            <x-confirm-delete :name="'delete-role-'.$role->id" :action="route('roles.destroy', $role)" :item-name="$role->name" />
                        @endif
                    </div>
                </x-td>
            </tr>
        @empty
            <tr><td colspan="5">
                <x-empty-state title="No roles found" icon="shield">
                    <x-slot:action><x-btn :href="route('roles.create')" variant="primary" size="sm">+ Add Role</x-btn></x-slot:action>
                </x-empty-state>
            </td></tr>
        @endforelse

        <x-slot:pagination>{{ $roles->withQueryString()->links('vendor.pagination.simple') }}</x-slot:pagination>
    </x-data-table>

</x-app-layout>
