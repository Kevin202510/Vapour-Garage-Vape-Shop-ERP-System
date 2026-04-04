<x-app-layout title="Point of Sale">

    <x-page-header
        overline="Point of Sale"
        title="New Transaction"
        :subtitle="'Branch: ' . (auth()->user()->branch->name ?? 'All Branches') . ' · ' . now()->format('F d, Y')"
    >
        <x-slot:actions>
            <x-btn variant="ghost" size="sm" x-on:click="$dispatch('open-modal', 'held-orders')">Held Orders</x-btn>
            <x-btn variant="ghost" size="sm" x-on:click="clearCart()" style="color:var(--color-danger,#b83232);border-color:rgba(184,50,50,0.3)">Clear</x-btn>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-[1fr_340px] gap-5 h-[calc(100vh-168px)]"
         x-data="posApp(@js($products), @js(auth()->user()->branch_id), @js(csrf_token()))">

        {{-- Product panel --}}
        <div class="bg-white border border-cream3 rounded-lg p-5 flex flex-col overflow-hidden">

            {{-- Search --}}
            <div class="flex items-center gap-2 bg-cream border border-cream3 rounded-md px-3.5 py-2 mb-3.5 focus-within:border-terra transition-colors">
                <svg class="w-3.5 h-3.5 text-ink4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" x-model="search" placeholder="Search by name or SKU…"
                       class="bg-transparent border-none outline-none text-[13px] font-sans text-ink placeholder-ink4 flex-1">
            </div>

            {{-- Category tabs --}}
            <div class="flex gap-1 bg-cream border border-cream3 rounded-md p-[3px] mb-3.5 w-fit">
                <button @click="activeCategory = null" :class="activeCategory === null ? 'bg-white text-ink shadow-sm' : 'text-ink4'"
                        class="px-3.5 py-[5px] rounded text-[11px] font-mono tracking-wide transition-all">All</button>
                @foreach($categories as $cat)
                    <button @click="activeCategory = {{ $cat->id }}"
                            :class="activeCategory === {{ $cat->id }} ? 'bg-white text-ink shadow-sm' : 'text-ink4'"
                            class="px-3.5 py-[5px] rounded text-[11px] font-mono tracking-wide transition-all">
                        {{ $cat->name }}
                    </button>
                @endforeach
            </div>

            {{-- Product grid --}}
            <div class="grid grid-cols-3 gap-2.5 overflow-y-auto flex-1 pr-1">
                <template x-for="product in filteredProducts" :key="product.id">
                    <div @click="addToCart(product)"
                         class="bg-cream border border-cream3 rounded-lg p-3.5 cursor-pointer transition-all
                                hover:border-terra hover:bg-white hover:shadow-[0_2px_12px_rgba(196,94,60,0.1)]">
                        <p class="font-mono text-[8px] tracking-[0.14em] uppercase text-ink4 mb-1.5" x-text="product.category_name"></p>
                        <p class="text-[11.5px] font-semibold text-ink leading-snug mb-2" x-text="product.name"></p>
                        <p class="font-mono text-[9px] text-ink4 mb-2" x-text="product.sku"></p>
                        <p class="font-serif text-[16px] font-bold text-terra" x-text="'₱' + Number(product.selling_price).toLocaleString()"></p>
                        <p class="font-mono text-[9px] mt-0.5"
                           :class="product.stock <= product.reorder_level ? 'text-warn' : 'text-ok'"
                           x-text="product.stock + ' in stock'"></p>
                    </div>
                </template>
                <div x-show="filteredProducts.length === 0" class="col-span-3 flex items-center justify-center py-12 text-ink4 font-mono text-[11px]">
                    No products match your search.
                </div>
            </div>
        </div>

        {{-- Cart panel --}}
        <div class="bg-white border border-cream3 rounded-lg p-5 flex flex-col">
            <div class="flex items-center justify-between mb-3.5">
                <h3 class="font-serif text-[15px] font-bold text-ink">Current Order</h3>
                <x-badge variant="terra" x-text="cart.length + ' items'">0 items</x-badge>
            </div>

            {{-- Cart items --}}
            <div class="flex-1 overflow-y-auto flex flex-col gap-2 mb-3.5">
                <template x-for="(item, index) in cart" :key="item.id">
                    <div class="flex items-center gap-2.5 px-3 py-2.5 bg-cream rounded-md border border-cream3">
                        <div class="flex-1 min-w-0">
                            <p class="text-[11.5px] font-medium text-ink truncate" x-text="item.name"></p>
                            <p class="font-mono text-[9px] text-ink4 mt-0.5" x-text="item.sku + ' · ₱' + Number(item.unit_price).toLocaleString() + ' each'"></p>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button @click="decrementQty(index)"
                                    class="w-5 h-5 rounded bg-cream2 hover:bg-cream3 text-ink2 flex items-center justify-center text-[13px] transition-colors">−</button>
                            <span class="font-mono text-[11px] min-w-[18px] text-center" x-text="item.quantity"></span>
                            <button @click="incrementQty(index)"
                                    class="w-5 h-5 rounded bg-cream2 hover:bg-cream3 text-ink2 flex items-center justify-center text-[13px] transition-colors">+</button>
                        </div>
                        <p class="font-serif text-[13px] font-bold text-ink whitespace-nowrap"
                           x-text="'₱' + Number(item.unit_price * item.quantity).toLocaleString()"></p>
                        <button @click="removeFromCart(index)" class="text-ink4 hover:text-danger transition-colors ml-0.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>
                </template>
                <div x-show="cart.length === 0" class="flex items-center justify-center flex-1 text-ink4 font-mono text-[11px] py-8">
                    Cart is empty — tap a product to add
                </div>
            </div>

            {{-- Totals --}}
            <div class="border-t border-cream2 pt-3 flex flex-col gap-1.5 mb-3">
                <div class="flex justify-between text-[12px]">
                    <span class="text-ink4">Subtotal</span>
                    <span class="font-mono" x-text="'₱' + subtotal.toLocaleString('en', {minimumFractionDigits:2})"></span>
                </div>
                <div class="flex justify-between text-[12px]">
                    <span class="text-ink4">Discount</span>
                    <span class="font-mono text-ok">— ₱0.00</span>
                </div>
                <div class="flex justify-between text-[12px]">
                    <span class="text-ink4">VAT (12%)</span>
                    <span class="font-mono" x-text="'₱' + vat.toLocaleString('en', {minimumFractionDigits:2})"></span>
                </div>
                <div class="flex justify-between font-serif text-[17px] font-bold mt-1.5">
                    <span>Total Due</span>
                    <span class="text-terra" x-text="'₱' + total.toLocaleString('en', {minimumFractionDigits:2})"></span>
                </div>
            </div>

            {{-- Payment method --}}
            <p class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 mb-2">Payment Method</p>
            <div class="grid grid-cols-3 gap-1.5 mb-3">
                @foreach(['cash' => 'Cash', 'gcash' => 'GCash', 'maya' => 'Maya', 'card' => 'Card', 'bank_transfer' => 'Bank', 'mixed' => 'Mixed'] as $val => $lbl)
                    <button @click="paymentMethod = '{{ $val }}'"
                            :class="paymentMethod === '{{ $val }}' ? 'border-terra bg-terra/8 text-terra' : 'border-cream3 bg-cream text-ink4'"
                            class="py-2 rounded-md font-mono text-[9px] uppercase tracking-[0.08em] border transition-all hover:border-terra hover:text-terra">
                        {{ $lbl }}
                    </button>
                @endforeach
            </div>

            {{-- Checkout --}}
            <button @click="checkout()"
                    :disabled="cart.length === 0"
                    class="w-full py-3.5 bg-navy text-white font-serif text-[15px] font-bold rounded-md
                           hover:bg-navy-dark hover:-translate-y-px hover:shadow-lg transition-all
                           disabled:opacity-40 disabled:cursor-not-allowed disabled:transform-none"
                    x-text="'Charge ₱' + total.toLocaleString('en', {minimumFractionDigits:2})">
                Charge ₱0.00
            </button>
        </div>
    </div>

    @push('scripts')
    <script>
    function posApp(products, branchId, csrfToken) {
        return {
            products, branchId,
            cart: [],
            search: '',
            activeCategory: null,
            paymentMethod: 'cash',
    
            get filteredProducts() {
                return this.products.filter(p => {
                    const matchSearch = !this.search ||
                        p.name.toLowerCase().includes(this.search.toLowerCase()) ||
                        p.sku.toLowerCase().includes(this.search.toLowerCase());
                    const matchCat = this.activeCategory === null || p.category_id === this.activeCategory;
                    return matchSearch && matchCat && p.is_active;
                });
            },
    
            get subtotal() {
                return this.cart.reduce((s, i) => s + (i.unit_price * i.quantity), 0);
            },
            get vat()      { return Math.round(this.subtotal * 0.12 * 100) / 100; },
            get total()    { return Math.round((this.subtotal + this.vat) * 100) / 100; },
    
            addToCart(product) {
                const existing = this.cart.find(i => i.id === product.id);
                if (existing) { existing.quantity++; return; }
                this.cart.push({ id: product.id, name: product.name, sku: product.sku,
                                 unit_price: parseFloat(product.selling_price), quantity: 1 });
            },
            incrementQty(i) { this.cart[i].quantity++; },
            decrementQty(i) { if (this.cart[i].quantity > 1) this.cart[i].quantity--; else this.removeFromCart(i); },
            removeFromCart(i) { this.cart.splice(i, 1); },
            clearCart() { this.cart = []; },
    
            async checkout() {
                if (!this.cart.length) return;
                const res = await fetch('/api/sales', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({
                        branch_id: this.branchId,
                        cashier_id: {{ auth()->user()->employee_id ?? 'null' }},
                        payment_method: this.paymentMethod,
                        amount_tendered: this.total,
                        items: this.cart.map(i => ({
                            product_id: i.id,
                            quantity: i.quantity,
                            unit_price: i.unit_price,
                        })),
                    }),
                });
                const data = await res.json();
                if (res.ok) {
                    alert('Transaction complete! TXN: ' + data.data.transaction_number);
                    this.clearCart();
                } else {
                    alert('Error: ' + (data.message || 'Transaction failed'));
                }
            },
        }
    }
    </script>
    @endpush
</x-app-layout>

