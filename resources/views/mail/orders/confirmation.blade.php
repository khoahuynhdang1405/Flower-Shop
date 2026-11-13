@php
    /**
     * @var \App\Models\Order $order (kèm deliveryDetail, items, promotion)
     *
     * Nội dung bám trang xác nhận (resources/views/orders/confirmation.blade.php). Markdown: không thụt dòng nội dung.
     */
    use App\Enums\PaymentStatus;

    $delivery = $order->deliveryDetail;
    $money = fn (int $amount): string => number_format($amount, 0, '', __('storefront.thousands_separator')).' '.__('storefront.currency');
    $method = $order->payment_method->label();
    $paymentStatus = $order->payment_status === PaymentStatus::Paid
        ? __('order.confirmation.badge.paid', ['method' => $method])
        : __('order.confirmation.badge.cod');
    // Tên gọi: hai chữ cuối của họ tên ("Huỳnh Đăng Khoa" → "Đăng Khoa").
    $callName = implode(' ', array_slice(preg_split('/\s+/', trim($delivery->sender_name)), -2));
@endphp
<x-mail::message>
# {{ __('order.confirmation.success', ['code' => $order->order_code]) }}

{{ __('order.mail.greeting', ['name' => $callName]) }}

{{ __('order.mail.intro', ['code' => $order->order_code]) }}

**{{ __('order.mail.payment', ['status' => $paymentStatus]) }}**

<x-mail::table>
| {{ __('order.mail.items.item') }} | {{ __('order.mail.items.quantity') }} | {{ __('order.mail.items.total') }} |
|:--|:-:|--:|
@foreach ($order->items as $item)
| {{ $item->item_name }}{{ $item->size_name ? ' · '.$item->size_name : '' }} | {{ $item->quantity }} | {{ $money($item->total_price) }} |
@endforeach
| {{ __('order.mail.subtotal') }} | | {{ $money($order->subtotal) }} |
@if ($order->discount_amount > 0)
| {{ __('order.mail.discount', ['code' => $order->promotion?->code]) }} | | −{{ $money($order->discount_amount) }} |
@endif
| {{ __('order.mail.shipping') }} | | {{ $order->shipping_fee > 0 ? $money($order->shipping_fee) : __('order.mail.free') }} |
| **{{ __('order.mail.total') }}** | | **{{ $money($order->total_amount) }}** |
</x-mail::table>

<x-mail::panel>
**{{ __('order.mail.schedule') }}:** {{ $delivery->delivery_date->format('d/m/Y') }} · {{ $delivery->time_slot }}<br>
**{{ __('order.mail.receiver') }}:** {{ $delivery->receiver_name }} · {{ $delivery->receiver_phone }}<br>
**{{ __('order.mail.address') }}:** {{ $delivery->shipping_address }}, {{ $delivery->shipping_district }}
</x-mail::panel>

@if ($delivery->is_gift && $delivery->card_message)
**{{ __('order.mail.card') }}**

> “{{ $delivery->card_message }}”

@endif
## {{ __('order.confirmation.next.title') }}

{{ __('order.confirmation.next.body', ['code' => $order->order_code]) }}

{{ __('order.confirmation.help.body', ['hotline' => config('shop.hotline'), 'code' => $order->order_code]) }}

<x-mail::button :url="url('/')">
{{ __('order.mail.visit') }}
</x-mail::button>

{{ __('order.mail.signature') }}<br>
{{ config('app.name') }}
</x-mail::message>
