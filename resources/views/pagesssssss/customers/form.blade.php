<x-app-layout :title="isset($customer) ? 'Edit Customer' : 'New Customer'">

    <x-page-header
        overline="Loyalty & CRM"
        :title="isset($customer) ? 'Edit Customer' : 'New Customer'"
        :subtitle="isset($customer) ? $customer->full_name : 'Register a new loyalty member'"
    >
        <x-slot:actions>
            <x-btn :href="route('customers.index')" variant="ghost" size="sm">← Back</x-btn>
        </x-slot:actions>
    </x-page-header>

    <form method="POST"
          action="{{ isset($customer) ? route('customers.update', $customer) : route('customers.store') }}"
          class="max-w-xl">
        @csrf
        @if(isset($customer)) @method('PUT') @endif

        <div class="bg-white border border-cream3 rounded-lg p-6 flex flex-col gap-5">

            <div class="grid grid-cols-2 gap-4">
                <x-form-input label="First Name" name="first_name"
                              :value="old('first_name', $customer->first_name ?? '')" required />
                <x-form-input label="Last Name"  name="last_name"
                              :value="old('last_name', $customer->last_name ?? '')" required />
            </div>

            <x-form-input label="Email Address" name="email" type="email"
                          :value="old('email', $customer->email ?? '')"
                          placeholder="optional — for receipts & promos" />

            <x-form-input label="Phone Number" name="phone"
                          :value="old('phone', $customer->phone ?? '')"
                          placeholder="09XXXXXXXXX" />

            <x-form-input label="Date of Birth" name="birth_date" type="date"
                          :value="old('birth_date', isset($customer) ? $customer->birth_date?->format('Y-m-d') : '')"
                          help="Required for age verification. Must be 18+" />

            <x-form-select label="Registered Branch" name="registered_branch_id">
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}"
                        {{ old('registered_branch_id', $customer->registered_branch_id ?? auth()->user()->branch_id) == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </x-form-select>

        </div>

        <div class="flex items-center gap-3 mt-5">
            <x-btn type="submit" variant="primary">
                {{ isset($customer) ? 'Save Changes' : 'Register Customer' }}
            </x-btn>
            <x-btn :href="route('customers.index')" variant="ghost">Cancel</x-btn>
        </div>
    </form>

</x-app-layout>
