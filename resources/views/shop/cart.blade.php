@php $title = 'Your Cart'; @endphp
@include('shop._header')

<div style="padding:40px;max-width:1000px;margin:0 auto">

    @if(session('success'))
        <div class="flash flash-ok" style="margin-bottom:24px">{{ session('success') }}</div>
    @endif

    <div class="sec-tag">Shopping Cart</div>
    <h1 class="sec-title" style="margin-bottom:32px">YOUR <span style="color:var(--purple2)">CART</span></h1>

    @if($items->isEmpty())
        {{-- Empty cart --}}
        <div style="text-align:center;padding:80px 24px;border:1px solid rgba(124,34,212,0.2);border-radius:6px;background:#130f1e">
            <div style="width:60px;height:60px;border-radius:6px;background:rgba(124,34,212,0.12);border:1px solid rgba(124,34,212,0.25);display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
                <svg style="width:28px;height:28px;color:rgba(124,34,212,0.5)" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            </div>
            <p style="font-family:'Russo One',sans-serif;font-size:16px;color:#e8e4f0;text-transform:uppercase;margin-bottom:8px">Your Cart is Empty</p>
            <p style="font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--muted);margin-bottom:24px">Browse our catalogue and add some products</p>
            <a href="{{ route('shop.index') }}" class="vbtn vbtn-primary">Browse Products</a>
        </div>
    @else
    <div style="display:grid;grid-template-columns:1fr 320px;gap:24px">

        {{-- Cart items --}}
        <div style="display:flex;flex-direction:column;gap:2px;background:rgba(124,34,212,0.1)">
            @foreach($items as $id => $item)
            @php
                $product  = $item['product'];
                $qty      = $item['quantity'];
                $subtotal = $product->selling_price * $qty;
            @endphp
            <div style="background:#0d0b14;padding:20px;display:grid;grid-template-columns:80px 1fr auto;gap:16px;align-items:center;border:1px solid transparent;transition:border-color 0.2s"
                 onmouseover="this.style.borderColor='rgba(124,34,212,0.3)'" onmouseout="this.style.borderColor='transparent'">

                {{-- Image placeholder --}}
                <div style="width:80px;height:80px;background:#130f1e;border:1px solid rgba(124,34,212,0.2);border-radius:4px;display:flex;align-items:center;justify-content:center">
                    <svg style="width:32px;height:32px;color:rgba(124,34,212,0.3)" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                </div>

                {{-- Info --}}
                <div>
                    <div style="font-family:'Share Tech Mono',monospace;font-size:8.5px;letter-spacing:0.16em;text-transform:uppercase;color:rgba(124,34,212,0.6);margin-bottom:5px">
                        {{ $product->sku }} · {{ $product->category->name ?? '' }}
                    </div>
                    <h3 style="font-family:'Russo One',sans-serif;font-size:15px;letter-spacing:0.03em;color:#e8e4f0;text-transform:uppercase;margin-bottom:6px">
                        {{ $product->name }}
                    </h3>
                    <span style="font-family:'Russo One',sans-serif;font-size:13px;color:var(--green)">
                        ₱{{ number_format($product->selling_price, 2) }} each
                    </span>
                </div>

                {{-- Qty + remove --}}
                <div style="display:flex;flex-direction:column;align-items:flex-end;gap:10px">
                    <span style="font-family:'Russo One',sans-serif;font-size:18px;color:#e8e4f0">
                        ₱{{ number_format($subtotal, 2) }}
                    </span>
                    <div style="display:flex;align-items:center;gap:6px">
                        <form method="POST" action="{{ route('shop.cart.update', $product->id) }}" style="display:flex;align-items:center;gap:6px">
                            @csrf @method('PATCH')
                            <button type="submit" name="quantity" value="{{ max(1, $qty - 1) }}"
                                    style="width:28px;height:28px;background:#130f1e;border:1px solid rgba(124,34,212,0.3);border-radius:3px;color:var(--grey);cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;transition:all 0.2s"
                                    onmouseover="this.style.borderColor='rgba(124,34,212,0.6)'" onmouseout="this.style.borderColor='rgba(124,34,212,0.3)'">−</button>
                            <span style="font-family:'Share Tech Mono',monospace;font-size:14px;color:#e8e4f0;min-width:24px;text-align:center">{{ $qty }}</span>
                            <button type="submit" name="quantity" value="{{ $qty + 1 }}"
                                    style="width:28px;height:28px;background:#130f1e;border:1px solid rgba(124,34,212,0.3);border-radius:3px;color:var(--grey);cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;transition:all 0.2s"
                                    onmouseover="this.style.borderColor='rgba(124,34,212,0.6)'" onmouseout="this.style.borderColor='rgba(124,34,212,0.3)'">+</button>
                        </form>
                        <form method="POST" action="{{ route('shop.cart.remove', $product->id) }}">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    style="width:28px;height:28px;background:rgba(224,80,80,0.08);border:1px solid rgba(224,80,80,0.25);border-radius:3px;color:#e05050;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.2s"
                                    onmouseover="this.style.background='rgba(224,80,80,0.18)'" onmouseout="this.style.background='rgba(224,80,80,0.08)'">
                                <svg style="width:12px;height:12px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Order summary --}}
        <div>
            <div style="background:#130f1e;border:1px solid rgba(124,34,212,0.25);border-radius:6px;overflow:hidden;position:sticky;top:80px">
                <div style="height:2px;background:linear-gradient(90deg,#5b16a0,#39ff14,#5b16a0)"></div>
                <div style="padding:20px 20px 0;border-bottom:1px solid rgba(124,34,212,0.15)">
                    <h3 style="font-family:'Russo One',sans-serif;font-size:13px;letter-spacing:0.1em;color:#e8e4f0;text-transform:uppercase;margin-bottom:16px">Order Summary</h3>

                    @php
                        $subtotalAmt = $items->sum(fn ($i) => $i['product']->selling_price * $i['quantity']);
                        $taxAmt      = round($subtotalAmt * 0.12, 2);
                        $totalAmt    = $subtotalAmt + $taxAmt;
                    @endphp

                    <div style="display:flex;flex-direction:column;gap:10px;padding-bottom:16px">
                        @foreach($items as $id => $item)
                        <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--muted)">
                            <span style="font-family:'Exo 2',sans-serif">{{ Str::limit($item['product']->name, 24) }} × {{ $item['quantity'] }}</span>
                            <span style="font-family:'Share Tech Mono',monospace">₱{{ number_format($item['product']->selling_price * $item['quantity'], 2) }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div style="padding:16px 20px;display:flex;flex-direction:column;gap:8px">
                    <div style="display:flex;justify-content:space-between;font-size:12px">
                        <span style="color:var(--muted)">Subtotal</span>
                        <span style="font-family:'Share Tech Mono',monospace;color:#e8e4f0">₱{{ number_format($subtotalAmt, 2) }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:12px">
                        <span style="color:var(--muted)">VAT (12%)</span>
                        <span style="font-family:'Share Tech Mono',monospace;color:#e8e4f0">₱{{ number_format($taxAmt, 2) }}</span>
                    </div>
                    <div style="height:1px;background:rgba(124,34,212,0.2);margin:6px 0"></div>
                    <div style="display:flex;justify-content:space-between">
                        <span style="font-family:'Russo One',sans-serif;font-size:14px;letter-spacing:0.04em;color:#e8e4f0;text-transform:uppercase">Total</span>
                        <span style="font-family:'Russo One',sans-serif;font-size:20px;color:var(--green)">₱{{ number_format($totalAmt, 2) }}</span>
                    </div>
                </div>

                <div style="padding:0 20px 20px;display:flex;flex-direction:column;gap:10px">
                    <a href="{{ route('shop.checkout') }}" class="vbtn vbtn-primary" style="justify-content:center;width:100%">
                        Proceed to Checkout →
                    </a>
                    <a href="{{ route('shop.index') }}" class="vbtn vbtn-ghost" style="justify-content:center;width:100%;font-size:11px">
                        ← Continue Shopping
                    </a>
                </div>

                <div style="padding:12px 20px;border-top:1px solid rgba(124,34,212,0.15);background:rgba(0,0,0,0.2)">
                    <div style="display:flex;align-items:center;gap:6px;font-family:'Share Tech Mono',monospace;font-size:9.5px;color:var(--muted);letter-spacing:0.06em">
                        <svg style="width:11px;height:11px;color:var(--green)" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        SECURE ORDER · RA 11467 COMPLIANT
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<footer class="shop-footer">
    <span class="sfooter-brand">VAPOUR GARAGE MAIN</span>
    <span class="sfooter-legal">© {{ date('Y') }} Vapour Garage Main · 18+ only</span>
    <span class="sfooter-age">// 18+ ONLY</span>
</footer>
</div></body></html>
