<x-app-layout title="New Purchase Order">

    <x-page-header overline="Procurement" title="New Purchase Order" subtitle="Create a purchase order for a supplier">
        <x-slot:actions>
            <x-btn :href="route('purchasing.index')" variant="ghost" size="sm">← Back</x-btn>
        </x-slot:actions>
    </x-page-header>

    <form method="POST" action="{{ route('purchasing.store') }}" x-data="poForm()" class="max-w-4xl">
        @csrf

        <div class="bg-white border border-cream3 rounded-lg p-6 mb-5">
            <p class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 mb-4">Order Details</p>

            <div class="grid grid-cols-2 gap-5 mb-5">
                <x-form-select label="Supplier" name="supplier_id" required>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->company_name }}
                        </option>
                    @endforeach
                </x-form-select>

                <x-form-select label="Destination Branch" name="branch_id" required>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}"
                            {{ old('branch_id', auth()->user()->branch_id) == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </x-form-select>
            </div>

            <div class="grid grid-cols-2 gap-5 mb-5">
                <x-form-input label="Order Date" name="order_date" type="date"
                              :value="old('order_date', now()->format('Y-m-d'))" required />
                <x-form-input label="Expected Delivery Date" name="expected_date" type="date"
                              :value="old('expected_date', now()->addDays(7)->format('Y-m-d'))" />
            </div>

            <div>
                <label class="block font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 mb-1.5">Notes (optional)</label>
                <textarea name="notes" rows="2"
                          class="w-full bg-cream border border-cream3 rounded-md px-3 py-2 text-[13px] font-sans text-ink
                                 placeholder-ink4 outline-none focus:border-terra focus:ring-1 focus:ring-terra/20 resize-none transition-colors"
                          placeholder="Delivery instructions, priority notes…">{{ old('notes') }}</textarea>
            </div>
        </div>

        {{-- Line items --}}
        <div class="bg-white border border-cream3 rounded-lg p-6 mb-5">
            <div class="flex items-center justify-between mb-4">
                <p class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4">Line Items</p>
                <button type="button" @click="addItem()"
                        class="text-[11px] font-medium text-terra hover:underline">+ Add Item</button>
            </div>

            <div class="flex flex-col gap-3">
                <template x-for="(item, i) in items" :key="i">
                    <div class="grid grid-cols-[1fr_120px_120px_40px] gap-3 items-start">
                        <div>
                            <label class="block font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 mb-1.5">Product</label>
                            <select :name="'items[' + i + '][product_id]'"
                                    @change="updateCost(i, $event)"
                                    required
                                    class="w-full bg-cream border border-cream3 rounded-md px-3 py-2 text-[12px] font-sans text-ink
                                           outline-none focus:border-terra transition-colors">
                                <option value="">Select product…</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" data-cost="{{ $product->unit_cost }}">
                                        {{ $product->name }} ({{ $product->sku }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 mb-1.5">Qty</label>
                            <input :name="'items[' + i + '][quantity_ordered]'"
                                   type="number" min="1" x-model.number="item.qty"
                                   @input="calcTotal()"
                                   class="w-full bg-cream border border-cream3 rounded-md px-3 py-2 text-[12px] font-sans text-ink
                                          outline-none focus:border-terra transition-colors">
                        </div>
                        <div>
                            <label class="block font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 mb-1.5">Unit Cost (₱)</label>
                            <input :name="'items[' + i + '][unit_cost]'"
                                   type="number" step="0.01" min="0" x-model.number="item.cost"
                                   @input="calcTotal()"
                                   class="w-full bg-cream border border-cream3 rounded-md px-3 py-2 text-[12px] font-sans text-ink
                                          outline-none focus:border-terra transition-colors">
                        </div>
                        <div class="pt-6">
                            <button type="button" @click="removeItem(i)"
                                    x-show="items.length > 1"
                                    class="text-ink4 hover:text-danger transition-colors mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Running total --}}
            <div class="mt-5 pt-4 border-t border-cream2 flex justify-end">
                <div class="text-right">
                    <p class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 mb-1">Estimated Total</p>
                    <p class="font-serif text-[24px] font-bold text-terra"
                       x-text="'₱' + orderTotal.toLocaleString('en', { minimumFractionDigits: 2 })">₱0.00</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <x-btn type="submit" variant="primary">Create Purchase Order</x-btn>
            <x-btn :href="route('purchasing.index')" variant="ghost">Cancel</x-btn>
        </div>
    </form>

    @push('scripts')
    <script>
    function poForm() {
        return {
            items: [{ qty: 1, cost: 0 }],
            orderTotal: 0,
    
            addItem()    { this.items.push({ qty: 1, cost: 0 }); },
            removeItem(i){ this.items.splice(i, 1); this.calcTotal(); },
    
            updateCost(i, event) {
                const opt  = event.target.selectedOptions[0];
                const cost = parseFloat(opt?.dataset?.cost ?? 0);
                this.items[i].cost = cost;
                this.calcTotal();
            },
    
            calcTotal() {
                this.orderTotal = this.items.reduce((s, item) => s + (item.qty * item.cost), 0);
            },
        }
    }
    </script>
    @endpush
</x-app-layout>

