@php $title = 'Track Your Order'; @endphp
@include('shop._header')

<div style="padding:60px 40px;max-width:720px;margin:0 auto">

    <div class="sec-tag">Order Tracking</div>
    <h1 class="sec-title" style="margin-bottom:8px">TRACK YOUR <span style="color:var(--purple2)">ORDER</span></h1>
    <p style="font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--muted);letter-spacing:0.06em;margin-bottom:36px">
        Enter your order number and email to check status
    </p>

    {{-- Search form --}}
    <form method="GET" action="{{ route('shop.track') }}"
          style="background:#130f1e;border:1px solid rgba(124,34,212,0.25);border-radius:6px;overflow:hidden;margin-bottom:32px">
        <div style="height:2px;background:linear-gradient(90deg,#5b16a0,rgba(57,255,20,0.5),#5b16a0)"></div>
        <div style="padding:24px;display:flex;flex-direction:column;gap:16px">
            <div>
                <label class="vlabel">Order Number *</label>
                <input type="text" name="order_number" value="{{ request('order_number', $order?->order_number) }}"
                       class="vinput" placeholder="VGM-XXXXXXXX" required style="font-family:'Share Tech Mono',monospace;letter-spacing:0.1em">
            </div>
            <div>
                <label class="vlabel">Email Address *</label>
                <input type="email" name="email" value="{{ request('email', $order?->email) }}"
                       class="vinput" placeholder="The email used when ordering">
            </div>
            <button type="submit" class="vbtn vbtn-primary" style="align-self:flex-start">
                <svg style="width:14px;height:14px;fill:none;stroke:currentColor;stroke-width:2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                Track Order
            </button>
        </div>
    </form>

    {{-- Results --}}
    @if(request('order_number'))
        @if(!$order)
            <div style="text-align:center;padding:48px 24px;border:1px solid rgba(224,80,80,0.25);border-radius:6px;background:rgba(224,80,80,0.04)">
                <p style="font-family:'Russo One',sans-serif;font-size:16px;color:#e05050;text-transform:uppercase;margin-bottom:8px">Order Not Found</p>
                <p style="font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--muted)">
                    Check your order number and email address and try again.
                </p>
            </div>
        @else
        @php
            $steps = [
                'pending'    => ['Pending',           'Your order has been received and is awaiting confirmation.'],
                'confirmed'  => ['Confirmed',          'Your order has been confirmed. Payment verified.'],
                'processing' => ['Processing',         'We\'re preparing your order at the branch.'],
                'ready'      => ['Ready for Pickup',   'Your order is ready! Head to the branch anytime.'],
                'completed'  => ['Completed',          'Order completed. Thank you for shopping with us!'],
                'cancelled'  => ['Cancelled',          'This order has been cancelled. Contact us for assistance.'],
            ];
            $allSteps = ['pending','confirmed','processing','ready','completed'];
            $currentIdx = array_search($order->status, $allSteps);
            if ($order->status === 'cancelled') $currentIdx = -1;
        @endphp

        {{-- Order header --}}
        <div style="background:#130f1e;border:1px solid rgba(124,34,212,0.25);border-radius:6px;overflow:hidden;margin-bottom:20px">
            <div style="height:2px;background:linear-gradient(90deg,#5b16a0,#39ff14,#5b16a0)"></div>
            <div style="padding:20px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid rgba(124,34,212,0.15)">
                <div>
                    <p style="font-family:'Russo One',sans-serif;font-size:20px;letter-spacing:0.08em;color:#39ff14">{{ $order->order_number }}</p>
                    <p style="font-family:'Share Tech Mono',monospace;font-size:10px;color:var(--muted);margin-top:4px;letter-spacing:0.06em">
                        Placed {{ $order->created_at->format('F d, Y · g:i A') }}
                    </p>
                </div>
                @php
                    $sv = match($order->status) {
                        'completed'  => 'background:rgba(57,255,20,0.1);color:#39ff14;border:1px solid rgba(57,255,20,0.3)',
                        'cancelled'  => 'background:rgba(224,80,80,0.1);color:#e05050;border:1px solid rgba(224,80,80,0.3)',
                        'ready'      => 'background:rgba(57,255,20,0.1);color:#39ff14;border:1px solid rgba(57,255,20,0.3)',
                        default      => 'background:rgba(124,34,212,0.15);color:#9b3df0;border:1px solid rgba(124,34,212,0.35)',
                    };
                @endphp
                <span style="padding:6px 14px;border-radius:4px;font-family:'Russo One',sans-serif;font-size:12px;letter-spacing:0.08em;text-transform:uppercase;{{ $sv }}">
                    {{ $order->status_label }}
                </span>
            </div>

            {{-- Status timeline --}}
            @if($order->status !== 'cancelled')
            <div style="padding:24px 20px;border-bottom:1px solid rgba(124,34,212,0.15)">
                <div style="display:flex;align-items:flex-start;gap:0;position:relative">
                    @foreach($allSteps as $i => $step)
                    @php
                        $done   = $currentIdx >= $i;
                        $active = $currentIdx === $i;
                        $label  = $steps[$step][0];
                    @endphp
                    <div style="flex:{{ $loop->last ? '0' : '1' }};display:flex;align-items:flex-start">
                        <div style="display:flex;flex-direction:column;align-items:center">
                            <div style="width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:'Russo One',sans-serif;font-size:11px;transition:all 0.3s;
                                {{ $done ? 'background:#39ff14;color:#060608;box-shadow:0 0 16px rgba(57,255,20,0.4)' : 'background:#130f1e;border:1px solid rgba(124,34,212,0.3);color:#7a7490' }}">
                                @if($done && !$active)
                                    <svg style="width:14px;height:14px" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                @else
                                    {{ $i + 1 }}
                                @endif
                            </div>
                            <p style="font-family:'Share Tech Mono',monospace;font-size:8px;letter-spacing:0.1em;text-transform:uppercase;margin-top:8px;text-align:center;white-space:nowrap;color:{{ $done ? '#39ff14' : '#7a7490' }}">{{ $label }}</p>
                        </div>
                        @if(!$loop->last)
                        <div style="flex:1;height:2px;margin-top:15px;background:{{ $currentIdx > $i ? 'linear-gradient(90deg,#39ff14,rgba(57,255,20,0.3))' : 'rgba(124,34,212,0.2)' }}"></div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Status message --}}
            <div style="padding:14px 20px;background:rgba(0,0,0,0.2)">
                <p style="font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--muted);letter-spacing:0.04em;line-height:1.5">
                    {{ $steps[$order->status][1] ?? '' }}
                    @if($order->branch && in_array($order->status, ['confirmed','processing','ready']))
                        <br><strong style="color:#e8e4f0">Pickup at: {{ $order->branch->name }}</strong> — {{ $order->branch->address }}, {{ $order->branch->city }}
                    @endif
                </p>
            </div>
        </div>

        {{-- Items --}}
        <div style="background:#130f1e;border:1px solid rgba(124,34,212,0.25);border-radius:6px;overflow:hidden;margin-bottom:20px">
            <div style="padding:14px 20px;border-bottom:1px solid rgba(124,34,212,0.15)">
                <h3 style="font-family:'Russo One',sans-serif;font-size:11px;letter-spacing:0.1em;color:#e8e4f0;text-transform:uppercase">Items Ordered</h3>
            </div>
            @foreach($order->items as $item)
            <div style="display:flex;align-items:center;justify-content:space-between;padding:11px 20px;border-bottom:1px solid rgba(124,34,212,0.08)">
                <div>
                    <p style="font-size:13px;color:#e8e4f0">{{ $item->product_name }}</p>
                    <p style="font-family:'Share Tech Mono',monospace;font-size:9px;color:var(--muted);margin-top:2px">{{ $item->sku }} × {{ $item->quantity }}</p>
                </div>
                <span style="font-family:'Russo One',sans-serif;font-size:14px;color:#e8e4f0">₱{{ number_format($item->subtotal, 2) }}</span>
            </div>
            @endforeach
            <div style="padding:12px 20px;display:flex;justify-content:space-between;align-items:center">
                <span style="font-family:'Russo One',sans-serif;font-size:14px;color:#e8e4f0;text-transform:uppercase;letter-spacing:0.04em">Total</span>
                <span style="font-family:'Russo One',sans-serif;font-size:20px;color:#39ff14">₱{{ number_format($order->total_amount, 2) }}</span>
            </div>
        </div>

        {{-- Help --}}
        <div style="padding:14px 18px;background:rgba(124,34,212,0.06);border:1px solid rgba(124,34,212,0.2);border-radius:4px">
            <p style="font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--muted);letter-spacing:0.04em;line-height:1.6">
                Questions about your order? Message us on
                <strong style="color:#9b3df0">Facebook: @VapourGarageMain</strong> or email
                <strong style="color:#9b3df0">orders@vapourgarage.ph</strong> with your order number.
            </p>
        </div>
        @endif
    @endif
</div>

<footer class="shop-footer">
    <span class="sfooter-brand">VAPOUR GARAGE MAIN</span>
    <span class="sfooter-legal">© {{ date('Y') }} Vapour Garage Main · 18+ only</span>
    <span class="sfooter-age">// 18+ ONLY</span>
</footer>
</div></body></html>
