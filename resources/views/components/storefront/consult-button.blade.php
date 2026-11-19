{{-- Nút "Nhắn Lá để tư vấn riêng": mở Zalo của tiệm (config shop.zalo_url) ở tab mới. --}}
<a href="{{ config('shop.zalo_url') }}" target="_blank" rel="noopener"
    {{ $attributes->class('inline-flex h-12 items-center justify-center gap-3 self-start rounded bg-lh-primary px-6 text-[15px] font-medium text-white transition-colors hover:bg-lh-primary-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary') }}>
    {{ $slot }}
    <x-lucide-arrow-right class="size-4 stroke-[1.5]" aria-hidden="true" />
    <span class="sr-only">{{ __('storefront.opens_zalo') }}</span>
</a>
