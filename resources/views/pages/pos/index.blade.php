<x-app-layout title="Point of Sale">

    <x-page-header overline="Point of Sale" title="New Transaction" :subtitle="'Branch: ' . (auth()->user()->branch->name ?? 'All Branches') . ' · ' . now()->format('F d, Y')">
        <x-slot:actions>
            <x-btn variant="ghost" size="sm" x-on:click="$dispatch('open-modal', 'held-orders')">Held Orders</x-btn>
            <x-btn variant="ghost" size="sm" x-on:click="clearCart()"
                style="color:#b83232;border-color:rgba(184,50,50,0.3)">Clear</x-btn>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-[1fr_360px] gap-5 h-[calc(100vh-168px)]" x-data="posApp(@js($products), @js($customers), @js(auth()->user()->branch_id), @js(csrf_token()))">

        {{-- LEFT: Product panel --}}
        <div class="bg-navy border border-cream3 rounded-lg p-5 flex flex-col overflow-hidden">
            <div
                class="flex items-center gap-2 bg-cream border border-cream3 rounded-md px-3.5 py-2 mb-3.5 focus-within:border-terra transition-colors">
                <svg class="w-3.5 h-3.5 text-ink4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8"
                    viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
                <input type="text" x-model="search" placeholder="Search by name or SKU…"
                    class="bg-transparent border-none outline-none text-[13px] font-sans text-ink placeholder-ink4 flex-1">
            </div>

            <div class="flex gap-1 bg-cream border border-cream3 rounded-md p-[3px] mb-3.5 overflow-x-auto">
                <button @click="activeCategory = null"
                    :class="activeCategory === null ? 'bg-black text-ink shadow-sm' : 'text-ink4'"
                    class="px-3.5 py-[5px] rounded text-[11px] font-mono tracking-wide transition-all whitespace-nowrap flex-shrink-0">All</button>
                @foreach ($categories as $cat)
                    <button @click="activeCategory = {{ $cat->id }}"
                        :class="activeCategory === {{ $cat->id }} ? 'bg-black text-ink shadow-sm' : 'text-ink4'"
                        class="px-3.5 py-[5px] rounded text-[11px] font-mono tracking-wide transition-all whitespace-nowrap flex-shrink-0">{{ $cat->name }}</button>
                @endforeach
            </div>

            <div class="grid grid-cols-3 gap-2.5 overflow-y-auto flex-1 content-start">
                <template x-for="product in filteredProducts" :key="product.id">
                    <div @click="addToCart(product)"
                        class="bg-cream border border-cream3 rounded-lg p-3.5 cursor-pointer transition-all hover:border-terra hover:bg-navy hover:shadow-[0_2px_12px_rgba(196,94,60,0.1)] relative">
                        <template x-if="product.stock <= 0">
                            <div class="absolute inset-0 bg-navy/80 rounded-lg flex items-center justify-center z-10">
                                <span class="font-mono text-[9px] text-danger uppercase tracking-widest">Out of
                                    stock</span>
                            </div>
                        </template>
                        <p class="font-mono text-[8px] tracking-[0.14em] uppercase text-ink4 mb-1.5"
                            style="color: white;" x-text="product.category_name"></p>
                        <p class="text-[11.5px] font-semibold text-ink leading-snug mb-2" x-text="product.name"></p>
                        <p class="font-mono text-[9px] text-ink4 mb-2" x-text="product.sku"></p>
                        <p class="font-serif text-[16px] font-bold text-terra"
                            x-text="'₱' + Number(product.selling_price).toLocaleString()"></p>
                        <p class="font-mono text-[9px] mt-0.5"
                            :class="product.stock <= product.reorder_level ? 'text-warn' : 'text-ok'"
                            x-text="product.stock + ' in stock'"></p>
                    </div>
                </template>
                <div x-show="filteredProducts.length === 0"
                    class="col-span-3 flex items-center justify-center py-12 text-ink4 font-mono text-[11px]">No
                    products found.</div>
            </div>
        </div>

        {{-- RIGHT: Cart + Customer panel --}}
        <div class="bg-navy border border-cream3 rounded-lg flex flex-col overflow-hidden">

            {{-- CUSTOMER SECTION --}}
            <div class="px-5 pt-5 pb-4 border-b border-cream2 flex-shrink-0">

                {{-- No customer selected: search --}}
                <div x-show="!selectedCustomer && !isWalkIn">
                    <p class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 mb-2">Customer</p>
                    <div class="relative">
                        <div
                            class="flex items-center gap-2 bg-cream border border-cream3 rounded-md px-3 py-2 focus-within:border-terra transition-colors">
                            <svg class="w-3.5 h-3.5 text-ink4 flex-shrink-0" fill="none" stroke="currentColor"
                                stroke-width="1.8" viewBox="0 0 24 24">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                            <input type="text" x-model="customerSearch" @input="searchCustomers()"
                                @keydown.escape="customerSearch='';customerResults=[]"
                                placeholder="Name, phone, or email…"
                                class="bg-transparent border-none outline-none text-[12px] font-sans text-ink placeholder-ink4 flex-1">
                            <button x-show="customerSearch.length > 0" @click="customerSearch='';customerResults=[]"
                                class="text-ink4 hover:text-ink">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <line x1="18" y1="6" x2="6" y2="18" />
                                    <line x1="6" y1="6" x2="18" y2="18" />
                                </svg>
                            </button>
                        </div>

                        {{-- Results dropdown --}}
                        <div x-show="customerResults.length > 0"
                            class="absolute top-full left-0 right-0 mt-1 bg-white border border-cream3 rounded-lg shadow-lg z-50 max-h-52 overflow-y-auto">
                            <template x-for="cust in customerResults" :key="cust.id">
                                <div @click="selectCustomer(cust)"
                                    class="flex items-center gap-3 px-3 py-2.5 hover:bg-cream cursor-pointer border-b border-cream2 last:border-0 transition-colors">
                                    <div
                                        class="w-8 h-8 rounded-full flex items-center justify-center font-serif font-bold text-[11px] text-white flex-shrink-0 bg-navy">
                                        <span x-text="cust.name.charAt(0).toUpperCase()"></span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <p class="text-[12px] font-semibold text-ink" x-text="cust.name"></p>
                                            <template x-if="cust.visit_count > 0">
                                                <span
                                                    class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[8px] font-mono bg-ok/10 text-ok">
                                                    ↩ <span x-text="cust.visit_count + 'x'"></span>
                                                </span>
                                            </template>
                                            <template x-if="cust.visit_count === 0">
                                                <span
                                                    class="inline-flex items-center px-1.5 py-0.5 rounded text-[8px] font-mono bg-navy/10 text-navy">NEW</span>
                                            </template>
                                        </div>
                                        <p class="font-mono text-[9px] text-ink4 mt-0.5"
                                            x-text="(cust.phone || cust.email || 'No contact') + ' · ' + Number(cust.loyalty_points).toLocaleString() + ' pts'">
                                        </p>
                                    </div>
                                    <span class="font-mono text-[8px] px-1.5 py-0.5 rounded uppercase flex-shrink-0"
                                        :class="{
                                            'bg-terra/10 text-terra': cust
                                                .tier ===
                                            'platinum',
                                            'bg-gold/10 text-gold': cust
                                                .tier ===
                                                'gold',
                                            'bg-navy/10 text-navy': cust
                                                .tier ===
                                            'silver',
                                            'bg-cream2 text-ink4': cust
                                                .tier === 'bronze'
                                        }"
                                        x-text="cust.tier"></span>
                                </div>
                            </template>
                        </div>

                        {{-- No results --}}
                        <div x-show="customerSearch.length >= 2 && customerResults.length === 0 && !customerSearching"
                            class="absolute top-full left-0 right-0 mt-1 bg-white border border-cream3 rounded-lg shadow-lg z-50 p-3 text-center">
                            <p class="font-mono text-[10px] text-ink4 mb-2">No customer found</p>
                            <button @click="$dispatch('open-modal', 'quick-add-customer')"
                                class="text-[11px] font-medium text-terra hover:underline">+ Register new
                                customer</button>
                        </div>
                    </div>
                    <button @click="proceedAsWalkIn()"
                        class="mt-2 text-[11px] font-mono text-ink4 hover:text-ink3 underline underline-offset-2">Continue
                        as walk-in →</button>
                </div>

                {{-- Walk-in state --}}
                <div x-show="isWalkIn && !selectedCustomer" class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-cream2 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-ink4" fill="none" stroke="currentColor"
                                stroke-width="1.8" viewBox="0 0 24 24">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                        </div>
                        <span class="font-mono text-[11px] text-ink4 uppercase tracking-wide">Walk-in customer</span>
                    </div>
                    <button @click="isWalkIn=false" class="font-mono text-[10px] text-terra hover:underline">Search
                        customer</button>
                </div>

                {{-- Customer selected --}}
                <div x-show="selectedCustomer">
                    <div class="flex items-start gap-3 p-3 rounded-lg border transition-all"
                        :class="isReturning ? 'bg-ok/5 border-ok/25' : 'bg-cream border-cream3'">
                        <div
                            class="w-10 h-10 rounded-full flex items-center justify-center font-serif font-bold text-sm text-white flex-shrink-0 bg-navy">
                            <span x-text="selectedCustomer?.name?.charAt(0)?.toUpperCase()"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5 flex-wrap mb-1">
                                <p class="text-[13px] font-semibold text-ink" x-text="selectedCustomer?.name"></p>
                                <template x-if="isReturning">
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-mono font-medium bg-ok text-white">
                                        ↩ RETURNING · <span x-text="selectedCustomer?.visit_count + 'x'"></span>
                                    </span>
                                </template>
                                <template x-if="!isReturning && selectedCustomer">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-mono bg-navy text-white">★
                                        NEW</span>
                                </template>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-[8px] uppercase tracking-wide px-1.5 py-0.5 rounded"
                                    :class="{
                                        'bg-terra/10 text-terra': selectedCustomer
                                            ?.tier ===
                                        'platinum',
                                        'bg-gold/10 text-gold': selectedCustomer
                                            ?.tier ===
                                        'gold',
                                        'bg-navy/10 text-navy': selectedCustomer
                                            ?.tier ===
                                        'silver',
                                        'bg-cream2 text-ink4': selectedCustomer
                                            ?.tier === 'bronze'
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
                        <button @click="clearCustomer()"
                            class="text-ink4 hover:text-danger transition-colors flex-shrink-0 mt-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <line x1="18" y1="6" x2="6" y2="18" />
                                <line x1="6" y1="6" x2="18" y2="18" />
                            </svg>
                        </button>
                    </div>

                    {{-- Previous purchases hint --}}
                    <template x-if="isReturning && selectedCustomer?.last_products?.length > 0">
                        <div class="mt-2 px-3 py-2 bg-ok/5 border border-ok/15 rounded-md">
                            <p class="font-mono text-[9px] text-ok uppercase tracking-wide mb-1.5">Previously bought
                            </p>
                            <div class="flex flex-wrap gap-1">
                                <template x-for="prod in selectedCustomer.last_products" :key="prod">
                                    <span class="bg-cream2 text-ink4 font-mono text-[8px] px-1.5 py-0.5 rounded"
                                        x-text="prod"></span>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- CART ITEMS --}}
            <div class="flex-1 overflow-y-auto px-5 py-3 flex flex-col gap-2 min-h-0">
                <template x-for="(item, index) in cart" :key="item.id">
                    <div class="flex items-center gap-2.5 px-3 py-2.5 bg-cream rounded-md border border-cream3">
                        <div class="flex-1 min-w-0">
                            <p class="text-[11.5px] font-medium text-ink truncate" x-text="item.name"></p>
                            <p class="font-mono text-[9px] text-ink4 mt-0.5"
                                x-text="item.sku + ' · ₱' + Number(item.unit_price).toLocaleString()"></p>
                        </div>
                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            <button @click="decrementQty(index)"
                                class="w-5 h-5 rounded bg-cream2 hover:bg-cream3 text-ink2 flex items-center justify-center text-[13px] transition-colors">−</button>
                            <span class="font-mono text-[11px] min-w-[18px] text-center"
                                x-text="item.quantity"></span>
                            <button @click="incrementQty(index)"
                                class="w-5 h-5 rounded bg-cream2 hover:bg-cream3 text-ink2 flex items-center justify-center text-[13px] transition-colors">+</button>
                        </div>
                        <p class="font-serif text-[13px] font-bold text-ink whitespace-nowrap"
                            x-text="'₱' + Number(item.unit_price * item.quantity).toLocaleString()"></p>
                        <button @click="removeFromCart(index)" class="text-ink4 hover:text-danger transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <line x1="18" y1="6" x2="6" y2="18" />
                                <line x1="6" y1="6" x2="18" y2="18" />
                            </svg>
                        </button>
                    </div>
                </template>
                <div x-show="cart.length === 0"
                    class="flex items-center justify-center flex-1 text-ink4 font-mono text-[11px] py-6">Cart is empty
                </div>
            </div>

            {{-- TOTALS + CHECKOUT --}}
            <div class="px-5 py-4 border-t border-cream2 flex-shrink-0">
                <template x-if="selectedCustomer && loyaltyDiscount > 0">
                    <div
                        class="flex items-center justify-between px-3 py-2 bg-ok/5 border border-ok/20 rounded-md mb-3">
                        <span class="font-mono text-[9px] text-ok uppercase tracking-wide"
                            x-text="(selectedCustomer?.tier ?? '') + ' discount'"></span>
                        <span class="font-mono text-[11px] text-ok font-medium"
                            x-text="'− ₱' + loyaltyDiscount.toLocaleString('en',{minimumFractionDigits:2})"></span>
                    </div>
                </template>

                <div class="flex flex-col gap-1.5 mb-3">
                    <div class="flex justify-between text-[12px]"><span class="text-ink4">Subtotal</span><span
                            class="font-mono"
                            x-text="'₱' + subtotal.toLocaleString('en',{minimumFractionDigits:2})"></span></div>
                    <template x-if="loyaltyDiscount > 0">
                        <div class="flex justify-between text-[12px]"><span class="text-ok">Loyalty
                                Discount</span><span class="font-mono text-ok"
                                x-text="'− ₱' + loyaltyDiscount.toLocaleString('en',{minimumFractionDigits:2})"></span>
                        </div>
                    </template>
                    <div class="flex justify-between text-[12px]"><span class="text-ink4">VAT (12%)</span><span
                            class="font-mono"
                            x-text="'₱' + vat.toLocaleString('en',{minimumFractionDigits:2})"></span></div>
                    <div
                        class="flex justify-between font-serif text-[17px] font-bold border-t border-cream2 pt-2 mt-1">
                        <span>Total Due</span>
                        <span class="text-terra"
                            x-text="'₱' + total.toLocaleString('en',{minimumFractionDigits:2})"></span>
                    </div>
                </div>

                <template x-if="selectedCustomer">
                    <p class="font-mono text-[9px] text-ink4 mb-3 text-right"
                        x-text="'Earns +' + pointsToEarn + ' loyalty pts this transaction'"></p>
                </template>

                <p class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 mb-2">Payment</p>
                <div class="grid grid-cols-3 gap-1.5 mb-3">
                    @foreach (['cash' => 'Cash', 'gcash' => 'GCash', 'maya' => 'Maya', 'card' => 'Card', 'bank_transfer' => 'Bank', 'mixed' => 'Mixed'] as $val => $lbl)
                        <button @click="paymentMethod = '{{ $val }}'"
                            :class="paymentMethod === '{{ $val }}' ? 'border-terra bg-terra/8 text-terra' :
                                'border-cream3 bg-cream text-ink4'"
                            class="py-2 rounded-md font-mono text-[9px] uppercase tracking-[0.08em] border transition-all hover:border-terra hover:text-terra">
                            {{ $lbl }}
                        </button>
                    @endforeach
                </div>

                <!-- CASH SECTION -->
                <div x-show="paymentMethod === 'cash'" class="mt-4">

                    <p class="font-mono text-[10px] tracking-[0.14em] uppercase text-ink4 mb-2">
                        Cash Tendered
                    </p>

                    <!-- INPUT -->
                    <input type="number" x-model.number="amountTendered" placeholder="Enter cash amount"
                        class="w-full px-4 py-3 rounded-md border border-cream3 bg-cream text-black text-[16px] font-bold mb-3">

                    <!-- QUICK CASH -->
                    <div class="grid grid-cols-3 gap-2 mb-3">

                        <button type="button" @click="amountTendered = 100"
                            class="py-3 rounded-md bg-cream border border-cream3 hover:border-terra text-black font-bold">
                            ₱1000
                        </button>

                        <button type="button" @click="amountTendered = 500"
                            class="py-3 rounded-md bg-cream border border-cream3 hover:border-terra text-black font-bold">
                            ₱5000
                        </button>

                        <button type="button" @click="amountTendered = 1000"
                            class="py-3 rounded-md bg-cream border border-cream3 hover:border-terra text-black font-bold">
                            ₱10000
                        </button>

                    </div>

                    <!-- CHANGE -->
                    <div
                        class="flex justify-between items-center bg-black/30 border border-cream3 rounded-md px-4 py-3">

                        <span class="font-mono text-[12px] uppercase tracking-wide text-ink4">
                            Change
                        </span>

                        <span class="font-serif text-[24px] font-bold text-[#39ff14]"
                            x-text="'₱' + change.toLocaleString('en', {
                minimumFractionDigits: 2
            })"></span>

                    </div>

                </div>

                <button @click="checkout()" :disabled="cart.length === 0"
                    class="w-full py-3.5 bg-navy text-white font-serif text-[15px] font-bold rounded-md
                               hover:bg-navy-dark hover:-translate-y-px hover:shadow-lg transition-all
                               disabled:opacity-40 disabled:cursor-not-allowed disabled:transform-none"
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
                <x-form-input label="Last Name" name="last_name" required />
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <x-form-input label="Phone" name="phone" placeholder="09XXXXXXXXX" />
                <x-form-input label="Email" name="email" type="email" />
            </div>
            <x-form-input label="Date of Birth" name="birth_date" type="date" help="Must be 18+"
                class="mb-0" />
            <x-slot:footer>
                <x-btn type="button" variant="ghost" size="sm"
                    x-on:click="$dispatch('close-modal', 'quick-add-customer')">Cancel</x-btn>
                <x-btn type="submit" variant="primary" size="sm">Register & Select</x-btn>
            </x-slot:footer>
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

                            const categoryMatch =
                                this.activeCategory === null ||
                                p.category_id === this.activeCategory;

                            return searchMatch && categoryMatch && p.is_active;
                        });
                    },

                    get isReturning() {

                        return this.selectedCustomer &&
                            (this.selectedCustomer.visit_count ?? 0) > 0;
                    },

                    get loyaltyDiscount() {

                        if (!this.selectedCustomer) {
                            return 0;
                        }

                        const pct = {
                            platinum: 0.15,
                            gold: 0.10,
                            silver: 0.05,
                            bronze: 0,
                        } [this.selectedCustomer.tier] ?? 0;

                        return Math.round(this.subtotal * pct * 100) / 100;
                    },

                    get subtotal() {

                        return this.cart.reduce((sum, item) => {

                            return sum + (item.unit_price * item.quantity);

                        }, 0);
                    },

                    get vat() {

                        return Math.round(
                            (this.subtotal - this.loyaltyDiscount) *
                            0.12 *
                            100
                        ) / 100;
                    },

                    get total() {

                        return Math.round(
                            (
                                this.subtotal -
                                this.loyaltyDiscount +
                                this.vat
                            ) * 100
                        ) / 100;
                    },

                    get pointsToEarn() {

                        return Math.floor(this.total / 10);
                    },

                    get change() {

                        if (this.paymentMethod !== 'cash') {
                            return 0;
                        }

                        return Math.max(
                            this.amountTendered - this.total,
                            0
                        );
                    },

                    searchCustomers() {

                        const q = this.customerSearch
                            .trim()
                            .toLowerCase();

                        if (q.length < 2) {

                            this.customerResults = [];

                            return;
                        }

                        this.customerResults = this.customers
                            .filter(c =>

                                (c.name &&
                                    c.name.toLowerCase().includes(q))

                                ||

                                (c.phone &&
                                    c.phone.includes(q))

                                ||

                                (c.email &&
                                    c.email.toLowerCase().includes(q))

                            )
                            .slice(0, 8);
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

                        if (product.stock <= 0) {
                            return;
                        }

                        const existing = this.cart.find(i => i.id === product.id);

                        if (existing) {

                            existing.quantity++;

                            return;
                        }

                        this.cart.push({

                            id: product.id,

                            name: product.name,

                            sku: product.sku,

                            unit_price: parseFloat(product.selling_price),

                            quantity: 1
                        });
                    },

                    incrementQty(index) {

                        const product = this.products.find(

                            p => p.id === this.cart[index].id
                        );

                        if (!product) {
                            return;
                        }

                        if (this.cart[index].quantity < product.stock) {

                            this.cart[index].quantity++;
                        }
                    },

                    decrementQty(index) {

                        if (this.cart[index].quantity > 1) {

                            this.cart[index].quantity--;

                        } else {

                            this.removeFromCart(index);
                        }
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

                        if (!this.cart.length) {
                            return;
                        }

                        if (
                            this.paymentMethod === 'cash' &&
                            this.amountTendered < this.total
                        ) {

                            alert('Insufficient cash amount.');

                            return;
                        }

                        try {

                            const response = await fetch('/api/sales', {

                                method: 'POST',

                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                },

                                body: JSON.stringify({

                                    branch_id: this.branchId,

                                    cashier_id: {{ auth()->user()->employee_id ?? 'null' }},

                                    customer_id: this.selectedCustomer?.id ?? null,

                                    payment_method: this.paymentMethod,

                                    amount_tendered: this.paymentMethod === 'cash' ?
                                        this.amountTendered :
                                        this.total,

                                    items: this.cart.map(i => ({

                                        product_id: i.id,

                                        quantity: i.quantity,

                                        unit_price: i.unit_price
                                    }))
                                })
                            });

                            const data = await response.json();

                            if (!response.ok) {

                                alert(
                                    data.message ||
                                    'Transaction failed.'
                                );

                                return;
                            }

                            this.printReceipt(data);

                            this.clearCart();

                        } catch (error) {

                            console.error(error);

                            alert(
                                'Network/server error.'
                            );
                        }
                    },

                    printReceipt(data) {

                        const receiptWindow = window.open(
                            '',
                            '_blank',
                            'width=400,height=700'
                        );

                        const itemsHtml = this.cart.map(item => `
                <tr>
                    <td>
                        ${item.name} x${item.quantity}
                    </td>

                    <td align="right">
                        ₱${(
                            item.unit_price *
                            item.quantity
                        ).toFixed(2)}
                    </td>
                </tr>
            `).join('');

                        receiptWindow.document.write(`

                <html>

                <head>

                    <title>Receipt</title>

                    <style>

                        body{
                            font-family:monospace;
                            padding:20px;
                            color:#000;
                        }

                        h2,p{
                            margin:0;
                        }

                        table{
                            width:100%;
                            border-collapse:collapse;
                            margin-top:10px;
                        }

                        td{
                            padding:4px 0;
                            font-size:13px;
                        }

                        hr{
                            border:none;
                            border-top:1px dashed #000;
                            margin:10px 0;
                        }

                    </style>

                </head>

                <body>

                    <center>

                        <h2>VAPOUR GARAGE</h2>

                        <p>Official Receipt</p>

                    </center>

                    <hr>

                    <p>
                        TXN:
                        ${data.data?.transaction_number ?? '-'}
                    </p>

                    <p>
                        Customer:
                        ${this.selectedCustomer?.name ?? 'Walk-in'}
                    </p>

                    <p>
                        Payment:
                        ${this.paymentMethod.toUpperCase()}
                    </p>

                    <hr>

                    <table>

                        ${itemsHtml}

                    </table>

                    <hr>

                    <table>

                        <tr>
                            <td>Subtotal</td>

                            <td align="right">
                                ₱${this.subtotal.toFixed(2)}
                            </td>
                        </tr>

                        <tr>
                            <td>VAT</td>

                            <td align="right">
                                ₱${this.vat.toFixed(2)}
                            </td>
                        </tr>

                        <tr>
                            <td><b>Total</b></td>

                            <td align="right">
                                <b>
                                    ₱${this.total.toFixed(2)}
                                </b>
                            </td>
                        </tr>

                        <tr>
                            <td>Cash</td>

                            <td align="right">
                                ₱${this.amountTendered.toFixed(2)}
                            </td>
                        </tr>

                        <tr>
                            <td>Change</td>

                            <td align="right">
                                ₱${this.change.toFixed(2)}
                            </td>
                        </tr>

                    </table>

                    <hr>

                    <center>

                        <p>
                            Thank you for shopping!
                        </p>

                    </center>

                    <script>

                        window.print();

                    <\/script>

                </body>

                </html>

            `);

                        receiptWindow.document.close();
                    }
                }
            }
        </script>
        {{-- <script>
            function posApp(products, customers, branchId, csrfToken) {
                return {
                    products,
                    customers,
                    branchId,
                    cart: [],
                    search: '',
                    activeCategory: null,
                    paymentMethod: 'cash',
                    customerSearch: '',
                    customerResults: [],
                    customerSearching: false,
                    selectedCustomer: null,
                    isWalkIn: false,

                    get filteredProducts() {
                        return this.products.filter(p => {
                            const s = !this.search || p.name.toLowerCase().includes(this.search.toLowerCase()) || p
                                .sku.toLowerCase().includes(this.search.toLowerCase());
                            const c = this.activeCategory === null || p.category_id === this.activeCategory;
                            return s && c && p.is_active;
                        });
                    },

                    get isReturning() {
                        return this.selectedCustomer && (this.selectedCustomer.visit_count ?? 0) > 0;
                    },

                    get loyaltyDiscount() {
                        if (!this.selectedCustomer) return 0;
                        const pct = {
                            platinum: 0.15,
                            gold: 0.10,
                            silver: 0.05,
                            bronze: 0
                        } [this.selectedCustomer.tier] ?? 0;
                        return Math.round(this.subtotal * pct * 100) / 100;
                    },

                    get subtotal() {
                        return this.cart.reduce((s, i) => s + i.unit_price * i.quantity, 0);
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

                    searchCustomers() {
                        const q = this.customerSearch.trim().toLowerCase();
                        if (q.length < 2) {
                            this.customerResults = [];
                            return;
                        }
                        this.customerResults = this.customers.filter(c =>
                            (c.name && c.name.toLowerCase().includes(q)) ||
                            (c.phone && c.phone.includes(q)) ||
                            (c.email && c.email.toLowerCase().includes(q))
                        ).slice(0, 8);
                    },

                    selectCustomer(c) {
                        this.selectedCustomer = c;
                        this.isWalkIn = false;
                        this.customerSearch = '';
                        this.customerResults = [];
                    },
                    clearCustomer() {
                        this.selectedCustomer = null;
                        this.isWalkIn = false;
                        this.customerSearch = '';
                    },
                    proceedAsWalkIn() {
                        this.selectedCustomer = null;
                        this.isWalkIn = true;
                        this.customerSearch = '';
                        this.customerResults = [];
                    },

                    addToCart(product) {
                        if (product.stock <= 0) return;
                        const ex = this.cart.find(i => i.id === product.id);
                        if (ex) {
                            ex.quantity++;
                            return;
                        }
                        this.cart.push({
                            id: product.id,
                            name: product.name,
                            sku: product.sku,
                            unit_price: parseFloat(product.selling_price),
                            quantity: 1
                        });
                    },
                    incrementQty(i) {
                        const p = this.products.find(p => p.id === this.cart[i].id);
                        if (!p || this.cart[i].quantity < p.stock) this.cart[i].quantity++;
                    },
                    decrementQty(i) {
                        if (this.cart[i].quantity > 1) this.cart[i].quantity--;
                        else this.removeFromCart(i);
                    },
                    removeFromCart(i) {
                        this.cart.splice(i, 1);
                    },
                    clearCart() {
                        this.cart = [];
                        this.selectedCustomer = null;
                        this.isWalkIn = false;
                        this.customerSearch = '';
                    },

                    async checkout() {
                        if (!this.cart.length) return;
                        try {
                            const res = await fetch('/api/sales', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                },
                                body: JSON.stringify({
                                    branch_id: this.branchId,
                                    cashier_id: {{ auth()->user()->employee_id ?? 'null' }},
                                    customer_id: this.selectedCustomer?.id ?? null,
                                    payment_method: this.paymentMethod,
                                    amount_tendered: this.total,
                                    items: this.cart.map(i => ({
                                        product_id: i.id,
                                        quantity: i.quantity,
                                        unit_price: i.unit_price
                                    })),
                                }),
                            });
                            const data = await res.json();
                            if (res.ok) {
                                const pts = this.pointsToEarn;
                                alert('✓ Transaction complete!\n\nTXN: ' + data.data?.transaction_number + '\nCustomer: ' +
                                    (this.selectedCustomer?.name ?? 'Walk-in') + (pts > 0 ? '\nPoints earned: +' + pts :
                                        '') + '\nTotal: ₱' + this.total.toLocaleString('en', {
                                        minimumFractionDigits: 2
                                    }));
                                this.clearCart();
                            } else {
                                alert('Error: ' + (data.message || 'Transaction failed.'));
                            }
                        } catch {
                            alert('Network error. Please try again.');
                        }
                    },
                }
            }
        </script> --}}
    @endpush
</x-app-layout>
