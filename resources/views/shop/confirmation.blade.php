@php $title = 'Order Confirmed — ' . $order->order_number; @endphp
@include('shop._header')

<div style="padding:60px 40px;max-width:760px;margin:0 auto">

    {{-- Success animation --}}
    <div style="text-align:center;margin-bottom:48px">
        <div style="width:80px;height:80px;border-radius:50%;background:rgba(57,255,20,0.1);border:2px solid rgba(57,255,20,0.4);display:flex;align-items:center;justify-content:center;margin:0 auto 24px;box-shadow:0 0 40px rgba(57,255,20,0.2);animation:pulse-confirm 2s ease-in-out infinite">
            <svg style="width:36px;height:36px;color:#39ff14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
        </div>
        <div class="sec-tag" style="justify-content:center">Order Confirmed</div>
        <h1 style="font-family:'Russo One',sans-serif;font-size:clamp(36px,5vw,60px);letter-spacing:0.04em;color:#e8e4f0;text-transform:uppercase;line-height:0.95;margin-bottom:12px">
            ORDER <span style="color:#39ff14">PLACED!</span>
        </h1>
        <p style="font-family:'Share Tech Mono',monospace;font-size:12px;color:var(--muted);letter-spacing:0.08em">
            Thank you, {{ $order->first_name }}! Your order has been received.
        </p>
    </div>

    {{-- Order number highlight --}}
    <div style="background:#130f1e;border:1px solid rgba(57,255,20,0.3);border-radius:6px;padding:24px;text-align:center;margin-bottom:24px;position:relative;overflow:hidden;box-shadow:0 0 30px rgba(57,255,20,0.08)">
        <div style="position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,#39ff14,transparent)"></div>
        <p style="font-family:'Share Tech Mono',monospace;font-size:10px;letter-spacing:0.22em;text-transform:uppercase;color:rgba(57,255,20,0.6);margin-bottom:10px">Your Order Number</p>
        <p style="font-family:'Russo One',sans-serif;font-size:32px;letter-spacing:0.1em;color:#39ff14;text-shadow:0 0 24px rgba(57,255,20,0.4)">
            {{ $order->order_number }}
        </p>
        <p style="font-family:'Share Tech Mono',monospace;font-size:10px;color:var(--muted);margin-top:8px;letter-spacing:0.06em">
            Save this number to track your order
        </p>
    </div>

    {{-- Order details --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px">
        <div style="background:#130f1e;border:1px solid rgba(124,34,212,0.25);border-radius:6px;padding:18px">
            <p style="font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.2em;text-transform:uppercase;color:rgba(124,34,212,0.6);margin-bottom:12px">Customer Info</p>
            @foreach([
                'Name'    => $order->full_name,
                'Email'   => $order->email,
                'Phone'   => $order->phone,
                'Address' => $order->address . ', ' . $order->city,
            ] as $lbl => $val)
            <div style="display:flex;flex-direction:column;gap:1px;margin-bottom:10px;last-child:margin-bottom:0">
                <span style="font-family:'Share Tech Mono',monospace;font-size:8.5px;letter-spacing:0.14em;text-transform:uppercase;color:var(--muted)">{{ $lbl }}</span>
                <span style="font-size:13px;color:#e8e4f0">{{ $val }}</span>
            </div>
            @endforeach
        </div>

        <div style="background:#130f1e;border:1px solid rgba(124,34,212,0.25);border-radius:6px;padding:18px">
            <p style="font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.2em;text-transform:uppercase;color:rgba(124,34,212,0.6);margin-bottom:12px">Order Info</p>
            @foreach([
                'Payment'  => ucfirst(str_replace('_',' ', $order->payment_method)),
                'Pickup'   => $order->branch?->name ?? 'To be arranged',
                'Status'   => $order->status_label,
                'Date'     => $order->created_at->format('F d, Y · g:i A'),
            ] as $lbl => $val)
            <div style="display:flex;flex-direction:column;gap:1px;margin-bottom:10px">
                <span style="font-family:'Share Tech Mono',monospace;font-size:8.5px;letter-spacing:0.14em;text-transform:uppercase;color:var(--muted)">{{ $lbl }}</span>
                <span style="font-size:13px;color:#e8e4f0">{{ $val }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Items ordered --}}
    <div style="background:#130f1e;border:1px solid rgba(124,34,212,0.25);border-radius:6px;overflow:hidden;margin-bottom:24px">
        <div style="height:2px;background:linear-gradient(90deg,#5b16a0,rgba(57,255,20,0.5),#5b16a0)"></div>
        <div style="padding:16px 20px;border-bottom:1px solid rgba(124,34,212,0.15)">
            <h3 style="font-family:'Russo One',sans-serif;font-size:12px;letter-spacing:0.1em;color:#e8e4f0;text-transform:uppercase">Items Ordered</h3>
        </div>
        @foreach($order->items as $item)
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-bottom:1px solid rgba(124,34,212,0.1)">
            <div>
                <p style="font-size:13px;font-weight:600;color:#e8e4f0">{{ $item->product_name }}</p>
                <p style="font-family:'Share Tech Mono',monospace;font-size:9px;color:var(--muted);margin-top:2px;letter-spacing:0.06em">{{ $item->sku }} · {{ $item->quantity }} × ₱{{ number_format($item->unit_price, 2) }}</p>
            </div>
            <span style="font-family:'Russo One',sans-serif;font-size:15px;color:#e8e4f0">₱{{ number_format($item->subtotal, 2) }}</span>
        </div>
        @endforeach
        <div style="padding:14px 20px;display:flex;flex-direction:column;gap:6px">
            <div style="display:flex;justify-content:space-between;font-size:12px">
                <span style="color:var(--muted)">Subtotal</span>
                <span style="font-family:'Share Tech Mono',monospace;color:#e8e4f0">₱{{ number_format($order->subtotal, 2) }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:12px">
                <span style="color:var(--muted)">VAT (12%)</span>
                <span style="font-family:'Share Tech Mono',monospace;color:#e8e4f0">₱{{ number_format($order->tax_amount, 2) }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding-top:8px;border-top:1px solid rgba(124,34,212,0.15)">
                <span style="font-family:'Russo One',sans-serif;font-size:14px;color:#e8e4f0;text-transform:uppercase">Total Paid</span>
                <span style="font-family:'Russo One',sans-serif;font-size:22px;color:#39ff14">₱{{ number_format($order->total_amount, 2) }}</span>
            </div>
        </div>
    </div>

    {{-- Payment instructions --}}
    @if($order->payment_method !== 'cod')
    <div style="background:rgba(124,34,212,0.06);border:1px solid rgba(124,34,212,0.25);border-radius:6px;padding:18px;margin-bottom:24px">
        <p style="font-family:'Russo One',sans-serif;font-size:12px;letter-spacing:0.08em;color:#9b3df0;text-transform:uppercase;margin-bottom:10px">// Payment Instructions</p>
        @if($order->payment_method === 'gcash')
            <p style="font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--muted);line-height:1.7;letter-spacing:0.04em">
                Send payment to <strong style="color:#e8e4f0">GCash: 09XX-XXX-XXXX (Vapour Garage Main)</strong><br>
                Use your order number <strong style="color:#39ff14">{{ $order->order_number }}</strong> as your reference.<br>
                Send screenshot to our Facebook page for faster confirmation.
            </p>
        @elseif($order->payment_method === 'maya')
            <p style="font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--muted);line-height:1.7;letter-spacing:0.04em">
                Send payment to <strong style="color:#e8e4f0">Maya: 09XX-XXX-XXXX (Vapour Garage Main)</strong><br>
                Use order number <strong style="color:#39ff14">{{ $order->order_number }}</strong> as your reference.<br>
                Send screenshot to confirm your payment.
            </p>
        @elseif($order->payment_method === 'bank_transfer')
            <p style="font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--muted);line-height:1.7;letter-spacing:0.04em">
                Transfer to <strong style="color:#e8e4f0">BPI Account: XXXX-XXXX-XX · Vapour Garage Main</strong><br>
                Use order number <strong style="color:#39ff14">{{ $order->order_number }}</strong> as your reference.<br>
                Email deposit slip to <strong style="color:#e8e4f0">orders@vapourgarage.ph</strong>
            </p>
        @endif
    </div>
    @endif

    {{-- Actions --}}
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
        <a href="{{ route('shop.track') }}?order_number={{ $order->order_number }}&email={{ $order->email }}"
           class="vbtn vbtn-outline">Track My Order</a>
        <a href="{{ route('shop.index') }}" class="vbtn vbtn-ghost">Continue Shopping</a>
    </div>
</div>

<footer class="shop-footer">
    <span class="sfooter-brand">VAPOUR GARAGE MAIN</span>
    <span class="sfooter-legal">© {{ date('Y') }} Vapour Garage Main · 18+ only</span>
    <span class="sfooter-age">// 18+ ONLY</span>
</footer>
</div>

<style>
@keyframes pulse-confirm {
    0%,100%{box-shadow:0 0 40px rgba(57,255,20,0.2),0 0 0 0 rgba(57,255,20,0.2)}
    50%{box-shadow:0 0 60px rgba(57,255,20,0.3),0 0 20px 10px rgba(57,255,20,0.05)}
}
</style>
</body>
</html>
