@props([
    'name',
    // Hoa: tên Lora cỡ lớn; quà kèm: tên Inter nhỏ hơn (theo thiết kế).
    'serif' => false,
    /** @var list<string> $details  các dòng phụ dưới tên (cỡ, đơn giá) */
    'details' => [],
    'quantity',
    'total',
    // Route PATCH đổi số lượng / DELETE xóa dòng.
    'updateAction',
    'removeAction',
    // Trang chi tiết để "Sửa lựa chọn"; null = không có (quà kèm).
    'editUrl' => null,
    // Ảnh đã upload; chưa có thì ảnh tĩnh public/images/flowers/{imageName} hoặc khung giữ chỗ.
    'imageUrl' => null,
    'imageName' => '',
])

@php
    $textLink = 'rounded underline-offset-4 transition-colors hover:text-lh-primary-dark hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary';
@endphp

{{--
    Một dòng giỏ: ảnh | tên + dòng phụ + Sửa/Xóa | số lượng | thành tiền.
    Mobile: số lượng + thành tiền xuống hàng dưới tên (wrapper sm:contents trả hai ô về lưới 4 cột từ sm).
--}}
{{-- data-cart-removing: resources/js/cart.js gắn khi bấm Xóa để ẩn dòng ngay, không chờ server. --}}
<li {{ $attributes->class('grid grid-cols-[auto_minmax(0,1fr)] items-center gap-x-4 border-b border-lh-border py-6 data-cart-removing:hidden sm:grid-cols-[auto_minmax(0,1fr)_auto_8rem] sm:gap-x-6') }}>
    @if ($imageUrl)
        <img src="{{ $imageUrl }}" alt="" loading="lazy" class="row-span-2 aspect-[174/200] w-24 rounded-sm bg-lh-sage-100 object-cover sm:row-span-1 sm:w-32">
    @else
        <x-flower-image :name="$imageName" class="row-span-2 aspect-[174/200] w-24 rounded-sm sm:row-span-1 sm:w-32" />
    @endif

    <div class="min-w-0 self-center">
        <h3 @class([
            'text-lh-primary-dark',
            'font-serif text-2xl leading-tight sm:text-[28px]' => $serif,
            'text-[17px] font-medium' => ! $serif,
        ])>{{ $name }}</h3>
        @foreach ($details as $detail)
            <p @class(['text-sm text-lh-addon-muted', 'mt-2' => $loop->first, 'mt-0.5' => ! $loop->first])>{{ $detail }}</p>
        @endforeach

        <div class="mt-3 flex items-center gap-1.5 text-[13px] text-lh-addon-muted">
            @if ($editUrl)
                <a href="{{ $editUrl }}" class="{{ $textLink }}" aria-label="{{ __('cart.line.edit_label', ['name' => $name]) }}">{{ __('cart.line.edit') }}</a>
                <span aria-hidden="true">·</span>
            @endif
            <form method="POST" action="{{ $removeAction }}" data-cart-form>
                @csrf
                @method('DELETE')
                <button type="submit" class="{{ $textLink }} cursor-pointer" aria-label="{{ __('cart.line.remove_label', ['name' => $name]) }}">
                    {{ __('cart.line.remove') }}
                </button>
            </form>
        </div>
    </div>

    <div class="col-start-2 mt-4 flex items-center justify-between gap-4 sm:contents">
        <x-storefront.cart-quantity :action="$updateAction" :quantity="$quantity" :name="$name" />
        <x-money :amount="$total" class="text-base font-medium whitespace-nowrap text-lh-primary-dark tabular-nums sm:text-right" />
    </div>
</li>
