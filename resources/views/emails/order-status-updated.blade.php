<x-mail::message>
# {{ $headline }}

Hi {{ $order->customer?->name ?? 'there' }},

{{ $subtext }}

@if ($customMessage)
> {{ $customMessage }}
@endif

<x-mail::panel>
**Current Status:** {{ $statusBadge }}  
**Order Number:** #{{ $order->id }}  
**Order Date:** {{ $order->created_at ? $order->created_at->format('F j, Y, g:i a') : 'Recently' }}  
**Payment Method:** {{ ucfirst(str_replace('_', ' ', $order->payment_method)) }} ({{ ucfirst($order->payment_status) }})
@if ($trackingNumber)
<br>**Courier Tracking Number:** `{{ $trackingNumber }}`
@endif
</x-mail::panel>

---

### Order Items

<x-mail::table>
| Product | Qty | Unit Price | Subtotal |
|:--------|:---:|----------:|---------:|
@foreach ($order->items as $item)
| {{ $item->product?->title ?? 'Custom Item' }} | {{ $item->quantity }} | ${{ number_format($item->unit_price, 2) }} | ${{ number_format($item->unit_price * $item->quantity, 2) }} |
@endforeach
</x-mail::table>

@if ($order->shipping_amount > 0)
**Shipping Fee:** ${{ number_format($order->shipping_amount, 2) }}  
@endif
**Total Amount:** ${{ number_format($order->total_price, 2) }}

---

### Shipping Address
{{ $order->customer?->address ?? 'Address on file' }}

@if ($order->notes)
**Order Notes:**  
{{ $order->notes }}
@endif

<x-mail::button :url="url('/my-orders')">
View Order in Account
</x-mail::button>

If you have any questions or need to make an inquiry, simply reply directly to this email or reach out to our customer support.

Thanks for shopping with {{ config('app.name', 'Quirka') }}!
</x-mail::message>
