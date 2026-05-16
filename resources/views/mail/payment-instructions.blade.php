<x-mail::message>
# Payment Instructions

Hi **{{ $order->first_name }}**,

Thank you for your order! Please complete your payment to confirm your order.

---

<x-mail::panel>
## Order Summary

| | |
|---|---|
| **Order Number** | {{ $order->order_number }} |
| **Total Amount** | **₱{{ number_format($order->total_amount, 2) }}** |
| **Payment Method** | {{ strtoupper(str_replace('_', ' ', $order->payment_method)) }} |
@if($order->branch)
| **Pickup Branch** | {{ $order->branch->name }} — {{ $order->branch->city }} |
@endif
</x-mail::panel>

---

@if($order->payment_method === 'gcash')
## GCash Payment Instructions

<x-mail::panel>
1. Open your **GCash** app
2. Tap **Send Money → GCash**
3. Enter the number: **{{ config('services.gcash.number', '09XX-XXX-XXXX') }}**
4. Account name: **{{ config('services.gcash.name', 'Vapour Garage Main') }}**
5. Amount: **₱{{ number_format($order->total_amount, 2) }}**
6. In the **Message/Reference** field, type your order number: **{{ $order->order_number }}**
7. Send the payment and **screenshot the confirmation**
8. Reply to this email with the screenshot as proof of payment
</x-mail::panel>

> ⚠️ **Important:** Please include your order number **{{ $order->order_number }}** in the GCash message field so we can match your payment.
@endif

@if($order->payment_method === 'maya')
## Maya Payment Instructions

<x-mail::panel>
1. Open your **Maya** app
2. Tap **Send Money**
3. Enter the number: **{{ config('services.maya.number', '09XX-XXX-XXXX') }}**
4. Account name: **{{ config('services.maya.name', 'Vapour Garage Main') }}**
5. Amount: **₱{{ number_format($order->total_amount, 2) }}**
6. In the **Note/Message** field, type your order number: **{{ $order->order_number }}**
7. Send the payment and **screenshot the confirmation**
8. Reply to this email with the screenshot as proof of payment
</x-mail::panel>

> ⚠️ **Important:** Please include your order number **{{ $order->order_number }}** in the Maya message field so we can match your payment.
@endif

---

## Your Order

| Product | Qty | Price |
|---------|:---:|------:|
@foreach($order->items as $item)
| {{ $item->product_name }} | {{ $item->quantity }} | ₱{{ number_format($item->subtotal, 2) }} |
@endforeach
| **Subtotal** | | ₱{{ number_format($order->subtotal, 2) }} |
| VAT (12%) | | ₱{{ number_format($order->tax_amount, 2) }} |
| **Total** | | **₱{{ number_format($order->total_amount, 2) }}** |

---

Once we receive and verify your payment, your order status will be updated to **Confirmed**. You can track your order anytime using the link below.

<x-mail::button :url="route('shop.track') . '?order_number=' . $order->order_number . '&email=' . urlencode($order->email)">
Track My Order
</x-mail::button>

If you have questions, reply to this email or contact us on Facebook.

Thanks,
**Vapour Garage Main**
*10AM – 9PM daily · Nueva Ecija, Philippines*
</x-mail::message>
