@php $title = 'Shop All Products'; @endphp
@include('shop._header')

<div style="padding:40px 40px 0">

    {{-- Flash --}}
    @if(session('success'))
        <div class="flash flash-ok">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="flash flash-err">{{ session('error') }}</div>
    @endif

    {{-- Page header --}}
    <div style="display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:32px;padding-bottom:24px;border-bottom:1px solid rgba(124,34,212,0.2)">
        <div>
            <div class="sec-tag">Online Shop</div>
            <h1 class="sec-title" style="font-size:clamp(32px,4vw,56px)">
                @if(request()->boolean('is_on_sale'))
                    ON<br><span style="color:#c9943a">SALE</span>
                @elseif(request()->boolean('is_wholesale'))
                    WHOLESALE<br><span style="color:var(--purple2)">PRODUCTS</span>
                @else
                    ALL<br><span style="color:var(--purple2)">PRODUCTS</span>
                @endif
            </h1>
            <p style="font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--muted);margin-top:8px;letter-spacing:0.06em">
                {{ $products->total() }} products {{ request()->boolean('is_on_sale') ? 'on sale' : 'available' }}
            </p>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('shop.index') }}" style="display:flex;align-items:flex-end;gap:10px;flex-wrap:wrap">
            <div>
                <label class="vlabel">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or SKU…"
                       class="vinput" style="width:180px">
            </div>
            <div>
                <label class="vlabel">Category</label>
                <select name="category" class="vinput vselect" style="width:160px">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="vlabel">Sort</label>
                <select name="sort" class="vinput vselect" style="width:160px">
                    <option value="">Default</option>
                    <option value="newest"     {{ request('sort') === 'newest'     ? 'selected' : '' }}>Newest First</option>
                    <option value="price_asc"  {{ request('sort') === 'price_asc'  ? 'selected' : '' }}>Price: Low → High</option>
                    <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Price: High → Low</option>
                </select>
            </div>
            <button type="submit" class="vbtn vbtn-primary" style="align-self:flex-end">Apply</button>
            @if(request()->hasAny(['search','category','sort','is_on_sale','is_wholesale']))
                <a href="{{ route('shop.index') }}" class="vbtn vbtn-ghost" style="align-self:flex-end">Clear</a>
            @endif
        </form>
    </div>

    {{-- Quick-filter tabs: All · Sale · Wholesale · Categories --}}
    <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:28px">
        {{-- All --}}
        <a href="{{ route('shop.index') }}"
           style="padding:6px 14px;border-radius:3px;font-family:'Share Tech Mono',monospace;font-size:10px;letter-spacing:0.12em;text-transform:uppercase;text-decoration:none;transition:all 0.2s;
                  border:1px solid {{ (!request('category') && !request()->boolean('is_on_sale') && !request()->boolean('is_wholesale')) ? 'rgba(124,34,212,0.7)' : 'rgba(124,34,212,0.25)' }};
                  background:{{ (!request('category') && !request()->boolean('is_on_sale') && !request()->boolean('is_wholesale')) ? 'rgba(124,34,212,0.2)' : 'transparent' }};
                  color:{{ (!request('category') && !request()->boolean('is_on_sale') && !request()->boolean('is_wholesale')) ? '#9b3df0' : '#7a7490' }}">
            All
        </a>

        {{-- Sale --}}
        <a href="{{ route('shop.index', array_merge(request()->only(['search','category','sort']), ['is_on_sale' => 1])) }}"
           style="padding:6px 14px;border-radius:3px;font-family:'Share Tech Mono',monospace;font-size:10px;letter-spacing:0.12em;text-transform:uppercase;text-decoration:none;transition:all 0.2s;
                  border:1px solid {{ request()->boolean('is_on_sale') ? 'rgba(201,148,58,0.7)' : 'rgba(201,148,58,0.3)' }};
                  background:{{ request()->boolean('is_on_sale') ? 'rgba(201,148,58,0.15)' : 'transparent' }};
                  color:{{ request()->boolean('is_on_sale') ? '#c9943a' : '#7a7490' }}">
            🏷 Sale
        </a>

        {{-- Wholesale --}}
        <a href="{{ route('shop.index', array_merge(request()->only(['search','category','sort']), ['is_wholesale' => 1])) }}"
           style="padding:6px 14px;border-radius:3px;font-family:'Share Tech Mono',monospace;font-size:10px;letter-spacing:0.12em;text-transform:uppercase;text-decoration:none;transition:all 0.2s;
                  border:1px solid {{ request()->boolean('is_wholesale') ? 'rgba(124,34,212,0.7)' : 'rgba(124,34,212,0.25)' }};
                  background:{{ request()->boolean('is_wholesale') ? 'rgba(124,34,212,0.2)' : 'transparent' }};
                  color:{{ request()->boolean('is_wholesale') ? '#9b3df0' : '#7a7490' }}">
            📦 Wholesale
        </a>

        {{-- Divider --}}
        <span style="width:1px;background:rgba(124,34,212,0.2);margin:0 4px;align-self:stretch"></span>

        {{-- Category tabs --}}
        @foreach($categories as $cat)
        <a href="{{ route('shop.index', ['category' => $cat->id]) }}"
           style="padding:6px 14px;border-radius:3px;font-family:'Share Tech Mono',monospace;font-size:10px;letter-spacing:0.12em;text-transform:uppercase;text-decoration:none;transition:all 0.2s;
                  border:1px solid {{ request('category') == $cat->id ? 'rgba(124,34,212,0.7)' : 'rgba(124,34,212,0.25)' }};
                  background:{{ request('category') == $cat->id ? 'rgba(124,34,212,0.2)' : 'transparent' }};
                  color:{{ request('category') == $cat->id ? '#9b3df0' : '#7a7490' }}">
            {{ $cat->name }}
        </a>
        @endforeach
    </div>

    {{-- Product grid --}}
    @if($products->isEmpty())
        <div style="text-align:center;padding:80px 24px;border:1px solid rgba(124,34,212,0.2);border-radius:6px;background:#130f1e">
            <p style="font-family:'Russo One',sans-serif;font-size:16px;color:#e8e4f0;text-transform:uppercase;margin-bottom:8px">No Products Found</p>
            <p style="font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--muted)">Try a different search or category filter.</p>
        </div>
    @else
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:2px;background:rgba(124,34,212,0.1);margin-bottom:40px">
        @foreach($products as $product)
        @php
            $stock    = $product->inventory->sum('quantity_on_hand') - $product->inventory->sum('quantity_reserved');
            $inStock  = $stock > 0;
            $onSale   = $product->is_on_sale && $product->sale_price;
            $saleDisc = $onSale && $product->selling_price > 0
                ? (int) round((1 - $product->sale_price / $product->selling_price) * 100)
                : 0;
        @endphp
        <div style="background:#0d0b14;padding:0;position:relative;overflow:hidden;transition:all 0.25s;border:1px solid {{ $onSale ? 'rgba(201,148,58,0.3)' : 'transparent' }}"
             onmouseover="this.style.background='#130f1e';this.style.borderColor='{{ $onSale ? 'rgba(201,148,58,0.6)' : 'rgba(124,34,212,0.4)' }}'"
             onmouseout="this.style.background='#0d0b14';this.style.borderColor='{{ $onSale ? 'rgba(201,148,58,0.3)' : 'transparent' }}'">

            {{-- Sale top stripe --}}
            @if($onSale)
            <div style="height:2px;background:linear-gradient(90deg,#c9943a,#e0b44a,#c9943a)"></div>
            @endif

            {{-- Product image --}}
            <div style="height:180px;background:linear-gradient(135deg,#130f1e 0%,#1a1428 100%);display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;border-bottom:1px solid rgba(124,34,212,0.15)">
                @if($product->image_url)
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                         style="width:100%;height:100%;object-fit:cover;display:block;position:absolute;inset:0">
                    <div style="position:absolute;inset:0;background:linear-gradient(to bottom,transparent 50%,rgba(0,0,0,0.5) 100%)"></div>
                @else
                    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:120px;height:120px;border-radius:50%;background:radial-gradient(circle,{{ $onSale ? 'rgba(201,148,58,0.18)' : 'rgba(124,34,212,0.2)' }} 0%,transparent 70%)"></div>
                    <svg style="width:56px;height:56px;opacity:0.15;color:{{ $onSale ? '#c9943a' : '#9b3df0' }};position:relative;z-index:1" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                    </svg>
                @endif

                {{-- Sale badge --}}
                @if($onSale && $saleDisc > 0)
                <div style="position:absolute;top:10px;left:10px;padding:4px 9px;background:#c9943a;border-radius:3px;font-family:'Russo One',sans-serif;font-size:11px;letter-spacing:0.06em;color:#000;font-weight:900;z-index:2;box-shadow:0 0 12px rgba(201,148,58,0.5)">
                    −{{ $saleDisc }}%
                </div>
                @elseif($onSale)
                <div style="position:absolute;top:10px;left:10px;padding:4px 9px;background:#c9943a;border-radius:3px;font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.1em;color:#000;z-index:2">
                    SALE
                </div>
                @endif

                {{-- Age verification badge --}}
                @if($product->requires_age_verification)
                <div style="position:absolute;top:10px;right:10px;padding:3px 8px;background:rgba(201,148,58,0.2);border:1px solid rgba(201,148,58,0.4);border-radius:3px;font-family:'Share Tech Mono',monospace;font-size:8px;letter-spacing:0.1em;color:#c9943a">
                    18+ ONLY
                </div>
                @endif

                {{-- Wholesale badge --}}
                @if($product->is_wholesale)
                <div style="position:absolute;bottom:28px;left:10px;padding:2px 7px;background:rgba(124,34,212,0.25);border:1px solid rgba(124,34,212,0.4);border-radius:2px;font-family:'Share Tech Mono',monospace;font-size:8px;letter-spacing:0.08em;color:#9b3df0">
                    WHOLESALE
                </div>
                @endif

                {{-- Out of stock overlay --}}
                @if(!$inStock)
                <div style="position:absolute;inset:0;background:rgba(6,6,8,0.65);display:flex;align-items:center;justify-content:center;z-index:3">
                    <span style="font-family:'Russo One',sans-serif;font-size:13px;letter-spacing:0.12em;color:#e05050;text-transform:uppercase">Out of Stock</span>
                </div>
                @endif

                {{-- SKU --}}
                <div style="position:absolute;bottom:8px;left:10px;font-family:'Share Tech Mono',monospace;font-size:9px;color:rgba(124,34,212,0.5);letter-spacing:0.1em">
                    {{ $product->sku }}
                </div>
            </div>

            {{-- Info --}}
            <div style="padding:16px;display:flex;flex-direction:column;gap:8px">
                <div style="font-family:'Share Tech Mono',monospace;font-size:8.5px;letter-spacing:0.18em;text-transform:uppercase;color:{{ $onSale ? '#c9943a' : 'rgba(124,34,212,0.6)' }}">
                    {{ $product->category->name ?? 'Uncategorised' }}
                </div>
                <h3 style="font-family:'Russo One',sans-serif;font-size:15px;letter-spacing:0.03em;color:#e8e4f0;line-height:1.1;text-transform:uppercase">
                    {{ $product->name }}
                </h3>
                @if($product->description)
                <p style="font-size:12px;color:var(--muted);line-height:1.5;font-weight:300">
                    {{ Str::limit($product->description, 70) }}
                </p>
                @endif
                <div style="display:flex;align-items:flex-end;justify-content:space-between;margin-top:4px;gap:8px">
                    <div>
                        @if($onSale)
                            <div style="font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--muted);text-decoration:line-through;margin-bottom:2px">
                                ₱{{ number_format($product->selling_price, 0) }}
                            </div>
                            <span style="font-family:'Russo One',sans-serif;font-size:22px;color:#c9943a;letter-spacing:0.02em">
                                ₱{{ number_format($product->sale_price, 0) }}
                            </span>
                        @else
                            <span style="font-family:'Russo One',sans-serif;font-size:20px;color:var(--green);letter-spacing:0.02em">
                                ₱{{ number_format($product->selling_price, 0) }}
                            </span>
                        @endif
                        @if($product->is_wholesale && $product->min_order_qty)
                            <div style="font-family:'Share Tech Mono',monospace;font-size:9px;color:#9b3df0;margin-top:2px;letter-spacing:0.06em">
                                Min. {{ $product->min_order_qty }} pcs
                            </div>
                        @endif
                    </div>
                    <span style="font-family:'Share Tech Mono',monospace;font-size:9px;color:{{ $inStock ? 'var(--ok)' : 'var(--danger)' }};white-space:nowrap;text-align:right">
                        {{ $inStock ? "✓ IN STOCK ({$stock})" : '✗ OUT OF STOCK' }}
                    </span>
                </div>

                {{-- Actions --}}
                <div style="display:flex;gap:6px;margin-top:4px">
                    <a href="{{ route('shop.product', $product->id) }}"
                       style="flex:1;padding:8px;background:transparent;border:1px solid rgba(124,34,212,0.3);border-radius:3px;text-align:center;font-family:'Share Tech Mono',monospace;font-size:10px;letter-spacing:0.1em;text-transform:uppercase;color:var(--muted);text-decoration:none;transition:all 0.2s"
                       onmouseover="this.style.borderColor='rgba(124,34,212,0.6)';this.style.color='#e8e4f0'"
                       onmouseout="this.style.borderColor='rgba(124,34,212,0.3)';this.style.color='var(--muted)'">
                        View
                    </a>
                    @if($inStock)
                    <form method="POST" action="{{ route('shop.cart.add', $product->id) }}" style="flex:2">
                        @csrf
                        <button type="submit"
                                style="width:100%;padding:8px;background:var(--purple);color:#fff;border:none;border-radius:3px;font-family:'Russo One',sans-serif;font-size:11px;letter-spacing:0.06em;text-transform:uppercase;cursor:pointer;transition:all 0.2s;clip-path:polygon(0 0,calc(100% - 4px) 0,100% 4px,100% 100%,4px 100%,0 calc(100% - 4px))"
                                onmouseover="this.style.background='#9b3df0';this.style.boxShadow='0 0 16px rgba(124,34,212,0.5)'"
                                onmouseout="this.style.background='var(--purple)';this.style.boxShadow='none'">
                            + ADD TO CART
                        </button>
                    </form>
                    @else
                    <button disabled style="flex:2;padding:8px;background:rgba(255,255,255,0.04);color:var(--muted);border:1px solid rgba(255,255,255,0.1);border-radius:3px;font-family:'Share Tech Mono',monospace;font-size:10px;letter-spacing:0.1em;cursor:not-allowed;text-transform:uppercase">
                        OUT OF STOCK
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div style="margin-bottom:40px;display:flex;align-items:center;justify-content:space-between">
        <div style="font-family:'Share Tech Mono',monospace;font-size:10px;color:var(--muted);letter-spacing:0.06em">
            // PAGE {{ $products->currentPage() }} OF {{ $products->lastPage() }} · {{ $products->total() }} ITEMS
        </div>
        <div style="display:flex;gap:8px">
            @if($products->onFirstPage())
                <span style="padding:6px 14px;font-family:'Share Tech Mono',monospace;font-size:11px;border:1px solid rgba(124,34,212,0.15);border-radius:3px;color:rgba(255,255,255,0.2);cursor:not-allowed">← PREV</span>
            @else
                <a href="{{ $products->previousPageUrl() }}" style="padding:6px 14px;font-family:'Share Tech Mono',monospace;font-size:11px;border:1px solid rgba(124,34,212,0.3);border-radius:3px;color:var(--muted);text-decoration:none;transition:all 0.2s"
                   onmouseover="this.style.background='rgba(124,34,212,0.15)';this.style.color='#e8e4f0'" onmouseout="this.style.background='';this.style.color='var(--muted)'">← PREV</a>
            @endif
            @if($products->hasMorePages())
                <a href="{{ $products->nextPageUrl() }}" style="padding:6px 14px;font-family:'Share Tech Mono',monospace;font-size:11px;border:1px solid rgba(124,34,212,0.3);border-radius:3px;color:var(--muted);text-decoration:none;transition:all 0.2s"
                   onmouseover="this.style.background='rgba(124,34,212,0.15)';this.style.color='#e8e4f0'" onmouseout="this.style.background='';this.style.color='var(--muted)'">NEXT →</a>
            @else
                <span style="padding:6px 14px;font-family:'Share Tech Mono',monospace;font-size:11px;border:1px solid rgba(124,34,212,0.15);border-radius:3px;color:rgba(255,255,255,0.2);cursor:not-allowed">NEXT →</span>
            @endif
        </div>
    </div>
    @endif
</div>

<footer class="shop-footer">
    <span class="sfooter-brand">VAPOUR GARAGE MAIN</span>
    <span class="sfooter-legal">© {{ date('Y') }} Vapour Garage Main · For 18+ only · RA 11467 Compliant</span>
    <span class="sfooter-age">// 18+ ONLY</span>
</footer>
</div>
</body>
</html>
