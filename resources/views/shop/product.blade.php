@php
    $title = $product->name;
    $stock = $product->inventory->sum('quantity_on_hand') - $product->inventory->sum('quantity_reserved');
    $inStock = $stock > 0;
    $onSale  = $product->is_on_sale && $product->sale_price;
    $disc    = $onSale && $product->selling_price > 0
        ? (int) round((1 - $product->sale_price / $product->selling_price) * 100)
        : 0;
    $displayPrice = $onSale ? $product->sale_price : $product->selling_price;
@endphp
@include('shop._header')

<div style="padding:40px 40px 0;max-width:1100px;margin:0 auto">

    {{-- Breadcrumb --}}
    <div style="font-family:'Share Tech Mono',monospace;font-size:10px;color:var(--muted);letter-spacing:0.08em;margin-bottom:32px">
        <a href="{{ route('shop.index') }}" style="color:var(--muted);text-decoration:none;transition:color 0.2s"
           onmouseover="this.style.color='var(--green)'" onmouseout="this.style.color='var(--muted)'">Shop</a>
        <span style="margin:0 8px;opacity:0.4">//</span>
        @if($product->category)
        <a href="{{ route('shop.index', ['category' => $product->category->id]) }}"
           style="color:var(--muted);text-decoration:none;transition:color 0.2s"
           onmouseover="this.style.color='var(--green)'" onmouseout="this.style.color='var(--muted)'">
            {{ $product->category->name }}
        </a>
        <span style="margin:0 8px;opacity:0.4">//</span>
        @endif
        <span style="color:var(--grey)">{{ $product->name }}</span>
    </div>

    <div style="display:grid;grid-template-columns:1fr 420px;gap:48px;margin-bottom:80px">

        {{-- LEFT: Product image & badges --}}
        <div>
            {{-- Main image area --}}
            <div style="background:linear-gradient(135deg,#130f1e 0%,#1a1428 60%,#0d0b14 100%);
                        border:1px solid {{ $onSale ? 'rgba(201,148,58,0.4)' : 'rgba(124,34,212,0.3)' }};
                        border-radius:6px;height:420px;display:flex;align-items:center;justify-content:center;
                        position:relative;overflow:hidden;margin-bottom:16px">

                {{-- Top accent stripe --}}
                @if($onSale)
                <div style="position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#c9943a,#e0b44a,#c9943a)"></div>
                @else
                <div style="position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#5b16a0,#39ff14,#5b16a0)"></div>
                @endif

                @if($product->image_url)
                    {{-- Real product image --}}
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                         style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:0">
                    <div style="position:absolute;inset:0;background:linear-gradient(to bottom,rgba(0,0,0,0.15) 0%,transparent 40%,rgba(0,0,0,0.4) 100%);z-index:1"></div>
                @else
                    {{-- Glow placeholder --}}
                    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);
                                width:260px;height:260px;border-radius:50%;
                                background:radial-gradient(circle,{{ $onSale ? 'rgba(201,148,58,0.18)' : 'rgba(124,34,212,0.2)' }} 0%,transparent 70%)">
                    </div>
                    <svg style="width:100px;height:100px;opacity:0.12;color:{{ $onSale ? '#c9943a' : '#9b3df0' }};position:relative;z-index:1"
                         fill="none" stroke="currentColor" stroke-width="0.8" viewBox="0 0 24 24">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>
                    </svg>
                @endif

                {{-- Corner brackets --}}
                <div style="position:absolute;top:16px;left:16px;width:28px;height:28px;border-top:1.5px solid {{ $onSale ? 'rgba(201,148,58,0.4)' : 'rgba(57,255,20,0.3)' }};border-left:1.5px solid {{ $onSale ? 'rgba(201,148,58,0.4)' : 'rgba(57,255,20,0.3)' }}"></div>
                <div style="position:absolute;bottom:16px;right:16px;width:28px;height:28px;border-bottom:1.5px solid rgba(124,34,212,0.4);border-right:1.5px solid rgba(124,34,212,0.4)"></div>

                {{-- Sale badge --}}
                @if($onSale && $disc > 0)
                <div style="position:absolute;top:20px;right:20px;padding:6px 12px;background:#c9943a;border-radius:3px;
                            font-family:'Russo One',sans-serif;font-size:14px;color:#000;letter-spacing:0.04em;
                            box-shadow:0 0 20px rgba(201,148,58,0.5);z-index:2">
                    −{{ $disc }}%
                </div>
                @endif

                {{-- Out of stock overlay --}}
                @if(!$inStock)
                <div style="position:absolute;inset:0;background:rgba(6,6,8,0.75);display:flex;align-items:center;justify-content:center;z-index:3">
                    <div style="text-align:center">
                        <p style="font-family:'Russo One',sans-serif;font-size:18px;color:#e05050;letter-spacing:0.12em;text-transform:uppercase">Out of Stock</p>
                        <p style="font-family:'Share Tech Mono',monospace;font-size:10px;color:var(--muted);margin-top:6px;letter-spacing:0.08em">Check back soon or visit a branch</p>
                    </div>
                </div>
                @endif

                {{-- SKU watermark --}}
                <div style="position:absolute;bottom:14px;left:18px;font-family:'Share Tech Mono',monospace;font-size:10px;color:rgba(124,34,212,0.4);letter-spacing:0.12em">
                    {{ $product->sku }}
                </div>
            </div>

            {{-- Product flags --}}
            <div style="display:flex;flex-wrap:wrap;gap:6px">
                @if($product->is_new_arrival)
                    <span style="padding:4px 10px;border-radius:3px;font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.12em;text-transform:uppercase;background:rgba(57,255,20,0.1);color:#39ff14;border:1px solid rgba(57,255,20,0.3)">New Arrival</span>
                @endif
                @if($product->is_best_seller)
                    <span style="padding:4px 10px;border-radius:3px;font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.12em;text-transform:uppercase;background:rgba(57,255,20,0.08);color:#39ff14;border:1px solid rgba(57,255,20,0.2)">Best Seller</span>
                @endif
                @if($product->is_hot)
                    <span style="padding:4px 10px;border-radius:3px;font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.12em;text-transform:uppercase;background:rgba(255,90,50,0.12);color:#ff5a32;border:1px solid rgba(255,90,50,0.3)">🔥 Trending</span>
                @endif
                @if($product->is_wholesale)
                    <span style="padding:4px 10px;border-radius:3px;font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.12em;text-transform:uppercase;background:rgba(124,34,212,0.15);color:#9b3df0;border:1px solid rgba(124,34,212,0.35)">Wholesale</span>
                @endif
                @if($product->requires_age_verification)
                    <span style="padding:4px 10px;border-radius:3px;font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.12em;text-transform:uppercase;background:rgba(201,148,58,0.12);color:#c9943a;border:1px solid rgba(201,148,58,0.3)">18+ Only</span>
                @endif
            </div>
        </div>

        {{-- RIGHT: Product info & actions --}}
        <div style="display:flex;flex-direction:column;gap:20px">

            {{-- Category --}}
            <div style="font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.22em;text-transform:uppercase;
                        color:{{ $onSale ? '#c9943a' : 'rgba(124,34,212,0.7)' }};display:flex;align-items:center;gap:8px">
                <span style="opacity:0.5">//</span>
                {{ $product->category->name ?? 'Product' }}
            </div>

            {{-- Name --}}
            <h1 style="font-family:'Russo One',sans-serif;font-size:clamp(26px,3vw,38px);letter-spacing:0.04em;
                       color:#e8e4f0;line-height:1;text-transform:uppercase">
                {{ $product->name }}
            </h1>

            {{-- Description --}}
            @if($product->description)
            <p style="font-size:13.5px;color:var(--muted);line-height:1.7;font-weight:300;border-left:2px solid rgba(124,34,212,0.3);padding-left:14px">
                {{ $product->description }}
            </p>
            @endif

            {{-- Price block --}}
            <div style="padding:20px;background:#130f1e;border:1px solid {{ $onSale ? 'rgba(201,148,58,0.3)' : 'rgba(124,34,212,0.2)' }};border-radius:4px">
                @if($onSale)
                    <div style="font-family:'Share Tech Mono',monospace;font-size:12px;color:var(--muted);text-decoration:line-through;margin-bottom:4px">
                        ₱{{ number_format($product->selling_price, 2) }}
                    </div>
                    <div style="display:flex;align-items:baseline;gap:10px;margin-bottom:6px">
                        <span style="font-family:'Russo One',sans-serif;font-size:36px;color:#c9943a;letter-spacing:0.02em">
                            ₱{{ number_format($product->sale_price, 2) }}
                        </span>
                        @if($disc > 0)
                        <span style="font-family:'Share Tech Mono',monospace;font-size:11px;color:#c9943a;background:rgba(201,148,58,0.15);border:1px solid rgba(201,148,58,0.3);padding:2px 8px;border-radius:2px">
                            SAVE {{ $disc }}%
                        </span>
                        @endif
                    </div>
                    <p style="font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.12em;color:rgba(201,148,58,0.7);text-transform:uppercase">
                        // Limited time offer
                    </p>
                @else
                    <div style="display:flex;align-items:baseline;gap:8px;margin-bottom:4px">
                        <span style="font-family:'Russo One',sans-serif;font-size:36px;color:#39ff14;letter-spacing:0.02em">
                            ₱{{ number_format($product->selling_price, 2) }}
                        </span>
                        <span style="font-family:'Share Tech Mono',monospace;font-size:10px;color:var(--muted);letter-spacing:0.08em">
                            per {{ $product->unit_of_measure ?? 'pc' }}
                        </span>
                    </div>
                @endif
                @if($product->is_wholesale && $product->min_order_qty)
                <p style="font-family:'Share Tech Mono',monospace;font-size:10px;color:#9b3df0;margin-top:6px;letter-spacing:0.08em">
                    // Wholesale: min. order {{ number_format($product->min_order_qty) }} pcs
                </p>
                @endif
            </div>

            {{-- Stock status --}}
            <div style="display:flex;align-items:center;gap:8px">
                <span style="width:8px;height:8px;border-radius:50%;background:{{ $inStock ? '#39ff14' : '#e05050' }};
                             box-shadow:0 0 8px {{ $inStock ? '#39ff14' : '#e05050' }};flex-shrink:0;
                             {{ $inStock ? 'animation:pulse 2s infinite' : '' }}"></span>
                <span style="font-family:'Share Tech Mono',monospace;font-size:11px;letter-spacing:0.1em;
                             color:{{ $inStock ? '#39ff14' : '#e05050' }};text-transform:uppercase">
                    {{ $inStock ? "In Stock — {$stock} available" : 'Out of Stock' }}
                </span>
            </div>

            {{-- Product meta --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                @foreach([
                    'SKU'      => $product->sku,
                    'Category' => $product->category->name ?? '—',
                    'Supplier' => $product->supplier->company_name ?? '—',
                    'Tax'      => ($product->tax_rate ?? 12) . '%',
                ] as $lbl => $val)
                <div style="padding:10px 12px;background:#0d0b14;border:1px solid rgba(124,34,212,0.15);border-radius:3px">
                    <p style="font-family:'Share Tech Mono',monospace;font-size:8px;letter-spacing:0.16em;text-transform:uppercase;color:var(--muted);margin-bottom:4px">{{ $lbl }}</p>
                    <p style="font-family:'Exo 2',sans-serif;font-size:13px;color:#c8c4d4">{{ $val }}</p>
                </div>
                @endforeach
            </div>

            {{-- Add to cart / Out of stock --}}
            @if($inStock)
            <form method="POST" action="{{ route('shop.cart.add', $product->id) }}"
                  style="display:flex;gap:10px;align-items:center">
                @csrf
                <div style="display:flex;align-items:center;border:1px solid rgba(124,34,212,0.3);border-radius:3px;overflow:hidden">
                    <button type="button" onclick="adjQty(-1)"
                            style="width:38px;height:46px;background:#130f1e;border:none;color:#c8c4d4;font-size:18px;cursor:pointer;transition:background 0.2s"
                            onmouseover="this.style.background='#1a1428'" onmouseout="this.style.background='#130f1e'">−</button>
                    <input type="number" name="quantity" id="qtyInput" value="1" min="1" max="{{ $stock }}"
                           style="width:52px;height:46px;background:#0d0b14;border:none;border-left:1px solid rgba(124,34,212,0.2);border-right:1px solid rgba(124,34,212,0.2);text-align:center;color:#e8e4f0;font-family:'Share Tech Mono',monospace;font-size:15px;outline:none">
                    <button type="button" onclick="adjQty(1)"
                            style="width:38px;height:46px;background:#130f1e;border:none;color:#c8c4d4;font-size:18px;cursor:pointer;transition:background 0.2s"
                            onmouseover="this.style.background='#1a1428'" onmouseout="this.style.background='#130f1e'">+</button>
                </div>
                <button type="submit"
                        style="flex:1;height:46px;background:{{ $onSale ? '#c9943a' : 'var(--purple)' }};color:#fff;border:none;
                               font-family:'Russo One',sans-serif;font-size:13px;letter-spacing:0.07em;text-transform:uppercase;
                               border-radius:3px;cursor:pointer;transition:all 0.2s;
                               clip-path:polygon(0 0,calc(100% - 6px) 0,100% 6px,100% 100%,6px 100%,0 calc(100% - 6px));
                               box-shadow:0 0 20px {{ $onSale ? 'rgba(201,148,58,0.35)' : 'rgba(124,34,212,0.35)' }}"
                        onmouseover="this.style.background='{{ $onSale ? '#e0b44a' : '#9b3df0' }}';this.style.boxShadow='0 0 36px {{ $onSale ? 'rgba(201,148,58,0.6)' : 'rgba(124,34,212,0.6)' }}';this.style.transform='translateY(-1px)'"
                        onmouseout="this.style.background='{{ $onSale ? '#c9943a' : 'var(--purple)' }}';this.style.boxShadow='0 0 20px {{ $onSale ? 'rgba(201,148,58,0.35)' : 'rgba(124,34,212,0.35)' }}';this.style.transform=''">
                    + Add to Cart
                </button>
            </form>
            @else
            <button disabled
                    style="width:100%;height:46px;background:rgba(255,255,255,0.04);color:var(--muted);border:1px solid rgba(255,255,255,0.1);
                           border-radius:3px;font-family:'Share Tech Mono',monospace;font-size:11px;letter-spacing:0.12em;
                           text-transform:uppercase;cursor:not-allowed">
                Out of Stock
            </button>
            @endif

            {{-- Navigation links --}}
            <div style="display:flex;gap:12px;padding-top:4px">
                <a href="{{ route('shop.index') }}"
                   style="font-family:'Share Tech Mono',monospace;font-size:10px;letter-spacing:0.1em;text-transform:uppercase;
                          color:var(--muted);text-decoration:none;transition:color 0.2s"
                   onmouseover="this.style.color='#e8e4f0'" onmouseout="this.style.color='var(--muted)'">
                    ← Continue Shopping
                </a>
                <span style="color:rgba(124,34,212,0.3)">·</span>
                <a href="{{ route('shop.cart') }}"
                   style="font-family:'Share Tech Mono',monospace;font-size:10px;letter-spacing:0.1em;text-transform:uppercase;
                          color:var(--muted);text-decoration:none;transition:color 0.2s"
                   onmouseover="this.style.color='#39ff14'" onmouseout="this.style.color='var(--muted)'">
                    View Cart →
                </a>
            </div>
        </div>
    </div>

    {{-- Related products --}}
    @if($related->isNotEmpty())
    <div style="border-top:1px solid rgba(124,34,212,0.2);padding-top:60px;margin-bottom:80px">
        <div style="font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.24em;text-transform:uppercase;color:#39ff14;margin-bottom:10px;display:flex;align-items:center;gap:8px">
            <span style="opacity:0.4">//</span> Related Products
        </div>
        <h2 style="font-family:'Russo One',sans-serif;font-size:clamp(24px,3vw,40px);letter-spacing:0.04em;color:#e8e4f0;text-transform:uppercase;margin-bottom:28px">
            YOU MAY <span style="color:var(--purple2)">ALSO LIKE</span>
        </h2>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:2px;background:rgba(124,34,212,0.1)">
            @foreach($related as $rel)
            @php
                $relStock   = $rel->inventory->sum('quantity_on_hand') - $rel->inventory->sum('quantity_reserved');
                $relOnSale  = $rel->is_on_sale && $rel->sale_price;
            @endphp
            <a href="{{ route('shop.product', $rel->id) }}"
               style="background:#0d0b14;padding:24px 18px;border:1px solid transparent;position:relative;overflow:hidden;
                      text-decoration:none;display:block;transition:all 0.2s;cursor:pointer"
               onmouseover="this.style.background='#130f1e';this.style.borderColor='rgba(124,34,212,0.4)'"
               onmouseout="this.style.background='#0d0b14';this.style.borderColor='transparent'">
                @if($relOnSale)
                <div style="position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,#c9943a,#e0b44a)"></div>
                @endif
                <div style="font-family:'Share Tech Mono',monospace;font-size:8px;letter-spacing:0.18em;text-transform:uppercase;color:{{ $relOnSale ? '#c9943a' : 'rgba(124,34,212,0.6)' }};margin-bottom:8px">
                    {{ $rel->category->name ?? '—' }}
                </div>
                <div style="font-family:'Russo One',sans-serif;font-size:14px;letter-spacing:0.03em;color:#e8e4f0;text-transform:uppercase;line-height:1.15;margin-bottom:8px">
                    {{ $rel->name }}
                </div>
                <div style="font-family:'Share Tech Mono',monospace;font-size:9px;color:var(--muted);margin-bottom:12px">
                    {{ $rel->sku }}
                </div>
                @if($relOnSale)
                <div style="font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--muted);text-decoration:line-through;margin-bottom:2px">₱{{ number_format($rel->selling_price, 0) }}</div>
                <div style="font-family:'Russo One',sans-serif;font-size:18px;color:#c9943a">₱{{ number_format($rel->sale_price, 0) }}</div>
                @else
                <div style="font-family:'Russo One',sans-serif;font-size:18px;color:#39ff14">₱{{ number_format($rel->selling_price, 0) }}</div>
                @endif
                <div style="font-family:'Share Tech Mono',monospace;font-size:9px;margin-top:6px;color:{{ $relStock > 0 ? '#39ff14' : '#e05050' }}">
                    {{ $relStock > 0 ? '✓ In Stock' : '✗ Out of Stock' }}
                </div>
            </a>
            @endforeach
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

<script>
function adjQty(d) {
    const el  = document.getElementById('qtyInput');
    const max = parseInt(el.max) || 9999;
    const val = Math.min(Math.max(1, parseInt(el.value || 1) + d), max);
    el.value  = val;
}
</script>
</body>
</html>
