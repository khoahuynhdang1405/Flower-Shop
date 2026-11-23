{{--
    Thông báo nhỏ góc dưới sau khi bấm "+" trên thẻ sản phẩm (resources/js/quick-add.js điền nội dung và hiện/ẩn).
    Vùng role="status" nằm ngoài khung bị ẩn để trình đọc màn hình luôn đọc được nội dung mới.
--}}
<p role="status" class="sr-only" data-cart-toast-live></p>
<div data-cart-toast hidden data-added-template="{{ __('storefront.product.added', ['name' => ':name']) }}"
    data-failed-message="{{ __('storefront.product.add_failed') }}"
    class="fixed inset-x-4 bottom-4 z-50 mx-auto flex max-w-sm items-center gap-3 rounded-md bg-lh-primary-dark py-3 pr-2 pl-4 text-sm text-white shadow-lg sm:right-6 sm:bottom-6 sm:left-auto sm:mx-0">
    <x-lucide-check class="size-4 shrink-0 stroke-[1.5]" aria-hidden="true" data-cart-toast-icon />
    <p class="min-w-0 flex-1" aria-hidden="true" data-cart-toast-message></p>
    <a href="{{ route('cart.show') }}" data-cart-toast-link
        class="shrink-0 rounded px-2 py-1 font-medium underline underline-offset-4 hover:no-underline focus-visible:outline-2 focus-visible:outline-white">
        {{ __('storefront.product.view_cart') }}
    </a>
    <button type="button" data-cart-toast-close aria-label="{{ __('storefront.product.close_toast') }}"
        class="grid size-8 shrink-0 place-items-center rounded-full hover:bg-white/10 focus-visible:outline-2 focus-visible:outline-white">
        <x-lucide-x class="size-4 stroke-[1.5]" aria-hidden="true" />
    </button>
</div>
