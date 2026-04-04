<x-app-layout :title="isset($branch) ? 'Edit Branch' : 'New Branch'">

    <x-page-header
        overline="Network"
        :title="isset($branch) ? 'Edit Branch' : 'New Branch'"
        :subtitle="isset($branch) ? $branch->city : 'Add a new store location'"
    >
        <x-slot:actions>
            <x-btn :href="route('branches.index')" variant="ghost" size="sm">← Back</x-btn>
        </x-slot:actions>
    </x-page-header>

    <form method="POST"
          action="{{ isset($branch) ? route('branches.update', $branch) : route('branches.store') }}"
          class="max-w-xl">
        @csrf
        @if(isset($branch)) @method('PUT') @endif

        <div class="bg-white border border-cream3 rounded-lg p-6 flex flex-col gap-5">

            <x-form-input
                label="Branch Name"
                name="name"
                :value="old('name', $branch->name ?? '')"
                required
                placeholder="e.g. VapeHub — Quezon City"
            />

            <x-form-input
                label="Street Address"
                name="address"
                :value="old('address', $branch->address ?? '')"
                required
                placeholder="e.g. 123 Timog Avenue, South Triangle"
            />

            <div class="grid grid-cols-2 gap-4">
                <x-form-input
                    label="City"
                    name="city"
                    :value="old('city', $branch->city ?? '')"
                    required
                    placeholder="e.g. Quezon City"
                />
                <x-form-input
                    label="Contact Number"
                    name="contact_number"
                    :value="old('contact_number', $branch->contact_number ?? '')"
                    placeholder="09XXXXXXXXX"
                />
            </div>

            @if(isset($branch))
                <label class="flex items-center gap-2.5 cursor-pointer pt-1">
                    <input type="checkbox" name="is_active" value="1"
                           {{ old('is_active', $branch->is_active ?? true) ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-cream3 text-terra focus:ring-terra/30">
                    <span class="text-[13px] text-ink2">Branch is active</span>
                </label>
            @endif
        </div>

        <div class="flex items-center gap-3 mt-5">
            <x-btn type="submit" variant="primary">
                {{ isset($branch) ? 'Save Changes' : 'Create Branch' }}
            </x-btn>
            <x-btn :href="route('branches.index')" variant="ghost">Cancel</x-btn>
        </div>
    </form>

</x-app-layout>
