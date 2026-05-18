@php $title = 'Checkout'; @endphp
@include('shop._header')

<div style="padding:40px;max-width:1040px;margin:0 auto">
    <div class="sec-tag">Secure Checkout</div>
    <h1 class="sec-title" style="margin-bottom:32px">COMPLETE <span style="color:var(--green)">YOUR ORDER</span></h1>

    @if($errors->has('stock'))
        <div class="flash flash-err" style="margin-bottom:24px">
            <strong>Stock unavailable:</strong> {{ $errors->first('stock') }}
        </div>
    @elseif($errors->any())
        <div class="flash flash-err" style="margin-bottom:24px">{{ $errors->first() }}</div>
    @endif

    @php
        $subtotalAmt = $items->sum(fn ($i) => $i['product']->selling_price * $i['quantity']);
        $taxAmt      = round($subtotalAmt * 0.12, 2);
        $totalAmt    = $subtotalAmt + $taxAmt;
    @endphp

    <form method="POST" action="{{ route('shop.order.place') }}">
        @csrf
        <div style="display:grid;grid-template-columns:1fr 340px;gap:24px">

            {{-- Left: Contact + Delivery --}}
            <div style="display:flex;flex-direction:column;gap:20px">

                {{-- Contact Info --}}
                <div style="background:#130f1e;border:1px solid rgba(124,34,212,0.25);border-radius:6px;overflow:hidden">
                    <div style="height:2px;background:linear-gradient(90deg,#5b16a0,rgba(57,255,20,0.5),#5b16a0)"></div>
                    <div style="padding:18px 20px;border-bottom:1px solid rgba(124,34,212,0.15)">
                        <h3 style="font-family:'Russo One',sans-serif;font-size:12px;letter-spacing:0.1em;color:#e8e4f0;text-transform:uppercase">
                            // 01 · Contact Information
                        </h3>
                    </div>
                    <div style="padding:20px;display:flex;flex-direction:column;gap:16px">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                            <div>
                                <label class="vlabel">First Name *</label>
                                <input type="text" name="first_name" value="{{ old('first_name') }}" required class="vinput" placeholder="Juan">
                            </div>
                            <div>
                                <label class="vlabel">Last Name *</label>
                                <input type="text" name="last_name" value="{{ old('last_name') }}" required class="vinput" placeholder="dela Cruz">
                            </div>
                        </div>
                        <div>
                            <label class="vlabel">Email Address *</label>
                            <input type="email" name="email" value="{{ old('email') }}" required class="vinput" placeholder="juan@email.com">
                            <p style="margin-top:5px;font-family:'Share Tech Mono',monospace;font-size:9px;color:var(--muted)">Order confirmation will be sent here</p>
                        </div>
                        <div>
                            <label class="vlabel">Phone Number *</label>
                            <input type="tel" name="phone" value="{{ old('phone') }}" required class="vinput" placeholder="09XXXXXXXXX">
                        </div>
                    </div>
                </div>

                {{-- Delivery Info --}}
                <div style="background:#130f1e;border:1px solid rgba(124,34,212,0.25);border-radius:6px;overflow:hidden">
                    <div style="height:2px;background:linear-gradient(90deg,#5b16a0,rgba(57,255,20,0.5),#5b16a0)"></div>
                    <div style="padding:18px 20px;border-bottom:1px solid rgba(124,34,212,0.15)">
                        <h3 style="font-family:'Russo One',sans-serif;font-size:12px;letter-spacing:0.1em;color:#e8e4f0;text-transform:uppercase">
                            // 02 · Delivery / Pickup Details
                        </h3>
                    </div>
                    <div style="padding:20px;display:flex;flex-direction:column;gap:16px">
                        <div>
                            <label class="vlabel">Street Address *</label>
                            <input type="text" name="address" value="{{ old('address') }}" required class="vinput" placeholder="Unit/House No., Street, Barangay">
                        </div>
                        <div>
                            <label class="vlabel">City / Municipality *</label>
                            <input type="text" name="city" value="{{ old('city') }}" required class="vinput" placeholder="General Tiñio">
                        </div>
                        <div>
                            <label class="vlabel">Preferred Pickup Branch (optional)</label>
                            <select name="pickup_branch_id" class="vinput vselect">
                                <option value="">-- Home Delivery (coming soon) --</option>
                                @foreach(\App\Models\Branch::where('is_active',true)->get() as $branch)
                                    <option value="{{ $branch->id }}" {{ old('pickup_branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }} — {{ $branch->city }}
                                    </option>
                                @endforeach
                            </select>
                            <p style="margin-top:5px;font-family:'Share Tech Mono',monospace;font-size:9px;color:var(--muted)">Select a branch for store pickup (free) or leave blank for delivery</p>
                        </div>
                        <div>
                            <label class="vlabel">Order Notes (optional)</label>
                            <textarea name="notes" class="vinput" rows="3" style="resize:none" placeholder="Special instructions, preferred contact time, etc.">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Payment Method --}}
                <div style="background:#130f1e;border:1px solid rgba(124,34,212,0.25);border-radius:6px;overflow:hidden">
                    <div style="height:2px;background:linear-gradient(90deg,#5b16a0,rgba(57,255,20,0.5),#5b16a0)"></div>
                    <div style="padding:18px 20px;border-bottom:1px solid rgba(124,34,212,0.15)">
                        <h3 style="font-family:'Russo One',sans-serif;font-size:12px;letter-spacing:0.1em;color:#e8e4f0;text-transform:uppercase">
                            // 03 · Payment Method
                        </h3>
                    </div>
                    <div style="padding:20px;display:grid;grid-template-columns:1fr 1fr;gap:10px">
                        @foreach([
                            ['gcash', 'GCash',          'Mobile wallet payment','#39ff14'],
                            ['maya',  'Maya (PayMaya)', 'Mobile wallet payment','#39ff14'],
                            ['cod',   'Cash on Pickup', 'Pay when you collect', '#c9943a'],
                        ] as [$val, $lbl, $sub, $col])
                        <label style="display:flex;align-items:flex-start;gap:12px;padding:14px;border-radius:4px;border:1px solid rgba(124,34,212,0.2);cursor:pointer;transition:all 0.2s;background:#0d0b14"
                               onmouseover="this.style.borderColor='rgba(124,34,212,0.5)'" onmouseout="this.style.borderColor=this.querySelector('input').checked?'rgba(124,34,212,0.6)':'rgba(124,34,212,0.2)'">
                            <input type="radio" name="payment_method" value="{{ $val }}"
                                   {{ old('payment_method', 'gcash') === $val ? 'checked' : '' }}
                                   style="accent-color:var(--purple);margin-top:2px;cursor:pointer"
                                   onchange="document.querySelectorAll('[data-pm]').forEach(el=>el.style.borderColor='rgba(124,34,212,0.2)');this.closest('label').style.borderColor='rgba(124,34,212,0.6)'">
                            <div>
                                <div style="font-family:'Russo One',sans-serif;font-size:13px;letter-spacing:0.04em;color:#e8e4f0;text-transform:uppercase;margin-bottom:2px">{{ $lbl }}</div>
                                <div style="font-family:'Share Tech Mono',monospace;font-size:9px;color:var(--muted);letter-spacing:0.06em">{{ $sub }}</div>
                            </div>
                        </label>
                        @endforeach
                    </div>

                    {{-- Payment note --}}
                    <div style="padding:0 20px 20px">
                        <div style="padding:12px 14px;background:rgba(124,34,212,0.08);border:1px solid rgba(124,34,212,0.2);border-radius:4px">
                            <p style="font-family:'Share Tech Mono',monospace;font-size:10px;color:var(--muted);letter-spacing:0.04em;line-height:1.6">
                                📋 After placing your order, you will receive payment instructions via email. For GCash/Maya, your order will be confirmed once payment is verified. Cash on Pickup orders are confirmed upon collection.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Age verification --}}
                <div style="padding:14px 16px;background:rgba(201,148,58,0.06);border:1px solid rgba(201,148,58,0.25);border-radius:4px;display:flex;align-items:flex-start;gap:12px">
                    <label style="display:flex;align-items:flex-start;gap:12px;cursor:pointer">
                        <input type="checkbox" required style="margin-top:3px;accent-color:var(--purple);width:14px;height:14px;cursor:pointer">
                        <span style="font-family:'Share Tech Mono',monospace;font-size:11px;color:#c9943a;letter-spacing:0.04em;line-height:1.5">
                            I confirm I am 18 years or older and understand that Vapour Garage Main sells nicotine products regulated under Republic Act No. 11467.
                        </span>
                    </label>
                </div>
            </div>

            {{-- Right: Order summary --}}
            <div>
                <div style="background:#130f1e;border:1px solid rgba(124,34,212,0.25);border-radius:6px;overflow:hidden;position:sticky;top:80px">
                    <div style="height:2px;background:linear-gradient(90deg,#5b16a0,#39ff14,#5b16a0)"></div>
                    <div style="padding:18px 20px;border-bottom:1px solid rgba(124,34,212,0.15)">
                        <h3 style="font-family:'Russo One',sans-serif;font-size:12px;letter-spacing:0.1em;color:#e8e4f0;text-transform:uppercase">Order Summary</h3>
                    </div>
                    <div style="padding:16px 20px;display:flex;flex-direction:column;gap:10px;border-bottom:1px solid rgba(124,34,212,0.15)">
                        @foreach($items as $id => $item)
                        <div style="display:flex;align-items:flex-start;gap:10px">
                            <div style="flex:1;min-width:0">
                                <p style="font-family:'Exo 2',sans-serif;font-size:12px;font-weight:600;color:#e8e4f0;line-height:1.2">{{ $item['product']->name }}</p>
                                <p style="font-family:'Share Tech Mono',monospace;font-size:9px;color:var(--muted);margin-top:2px">{{ $item['product']->sku }} × {{ $item['quantity'] }}</p>
                            </div>
                            <span style="font-family:'Share Tech Mono',monospace;font-size:12px;color:#e8e4f0;white-space:nowrap">
                                ₱{{ number_format($item['product']->selling_price * $item['quantity'], 2) }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                    <div style="padding:14px 20px;display:flex;flex-direction:column;gap:8px;border-bottom:1px solid rgba(124,34,212,0.15)">
                        <div style="display:flex;justify-content:space-between;font-size:12px">
                            <span style="color:var(--muted)">Subtotal</span>
                            <span style="font-family:'Share Tech Mono',monospace;color:#e8e4f0">₱{{ number_format($subtotalAmt, 2) }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;font-size:12px">
                            <span style="color:var(--muted)">VAT (12%)</span>
                            <span style="font-family:'Share Tech Mono',monospace;color:#e8e4f0">₱{{ number_format($taxAmt, 2) }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;padding-top:6px;border-top:1px solid rgba(124,34,212,0.15)">
                            <span style="font-family:'Russo One',sans-serif;font-size:14px;letter-spacing:0.04em;color:#e8e4f0;text-transform:uppercase">Total</span>
                            <span style="font-family:'Russo One',sans-serif;font-size:22px;color:var(--green)">₱{{ number_format($totalAmt, 2) }}</span>
                        </div>
                    </div>
                    <div style="padding:16px 20px">
                        <button type="submit" style="width:100%;padding:14px;background:var(--purple);color:#fff;font-family:'Russo One',sans-serif;font-size:15px;letter-spacing:0.08em;text-transform:uppercase;border:none;border-radius:4px;cursor:pointer;box-shadow:0 0 24px rgba(124,34,212,0.4);transition:all 0.2s;clip-path:polygon(0 0,calc(100% - 6px) 0,100% 6px,100% 100%,6px 100%,0 calc(100% - 6px))"
                                onmouseover="this.style.background='#9b3df0';this.style.boxShadow='0 0 40px rgba(124,34,212,0.7)'"
                                onmouseout="this.style.background='var(--purple)';this.style.boxShadow='0 0 24px rgba(124,34,212,0.4)'">
                            PLACE ORDER →
                        </button>
                        <div style="display:flex;align-items:center;justify-content:center;gap:6px;margin-top:10px;font-family:'Share Tech Mono',monospace;font-size:9px;color:var(--muted)">
                            <svg style="width:11px;height:11px;color:var(--green)" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            SECURE · ENCRYPTED · RA 11467 COMPLIANT
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<footer class="shop-footer">
    <span class="sfooter-brand">VAPOUR GARAGE MAIN</span>
    <span class="sfooter-legal">© {{ date('Y') }} Vapour Garage Main · 18+ only</span>
    <span class="sfooter-age">// 18+ ONLY</span>
</footer>
</div></body></html>
