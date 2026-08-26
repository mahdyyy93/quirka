<x-mail::message>
# Your order is confirmed! 🎉

Hi {{ $order->customer->name }},

Thank you for your purchase. We've received your order and it's now being processed.

---

## Order Summary

**Order #:** {{ $order->id }}
**Date:** {{ $order->created_at->format('F j, Y') }}
**Payment Method:** {{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}
**Payment Status:** {{ ucfirst($order->payment_status) }}

<x-mail::table>
| Product | Qty | Unit Price | Subtotal |
|:--------|:---:|----------:|---------:|
@foreach ($order->items as $item)
| {{ $item->product->title }} | {{ $item->quantity }} | ${{ number_format($item->unit_price, 2) }} | ${{ number_format($item->unit_price * $item->quantity, 2) }} |
@endforeach
</x-mail::table>

**Total: ${{ number_format($order->total_price, 2) }}**

---

## Shipping Address

{{ $order->customer->address }}

@if ($order->notes)
## Notes

{{ $order->notes }}
@endif

---

We'll notify you when your order ships. If you have any questions, feel free to reply to this email.

<x-mail::button :url="url('/shop')">
Continue Shopping
</x-mail::button>

Thanks for choosing {{ config('app.name') }}!
</x-mail::message>
