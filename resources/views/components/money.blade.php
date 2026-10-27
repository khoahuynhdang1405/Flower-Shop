@props(['amount'])

{{-- Tiền VNĐ theo ngôn ngữ hiện tại: 690.000 VNĐ / 690,000 VND. --}}
<span {{ $attributes }}>{{ number_format($amount, 0, '', __('storefront.thousands_separator')) }} {{ __('storefront.currency') }}</span>
