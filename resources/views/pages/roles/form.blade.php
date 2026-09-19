<x-app-layout :title="isset($role) ? 'Edit Role' : 'New Role'">

    <x-page-header
        overline="Human Resources · Access"
        :title="isset($role) ? ucwords(str_replace('_', ' ', $role->name)) : 'New Role'"
        :subtitle="isset($role) ? 'Editing permission set' : 'Define a custom role'"
    >
        <x-slot:actions>
            <x-btn :href="route('roles.index')" variant="ghost" size="sm">← Back</x-btn>
        </x-slot:actions>
    </x-page-header>

    <form method="POST"
          action="{{ isset($role) ? route('roles.update', $role) : route('roles.store') }}"
          class="max-w-4xl">
        @csrf
        @if(isset($role)) @method('PUT') @endif

        <div class="mb-6 max-w-sm">
            <x-form-input label="Role Name" name="name" :value="old('name', $role->name ?? '')"
                          :disabled="isset($isProtected) && $isProtected" required
                          help="{{ isset($isProtected) && $isProtected ? 'Built-in system role — name cannot be changed.' : 'Lowercase, underscore-separated, e.g. warehouse_staff.' }}" />
        </div>

        <div x-data="{
            selected: {{ json_encode($assignedNames->values()) }},
            toggle(name) {
                const i = this.selected.indexOf(name);
                i === -1 ? this.selected.push(name) : this.selected.splice(i, 1);
            },
            toggleGroup(names, checked) {
                names.forEach(name => {
                    const i = this.selected.indexOf(name);
                    if (checked && i === -1) this.selected.push(name);
                    if (!checked && i !== -1) this.selected.splice(i, 1);
                });
            },
            groupChecked(names) { return names.every(n => this.selected.includes(n)); },
        }">
            <template x-for="name in selected" :key="name">
                <input type="hidden" name="permissions[]" :value="name">
            </template>

            <div class="flex flex-col gap-4">
                @foreach($permissionGroups as $module => $permissions)
                    @php $names = $permissions->pluck('name')->all(); @endphp
                    <div class="border border-cream3 rounded-lg overflow-hidden">
                        <div class="flex items-center justify-between px-4 py-2.5 bg-cream border-b border-cream3">
                            <p class="erp-label !mb-0">{{ $module }}</p>
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="checkbox"
                                       x-bind:checked="groupChecked({{ json_encode($names) }})"
                                       x-on:change="toggleGroup({{ json_encode($names) }}, $event.target.checked)"
                                       class="w-3.5 h-3.5 rounded border-cream3 text-terra focus:ring-terra/30">
                                <span class="font-mono text-[9px] text-ink4 uppercase tracking-wide">All</span>
                            </label>
                        </div>
                        <div class="grid grid-cols-3 gap-2.5 px-4 py-3">
                            @foreach($permissions as $permission)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox"
                                           x-bind:checked="selected.includes('{{ $permission->name }}')"
                                           x-on:change="toggle('{{ $permission->name }}')"
                                           class="w-3.5 h-3.5 rounded border-cream3 text-terra focus:ring-terra/30">
                                    <span class="text-[12px] text-ink2">{{ ucfirst($permission->name) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex items-center gap-3 pt-6">
            <x-btn type="submit" variant="primary">
                {{ isset($role) ? 'Save Changes' : 'Create Role' }}
            </x-btn>
            <x-btn :href="route('roles.index')" variant="ghost">Cancel</x-btn>
        </div>
    </form>

</x-app-layout>
