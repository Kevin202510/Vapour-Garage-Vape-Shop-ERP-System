<x-app-layout title="Users">

    <x-page-header overline="Human Resources · Access" title="Users"
                   :subtitle="$activeBranchLabel . ' · ' . $totalUsers . ' accounts · ' . $activeUsers . ' active'">
        <x-slot:actions>
            @can('create users')
            <x-btn variant="primary" size="sm" :href="route('users.create')">
                <x-icon name="plus" class="w-3 h-3" /> Add User
            </x-btn>
            @endcan
        </x-slot:actions>
    </x-page-header>

    @if(session('success'))
        <x-alert type="success" :message="session('success')" class="mb-5" />
    @endif
    @if(session('error'))
        <x-alert type="error" :message="session('error')" class="mb-5" />
    @endif

    <div class="grid grid-cols-3 gap-3 mb-6">
        <x-stat-box label="Total Accounts" :value="$totalUsers" />
        <x-stat-box label="Active"         :value="$activeUsers"   value-class="text-ok" />
        <x-stat-box label="Inactive"       :value="$inactiveUsers" value-class="text-danger" />
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('users.index') }}" class="flex items-center gap-3 flex-wrap mb-5">
        <div class="flex items-center gap-2 bg-white border border-cream3 rounded-md px-3 py-[7px] flex-1 max-w-xs focus-within:border-terra transition-colors">
            <x-icon name="filter" class="w-3.5 h-3.5 text-ink4 flex-shrink-0" />
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search name or email…"
                   class="bg-transparent border-none outline-none text-[12px] font-sans text-ink placeholder-ink4 w-full">
        </div>
        @if(auth()->user()->isSuperAdmin())
            <x-form-select name="branch_id" label="" placeholder="All Branches" class="min-w-[160px]">
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ $effectiveBranchId == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </x-form-select>
        @endif
        <x-form-select name="role" label="" placeholder="All Roles" class="min-w-[150px]">
            @foreach($roles as $role)
                <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>
                    {{ ucwords(str_replace('_', ' ', $role->name)) }}
                </option>
            @endforeach
        </x-form-select>
        <x-form-select name="is_active" label="" placeholder="Any Status" class="min-w-[130px]">
            <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
            <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
        </x-form-select>
        <x-btn type="submit" variant="ghost" size="sm">Apply</x-btn>
        @if(request()->hasAny(['search','branch_id','role','is_active']))
            <x-btn :href="route('users.index')" variant="ghost" size="sm">Clear</x-btn>
        @endif
    </form>

    <x-data-table title="Accounts">
        <x-slot:tableHeader>
            <span class="font-mono text-[11px] text-ink4">{{ $users->total() }} results</span>
        </x-slot:tableHeader>
        <x-slot:head>
            <x-th>Name</x-th>
            <x-th>Email</x-th>
            <x-th>Branch</x-th>
            <x-th>Roles</x-th>
            <x-th>Status</x-th>
            <x-th>Last Login</x-th>
            <x-th></x-th>
        </x-slot:head>

        @forelse($users as $u)
            <tr class="hover:bg-cream transition-colors cursor-pointer"
                onclick="window.location='{{ route('users.show', $u) }}'">
                <x-td class="font-semibold">{{ $u->name }}</x-td>
                <x-td><span class="font-mono text-[11px] text-ink3">{{ $u->email }}</span></x-td>
                <x-td class="text-ink3">{{ $u->branch->name ?? '— All Branches' }}</x-td>
                <x-td>
                    <div class="flex flex-wrap gap-1">
                        @forelse($u->roles as $role)
                            <x-badge variant="{{ in_array($role->name, ['super_admin','admin']) ? 'purple' : 'navy' }}">
                                {{ ucwords(str_replace('_', ' ', $role->name)) }}
                            </x-badge>
                        @empty
                            <span class="font-mono text-[10px] text-ink4">No role</span>
                        @endforelse
                    </div>
                </x-td>
                <x-td>
                    <x-badge variant="{{ $u->is_active ? 'ok' : 'error' }}">
                        {{ $u->is_active ? 'Active' : 'Inactive' }}
                    </x-badge>
                </x-td>
                <x-td>
                    <span class="font-mono text-[10px] text-ink4">
                        {{ $u->last_login_at?->format('M d, Y g:i A') ?? 'Never' }}
                    </span>
                </x-td>
                <x-td>
                    <div class="flex gap-1.5" @click.stop>
                        @can('edit users')
                        <x-btn :href="route('users.edit', $u)" variant="ghost" size="sm">Edit</x-btn>
                        @endcan
                        @can('manage users')
                            @if($u->id !== auth()->id())
                                @if($u->is_active)
                                <form method="POST" action="{{ route('users.deactivate', $u) }}">
                                    @csrf
                                    <x-btn type="submit" variant="ghost" size="sm">Deactivate</x-btn>
                                </form>
                                @else
                                <form method="POST" action="{{ route('users.activate', $u) }}">
                                    @csrf
                                    <x-btn type="submit" variant="ghost" size="sm" class="!text-ok !border-ok/30 hover:!bg-ok/10">Activate</x-btn>
                                </form>
                                @endif
                            @endif
                        @endcan
                        @can('delete users')
                            @if($u->id !== auth()->id())
                            <x-btn variant="danger" size="sm"
                                   x-on:click="$dispatch('open-modal', 'delete-user-{{ $u->id }}')">Delete</x-btn>
                            <x-confirm-delete :name="'delete-user-'.$u->id" :action="route('users.destroy', $u)" :item-name="$u->name" />
                            @endif
                        @endcan
                    </div>
                </x-td>
            </tr>
        @empty
            <tr><td colspan="7">
                <x-empty-state title="No users found" description="Try adjusting your filters." icon="user">
                    @can('create users')
                    <x-slot:action><x-btn :href="route('users.create')" variant="primary" size="sm">+ Add User</x-btn></x-slot:action>
                    @endcan
                </x-empty-state>
            </td></tr>
        @endforelse

        <x-slot:pagination>{{ $users->withQueryString()->links('vendor.pagination.simple') }}</x-slot:pagination>
    </x-data-table>

</x-app-layout>
