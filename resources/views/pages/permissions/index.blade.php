<x-app-layout title="Permissions">

    <x-page-header overline="Human Resources · Access" title="Permissions" subtitle="Read-only reference — edit assignments from the Roles page">
        <x-slot:actions>
            <x-btn :href="route('roles.index')" variant="ghost" size="sm">← Roles</x-btn>
        </x-slot:actions>
    </x-page-header>

    <div class="flex flex-col gap-4">
        @foreach($groups as $module => $permissions)
            <x-card :title="$module">
                <div class="flex flex-col divide-y divide-cream3">
                    @foreach($permissions as $permission)
                        <div class="flex items-center justify-between py-2.5">
                            <span class="font-mono text-[11.5px] text-ink2">{{ $permission->name }}</span>
                            <div class="flex flex-wrap gap-1 justify-end max-w-[60%]">
                                @forelse($permission->role_names as $roleName)
                                    <x-badge variant="{{ in_array($roleName, ['super_admin','admin']) ? 'purple' : 'muted' }}">
                                        {{ ucwords(str_replace('_', ' ', $roleName)) }}
                                    </x-badge>
                                @empty
                                    <span class="font-mono text-[9px] text-ink4">Unassigned</span>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>
        @endforeach
    </div>

</x-app-layout>
