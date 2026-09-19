<x-app-layout title="Point of Sale">

    <x-page-header overline="Point of Sale" title="New Transaction"
                   :subtitle="'Branch: ' . ($activeBranch->name ?? 'Select Branch') . ' · ' . now()->format('F d, Y')">
        <x-slot:actions>
            @if(auth()->user()->isSuperAdmin() && $branches->count() > 1)
            <form method="GET" action="{{ route('pos.index') }}" class="flex items-center gap-2">
                <select name="branch_id" onchange="this.form.submit()"
                        class="bg-cream border border-cream3 text-ink2 text-[12px] px-3 py-[5px] rounded-md outline-none focus:border-purple font-sans">
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </form>
            @endif
            <x-btn variant="ghost" size="sm" x-on:click="$dispatch('open-modal', 'held-orders')">Held Orders</x-btn>
            <x-btn variant="danger" size="sm" x-on:click="clearCart()">Clear</x-btn>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-[1fr_360px] gap-5 lg:h-[calc(100vh-168px)]"
         x-data="posApp(@js($products), @js($customers), @js($branchId), @js(csrf_token()))">

        {{-- ══════════════════════════════════════
             LEFT: Product catalogue
        ══════════════════════════════════════ --}}
        <div class="bg-white border border-cream3 rounded-xl p-5 flex flex-col overflow-hidden shadow-card">

            {{-- Search --}}
            <div class="flex items-center gap-2 bg-cream border border-cream3 rounded-lg px-3.5 py-2 mb-3.5
                        focus-within:border-purple focus-within:ring-2 focus-within:ring-purple/10 transition-all">
                <svg class="w-3.5 h-3.5 text-ink4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" x-model="search" placeholder="Search by name or SKU…"
                       class="bg-transparent border-none outline-none text-[13px] font-sans text-ink placeholder-ink4 flex-1">
                <button x-show="search.length > 0" @click="search = ''" class="text-ink4 hover:text-ink transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            {{-- Category tabs --}}
            <div class="flex gap-1 bg-cream border border-cream3 rounded-lg p-[3px] mb-3.5 overflow-x-auto scrollbar-none">
                <button @click="activeCategory = null"
                        :class="activeCategory === null ? 'bg-purple text-white shadow-sm' : 'text-ink4 hover:text-ink'"
                        class="px-3.5 py-[5px] rounded-md text-[11px] font-mono tracking-wide transition-all whitespace-nowrap flex-shrink-0">
                    All
                </button>
                @foreach($categories as $cat)
                <button @click="activeCategory = {{ $cat->id }}"
                        :class="activeCategory === {{ $cat->id }} ? 'bg-purple text-white shadow-sm' : 'text-ink4 hover:text-ink'"
                        class="px-3.5 py-[5px] rounded-md text-[11px] font-mono tracking-wide transition-all whitespace-nowrap flex-shrink-0">
                    {{ $cat->name }}
                </button>
                @endforeach
            </div>

            {{-- Product grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 lg:overflow-y-auto flex-1 content-start pr-1">
                <template x-for="product in filteredProducts" :key="product.id">
                    <div @click="addToCart(product)"
                         class="bg-cream border border-cream3 rounded-xl p-3.5 cursor-pointer transition-all relative
                                hover:border-purple hover:bg-purple/[0.03] hover:shadow-[0_2px_12px_rgba(124,34,212,0.1)]">

                        {{-- Out of stock overlay --}}
                        <template x-if="product.stock <= 0">
                            <div class="absolute inset-0 bg-white/90 rounded-xl flex items-center justify-center z-10 backdrop-blur-[1px]">
                                <span class="font-mono text-[9px] text-danger uppercase tracking-widest font-semibold">Out of stock</span>
                            </div>
                        </template>

                        <p class="font-mono text-[8px] tracking-[0.14em] uppercase text-ink4 mb-1.5 truncate"
                           x-text="product.category_name"></p>
                        <p class="text-[11.5px] font-semibold text-ink leading-snug mb-1.5 truncate" x-text="product.name"></p>
                        <p class="font-mono text-[9px] text-ink4 mb-2" x-text="product.sku"></p>
                        <p class="font-display text-[15px] font-bold text-purple"
                           x-text="'₱' + Number(product.selling_price).toLocaleString()"></p>
                        <p class="font-mono text-[9px] mt-0.5"
                           :class="product.stock <= product.reorder_level ? 'text-warn' : 'text-ok'"
                           x-text="product.stock + ' in stock'"></p>
                    </div>
                </template>

                <div x-show="filteredProducts.length === 0"
                     class="col-span-3 flex flex-col items-center justify-center py-12 text-ink4">
                    <svg class="w-8 h-8 mb-2 text-cream3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <p class="font-mono text-[11px]">No products found.</p>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════
             RIGHT: Cart + Checkout
        ══════════════════════════════════════ --}}
        <div class="bg-white border border-cream3 rounded-xl flex flex-col overflow-hidden shadow-card">

            {{-- CUSTOMER SECTION --}}
            <div class="px-5 pt-5 pb-4 border-b border-cream3 flex-shrink-0">

                {{-- Search state --}}
                <div x-show="!selectedCustomer && !isWalkIn">
                    <p class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 mb-2">Customer</p>
                    <div class="relative">
                        <div class="flex items-center gap-2 bg-cream border border-cream3 rounded-lg px-3 py-2
                                    focus-within:border-purple focus-within:ring-2 focus-within:ring-purple/10 transition-all">
                            <svg class="w-3.5 h-3.5 text-ink4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                            </svg>
                            <input type="text" x-model="customerSearch" @input="searchCustomers()"
                                   @keydown.escape="customerSearch='';customerResults=[]"
                                   placeholder="Name, phone, or email…"
                                   class="bg-transparent border-none outline-none text-[12px] font-sans text-ink placeholder-ink4 flex-1">
                            <button x-show="customerSearch.length > 0" @click="customerSearch='';customerResults=[]"
                                    class="text-ink4 hover:text-danger transition-colors">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Results dropdown --}}
                        <div x-show="customerResults.length > 0"
                             class="absolute top-full left-0 right-0 mt-1 bg-white border border-cream3 rounded-xl shadow-card z-50 max-h-52 overflow-y-auto">
                            <template x-for="cust in customerResults" :key="cust.id">
                                <div @click="selectCustomer(cust)"
                                     class="flex items-center gap-3 px-3 py-2.5 hover:bg-cream cursor-pointer border-b border-cream3 last:border-0 transition-colors">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-display font-bold text-[11px] text-white flex-shrink-0 bg-purple">
                                        <span x-text="cust.name.charAt(0).toUpperCase()"></span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <p class="text-[12px] font-semibold text-ink" x-text="cust.name"></p>
                                            <template x-if="cust.visit_count > 0">
                                                <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[8px] font-mono bg-ok/10 text-ok">
                                                    ↩ <span x-text="cust.visit_count + 'x'"></span>
                                                </span>
                                            </template>
                                            <template x-if="cust.visit_count === 0">
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[8px] font-mono bg-purple/8 text-purple">NEW</span>
                                            </template>
                                        </div>
                                        <p class="font-mono text-[9px] text-ink4 mt-0.5"
                                           x-text="(cust.phone || cust.email || 'No contact') + ' · ' + Number(cust.loyalty_points).toLocaleString() + ' pts'"></p>
                                    </div>
                                    <span class="font-mono text-[8px] px-1.5 py-0.5 rounded uppercase flex-shrink-0"
                                          :class="{
                                              'bg-purple/10 text-purple': cust.tier === 'platinum',
                                              'bg-gold/10 text-gold':     cust.tier === 'gold',
                                              'bg-ink4/10 text-ink3':     cust.tier === 'silver',
                                              'bg-cream2 text-ink4':      cust.tier === 'bronze'
                                          }"
                                          x-text="cust.tier"></span>
                                </div>
                            </template>
                        </div>

                        {{-- No results --}}
                        <div x-show="customerSearch.length >= 2 && customerResults.length === 0 && !customerSearching"
                             class="absolute top-full left-0 right-0 mt-1 bg-white border border-cream3 rounded-xl shadow-card z-50 p-3 text-center">
                            <p class="font-mono text-[10px] text-ink4 mb-2">No customer found</p>
                            <button @click="$dispatch('open-modal', 'quick-add-customer')"
                                    class="text-[11px] font-medium text-purple hover:underline">+ Register new customer</button>
                        </div>
                    </div>
                    <button @click="proceedAsWalkIn()"
                            class="mt-2 text-[11px] font-mono text-ink4 hover:text-ink3 underline underline-offset-2 transition-colors">
                        Continue as walk-in →
                    </button>
                </div>

                {{-- Walk-in state --}}
                <div x-show="isWalkIn && !selectedCustomer" class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-ink4/10 flex items-center justify-content-center p-1.5">
                            <svg class="w-full h-full text-ink4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                        <span class="font-mono text-[11px] text-ink3 uppercase tracking-wide">Walk-in customer</span>
                    </div>
                    <button @click="isWalkIn=false" class="font-mono text-[10px] text-purple hover:underline transition-colors">
                        Search customer
                    </button>
                </div>

                {{-- Customer selected --}}
                <div x-show="selectedCustomer">
                    <div class="flex items-start gap-3 p-3 rounded-lg border transition-all"
                         :class="isReturning ? 'bg-ok/5 border-ok/20' : 'bg-cream border-cream3'">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-display font-bold text-sm text-white flex-shrink-0 bg-purple">
                            <span x-text="selectedCustomer?.name?.charAt(0)?.toUpperCase()"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5 flex-wrap mb-1">
                                <p class="text-[13px] font-semibold text-ink" x-text="selectedCustomer?.name"></p>
                                <template x-if="isReturning">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-mono bg-ok text-white">
                                        ↩ RETURNING · <span x-text="selectedCustomer?.visit_count + 'x'"></span>
                                    </span>
                                </template>
                                <template x-if="!isReturning && selectedCustomer">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-mono bg-purple/10 text-purple">★ NEW</span>
                                </template>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-[8px] uppercase tracking-wide px-1.5 py-0.5 rounded"
                                      :class="{
                                          'bg-purple/10 text-purple': selectedCustomer?.tier === 'platinum',
                                          'bg-gold/10 text-gold':     selectedCustomer?.tier === 'gold',
                                          'bg-ink4/10 text-ink3':     selectedCustomer?.tier === 'silver',
                                          'bg-cream2 text-ink4':      selectedCustomer?.tier === 'bronze'
                                      }"
                                      x-text="selectedCustomer?.tier"></span>
                                <span class="font-mono text-[9px] text-ink4"
                                      x-text="Number(selectedCustomer?.loyalty_points ?? 0).toLocaleString() + ' pts'"></span>
                            </div>
                            <template x-if="isReturning && selectedCustomer?.last_visit">
                                <p class="font-mono text-[9px] text-ok mt-1"
                                   x-text="'Last visited ' + selectedCustomer.last_visit"></p>
                            </template>
                        </div>
                        <button @click="clearCustomer()" class="text-ink4 hover:text-danger transition-colors flex-shrink-0 mt-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>

                    <template x-if="isReturning && selectedCustomer?.last_products?.length > 0">
                        <div class="mt-2 px-3 py-2 bg-ok/5 border border-ok/15 rounded-md">
                            <p class="font-mono text-[9px] text-ok uppercase tracking-wide mb-1.5">Previously bought</p>
                            <div class="flex flex-wrap gap-1">
                                <template x-for="prod in selectedCustomer.last_products" :key="prod">
                                    <span class="bg-cream2 text-ink3 font-mono text-[8px] px-1.5 py-0.5 rounded" x-text="prod"></span>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- CART ITEMS --}}
            <div class="flex-1 overflow-y-auto px-5 py-3 flex flex-col gap-2 min-h-0">
                <template x-for="(item, index) in cart" :key="item.id">
                    <div class="flex items-center gap-2.5 px-3 py-2.5 bg-cream rounded-lg border border-cream3">
                        <div class="flex-1 min-w-0">
                            <p class="text-[11.5px] font-medium text-ink truncate" x-text="item.name"></p>
                            <p class="font-mono text-[9px] text-ink4 mt-0.5"
                               x-text="item.sku + ' · ₱' + Number(item.unit_price).toLocaleString()"></p>
                        </div>
                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            <button @click="decrementQty(index)"
                                    class="w-6 h-6 rounded-md bg-white border border-cream3 hover:border-purple hover:text-purple text-ink3 flex items-center justify-center text-[14px] transition-colors font-bold">
                                −
                            </button>
                            <span class="font-mono text-[12px] min-w-[22px] text-center font-semibold text-ink" x-text="item.quantity"></span>
                            <button @click="incrementQty(index)"
                                    class="w-6 h-6 rounded-md bg-white border border-cream3 hover:border-purple hover:text-purple text-ink3 flex items-center justify-center text-[14px] transition-colors font-bold">
                                +
                            </button>
                        </div>
                        <p class="font-display text-[13px] font-bold text-ink whitespace-nowrap"
                           x-text="'₱' + Number(item.unit_price * item.quantity).toLocaleString()"></p>
                        <button @click="removeFromCart(index)" class="text-ink4 hover:text-danger transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>
                </template>
                <div x-show="cart.length === 0"
                     class="flex flex-col items-center justify-center flex-1 text-ink4 py-6 gap-2">
                    <svg class="w-8 h-8 text-cream3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/>
                    </svg>
                    <p class="font-mono text-[11px]">Cart is empty</p>
                </div>
            </div>

            {{-- TOTALS + CHECKOUT --}}
            <div class="px-5 py-4 border-t border-cream3 flex-shrink-0 bg-cream/50">

                {{-- Loyalty discount banner --}}
                <template x-if="selectedCustomer && loyaltyDiscount > 0">
                    <div class="flex items-center justify-between px-3 py-2 bg-ok/8 border border-ok/20 rounded-lg mb-3">
                        <span class="font-mono text-[9px] text-ok uppercase tracking-wide"
                              x-text="(selectedCustomer?.tier ?? '') + ' discount'"></span>
                        <span class="font-mono text-[11px] text-ok font-semibold"
                              x-text="'− ₱' + loyaltyDiscount.toLocaleString('en',{minimumFractionDigits:2})"></span>
                    </div>
                </template>

                {{-- Line totals --}}
                <div class="flex flex-col gap-1.5 mb-3">
                    <div class="flex justify-between text-[12px]">
                        <span class="text-ink4">Subtotal</span>
                        <span class="font-mono text-ink" x-text="'₱' + subtotal.toLocaleString('en',{minimumFractionDigits:2})"></span>
                    </div>
                    <template x-if="loyaltyDiscount > 0">
                        <div class="flex justify-between text-[12px]">
                            <span class="text-ok">Loyalty Discount</span>
                            <span class="font-mono text-ok" x-text="'− ₱' + loyaltyDiscount.toLocaleString('en',{minimumFractionDigits:2})"></span>
                        </div>
                    </template>
                    <div class="flex justify-between text-[12px]">
                        <span class="text-ink4">VAT (12%)</span>
                        <span class="font-mono text-ink" x-text="'₱' + vat.toLocaleString('en',{minimumFractionDigits:2})"></span>
                    </div>
                    <div class="flex justify-between items-center border-t border-cream3 pt-2 mt-1">
                        <span class="font-display text-[15px] font-bold text-ink">Total Due</span>
                        <span class="font-display text-[20px] font-bold text-purple"
                              x-text="'₱' + total.toLocaleString('en',{minimumFractionDigits:2})"></span>
                    </div>
                </div>

                <template x-if="selectedCustomer">
                    <p class="font-mono text-[9px] text-ink4 mb-3 text-right"
                       x-text="'Earns +' + pointsToEarn + ' loyalty pts this transaction'"></p>
                </template>

                {{-- Payment method tabs --}}
                <p class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 mb-2">Payment Method</p>
                <div class="grid grid-cols-3 gap-1.5 mb-3">
                    @foreach(['cash' => 'Cash', 'gcash' => 'GCash', 'maya' => 'Maya'] as $val => $lbl)
                    <button @click="paymentMethod = '{{ $val }}'"
                            :class="paymentMethod === '{{ $val }}'
                                ? 'border-purple bg-purple/8 text-purple font-semibold'
                                : 'border-cream3 bg-white text-ink4 hover:border-purple/40 hover:text-ink3'"
                            class="py-2 rounded-lg font-mono text-[10px] uppercase tracking-[0.08em] border transition-all">
                        {{ $lbl }}
                    </button>
                    @endforeach
                </div>

                {{-- Cash tendered section --}}
                <div x-show="paymentMethod === 'cash'" class="mb-3">
                    <p class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 mb-1.5">Cash Tendered</p>
                    <input type="number" x-model.number="amountTendered" placeholder="0.00"
                           class="w-full px-4 py-3 rounded-lg border border-cream3 bg-white text-ink text-[18px] font-bold mb-2.5
                                  focus:border-purple focus:ring-2 focus:ring-purple/10 outline-none transition-all">
                    <div class="grid grid-cols-3 gap-1.5 mb-2.5">
                        @foreach([100, 500, 1000] as $amt)
                        <button type="button" @click="amountTendered = {{ $amt }}"
                                class="py-2.5 rounded-lg bg-white border border-cream3 hover:border-purple hover:bg-purple/5
                                       text-ink font-bold text-[13px] transition-all">
                            ₱{{ number_format($amt) }}
                        </button>
                        @endforeach
                    </div>
                    <div class="flex justify-between items-center bg-cream border border-cream3 rounded-lg px-4 py-3">
                        <span class="font-mono text-[11px] uppercase tracking-wide text-ink4">Change</span>
                        <span class="font-display text-[22px] font-bold text-ok"
                              x-text="'₱' + change.toLocaleString('en',{minimumFractionDigits:2})"></span>
                    </div>
                </div>

                {{-- Checkout button --}}
                <button @click="checkout()" :disabled="cart.length === 0"
                        class="w-full py-3.5 bg-purple text-white font-display text-[15px] font-bold rounded-lg
                               hover:bg-purple-2 hover:-translate-y-px hover:shadow-purple transition-all
                               disabled:opacity-40 disabled:cursor-not-allowed disabled:transform-none disabled:shadow-none"
                        x-text="cart.length === 0 ? 'Add items to checkout' : 'Charge ₱' + total.toLocaleString('en',{minimumFractionDigits:2})">
                </button>
            </div>
        </div>
    </div>

    {{-- Quick Add Customer Modal --}}
    <x-modal name="quick-add-customer" title="Register New Customer" max-width="md">
        <form method="POST" action="{{ route('customers.store') }}">
            @csrf
            <input type="hidden" name="registered_branch_id" value="{{ auth()->user()->branch_id }}">
            <div class="grid grid-cols-2 gap-4 mb-4">
                <x-form-input label="First Name" name="first_name" required />
                <x-form-input label="Last Name"  name="last_name"  required />
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <x-form-input label="Phone" name="phone" placeholder="09XXXXXXXXX" />
                <x-form-input label="Email" name="email" type="email" />
            </div>
            <x-form-input label="Date of Birth" name="birth_date" type="date" help="Must be 18+" class="mb-0" />
            <div class="flex items-center justify-end gap-3 pt-3 mt-3 border-t border-cream3">
                <x-btn type="button" variant="ghost" size="sm" x-on:click="$dispatch('close-modal', 'quick-add-customer')">Cancel</x-btn>
                <x-btn type="submit" variant="primary" size="sm">Register & Select</x-btn>
            </div>
        </form>
    </x-modal>

    @push('scripts')
    <script>
    function posApp(products, customers, branchId, csrfToken) {
        return {
            products,
            customers,
            branchId,
            cart: [],
            search: '',
            activeCategory: null,
            paymentMethod: 'cash',
            amountTendered: 0,
            customerSearch: '',
            customerResults: [],
            customerSearching: false,
            selectedCustomer: null,
            isWalkIn: false,

            get filteredProducts() {
                return this.products.filter(p => {
                    const searchMatch = !this.search ||
                        p.name.toLowerCase().includes(this.search.toLowerCase()) ||
                        p.sku.toLowerCase().includes(this.search.toLowerCase());
                    const catMatch = this.activeCategory === null || p.category_id === this.activeCategory;
                    return searchMatch && catMatch && p.is_active;
                });
            },
            get isReturning() {
                return this.selectedCustomer && (this.selectedCustomer.visit_count ?? 0) > 0;
            },
            get loyaltyDiscount() {
                if (!this.selectedCustomer) return 0;
                const pct = { platinum: 0.15, gold: 0.10, silver: 0.05, bronze: 0 }[this.selectedCustomer.tier] ?? 0;
                return Math.round(this.subtotal * pct * 100) / 100;
            },
            get subtotal() {
                return this.cart.reduce((sum, item) => sum + (item.unit_price * item.quantity), 0);
            },
            get vat() {
                return Math.round((this.subtotal - this.loyaltyDiscount) * 0.12 * 100) / 100;
            },
            get total() {
                return Math.round((this.subtotal - this.loyaltyDiscount + this.vat) * 100) / 100;
            },
            get pointsToEarn() {
                return Math.floor(this.total / 10);
            },
            get change() {
                if (this.paymentMethod !== 'cash') return 0;
                return Math.max(this.amountTendered - this.total, 0);
            },

            searchCustomers() {
                const q = this.customerSearch.trim().toLowerCase();
                if (q.length < 2) { this.customerResults = []; return; }
                this.customerResults = this.customers.filter(c =>
                    (c.name  && c.name.toLowerCase().includes(q)) ||
                    (c.phone && c.phone.includes(q)) ||
                    (c.email && c.email.toLowerCase().includes(q))
                ).slice(0, 8);
            },
            selectCustomer(customer) {
                this.selectedCustomer = customer;
                this.isWalkIn = false;
                this.customerSearch = '';
                this.customerResults = [];
            },
            clearCustomer() {
                this.selectedCustomer = null;
                this.isWalkIn = false;
                this.customerSearch = '';
                this.customerResults = [];
            },
            proceedAsWalkIn() {
                this.selectedCustomer = null;
                this.isWalkIn = true;
                this.customerSearch = '';
                this.customerResults = [];
            },

            addToCart(product) {
                if (product.stock <= 0) return;
                const existing = this.cart.find(i => i.id === product.id);
                if (existing) { existing.quantity++; return; }
                this.cart.push({
                    id: product.id,
                    name: product.name,
                    sku: product.sku,
                    unit_price: parseFloat(product.selling_price),
                    quantity: 1,
                });
            },
            incrementQty(index) {
                const product = this.products.find(p => p.id === this.cart[index].id);
                if (product && this.cart[index].quantity < product.stock) this.cart[index].quantity++;
            },
            decrementQty(index) {
                if (this.cart[index].quantity > 1) this.cart[index].quantity--;
                else this.removeFromCart(index);
            },
            removeFromCart(index) {
                this.cart.splice(index, 1);
            },
            clearCart() {
                this.cart = [];
                this.selectedCustomer = null;
                this.isWalkIn = false;
                this.customerSearch = '';
                this.customerResults = [];
                this.amountTendered = 0;
            },

            async checkout() {
                if (!this.cart.length) return;
                if (this.paymentMethod === 'cash' && this.amountTendered < this.total) {
                    alert('Insufficient cash amount. Total is ₱' + this.total.toFixed(2));
                    return;
                }
                try {
                    const response = await fetch('/api/sales', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({
                            branch_id:       this.branchId,
                            cashier_id:      {{ auth()->user()->employee_id ?? 'null' }},
                            customer_id:     this.selectedCustomer?.id ?? null,
                            payment_method:  this.paymentMethod,
                            amount_tendered: this.paymentMethod === 'cash' ? this.amountTendered : this.total,
                            items: this.cart.map(i => ({
                                product_id: i.id,
                                quantity:   i.quantity,
                                unit_price: i.unit_price,
                            })),
                        }),
                    });
                    const data = await response.json();
                    if (!response.ok) { alert(data.message || 'Transaction failed.'); return; }
                    this.printReceipt(data);
                    this.clearCart();
                } catch (e) {
                    console.error(e);
                    alert('Network error. Please try again.');
                }
            },

            printReceipt(data) {
                const w = window.open('', '_blank', 'width=380,height=650');
                const itemsHtml = this.cart.map(i =>
                    `<tr><td>${i.name} x${i.quantity}</td><td align="right">₱${(i.unit_price * i.quantity).toFixed(2)}</td></tr>`
                ).join('');
                w.document.write(`<!DOCTYPE html><html><head><title>Receipt</title>
                <style>
                    body{font-family:monospace;padding:20px;color:#000;font-size:13px}
                    h2,p{margin:0} table{width:100%;border-collapse:collapse;margin-top:8px}
                    td{padding:3px 0} hr{border:none;border-top:1px dashed #000;margin:8px 0}
                </style></head><body>
                <center><h2>VAPOUR GARAGE MAIN</h2><p>Official Receipt</p></center><hr>
                <p>TXN: ${data.data?.transaction_number ?? '-'}</p>
                <p>Customer: ${this.selectedCustomer?.name ?? 'Walk-in'}</p>
                <p>Payment: ${this.paymentMethod.toUpperCase()}</p><hr>
                <table>${itemsHtml}</table><hr>
                <table>
                    <tr><td>Subtotal</td><td align="right">₱${this.subtotal.toFixed(2)}</td></tr>
                    ${this.loyaltyDiscount > 0 ? `<tr><td>Discount</td><td align="right">−₱${this.loyaltyDiscount.toFixed(2)}</td></tr>` : ''}
                    <tr><td>VAT (12%)</td><td align="right">₱${this.vat.toFixed(2)}</td></tr>
                    <tr><td><b>TOTAL</b></td><td align="right"><b>₱${this.total.toFixed(2)}</b></td></tr>
                    ${this.paymentMethod === 'cash' ? `<tr><td>Cash</td><td align="right">₱${this.amountTendered.toFixed(2)}</td></tr><tr><td>Change</td><td align="right">₱${this.change.toFixed(2)}</td></tr>` : ''}
                </table><hr>
                <center><p>Thank you for shopping!</p><p>${new Date().toLocaleString()}</p></center>
                <script>window.print();<\/script></body></html>`);
                w.document.close();
            },
        };
    }
    </script>
    @endpush

</x-app-layout>
