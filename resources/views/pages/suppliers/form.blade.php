<x-app-layout :title="isset($supplier) ? 'Edit Supplier' : 'New Supplier'">

    <x-page-header
        overline="Procurement · Suppliers"
        :title="isset($supplier) ? 'Edit Supplier' : 'New Supplier'"
        :subtitle="isset($supplier) ? $supplier->company_name : 'Register a new supplier'"
    >
        <x-slot:actions>
            <x-btn :href="route('suppliers.index')" variant="ghost" size="sm">← All Suppliers</x-btn>
        </x-slot:actions>
    </x-page-header>

    <form method="POST"
          action="{{ isset($supplier) ? route('suppliers.update', $supplier) : route('suppliers.store') }}"
          class="max-w-2xl">
        @csrf
        @if(isset($supplier)) @method('PUT') @endif

        <div class="bg-white border border-cream3 rounded-lg p-6 flex flex-col gap-5 mb-5">
            <h3 class="font-display text-[12px] uppercase tracking-wide text-ink">Company Details</h3>

            <x-form-input label="Company Name" name="company_name"
                          :value="old('company_name', $supplier->company_name ?? '')"
                          required placeholder="e.g. Vape Wholesale PH Inc." />

            <div class="grid grid-cols-2 gap-4">
                <x-form-input label="Contact Person" name="contact_person"
                              :value="old('contact_person', $supplier->contact_person ?? '')"
                              placeholder="Primary contact name" />
                <x-form-input label="Phone" name="phone"
                              :value="old('phone', $supplier->phone ?? '')"
                              placeholder="09XXXXXXXXX" />
            </div>

            <x-form-input label="Email Address" name="email" type="email"
                          :value="old('email', $supplier->email ?? '')"
                          placeholder="orders@supplier.com" />

            <div class="mb-0">
                <label class="erp-label mb-1.5 block">Address <span class="text-ink4 font-sans normal-case tracking-normal text-[11px]">(optional)</span></label>
                <textarea name="address" rows="2"
                          class="erp-input resize-none"
                          placeholder="Full business address…">{{ old('address', $supplier->address ?? '') }}</textarea>
                @error('address') <p class="erp-field-error mt-1">{{ $message }}</p> @enderror
            </div>

            <x-form-input label="TIN (Tax Identification Number)" name="tin"
                          :value="old('tin', $supplier->tin ?? '')"
                          placeholder="000-000-000-000"
                          help="Required for official receipts." />
        </div>

        @if(isset($supplier))
        <div class="bg-white border border-cream3 rounded-lg px-5 py-4 mb-5 flex items-center justify-between">
            <div>
                <p class="text-[13px] font-semibold text-ink">Active Status</p>
                <p class="font-mono text-[10px] text-ink4 mt-0.5">Inactive suppliers won't appear in purchasing forms.</p>
            </div>
            <label class="flex items-center gap-2.5 cursor-pointer">
                <input type="checkbox" name="is_active" value="1"
                       {{ old('is_active', $supplier->is_active ?? true) ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-cream3 text-purple focus:ring-purple/20">
                <span class="text-[13px] text-ink2">Supplier is active</span>
            </label>
        </div>
        @endif

        <div class="flex items-center gap-3">
            <x-btn type="submit" variant="primary">
                {{ isset($supplier) ? 'Save Changes' : 'Register Supplier' }}
            </x-btn>
            <x-btn :href="route('suppliers.index')" variant="ghost">Cancel</x-btn>
        </div>
    </form>

</x-app-layout>
