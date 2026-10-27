<div class="bg-lh-primary px-4 py-2.5 text-center text-xs text-white">
    {{ __('storefront.top_bar', ['amount' => number_format(config('shop.free_shipping_from'), 0, '', __('storefront.thousands_separator'))]) }}
</div>
