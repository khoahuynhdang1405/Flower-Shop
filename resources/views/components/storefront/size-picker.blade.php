@props([
    /** @var list<array{id: int|string, name: string, price: int, dimension: string}> $sizes */
    'sizes',
    'selected',
    'name' => 'size',
])

@php
    $money = fn (int $amount): string => number_format($amount, 0, '', __('storefront.thousands_separator')).' '.__('storefront.currency');
    $dimensionOf = fn (array $size): string => __('product.sizes.dimension', ['size' => Str::lower($size['name']), 'dimension' => $size['dimension']]);
    $current = collect($sizes)->firstWhere('id', $selected) ?? $sizes[0];
@endphp

{{--
    Thẻ radio chọn size (daisyUI radio). Mỗi radio mang sẵn giá + kích thước đã định dạng (data-*);
    resources/js/size-picker.js cập nhật [data-size-price] và [data-size-dimension] trong cùng [data-product].
--}}
<fieldset {{ $attributes }}>
    <legend class="text-sm font-medium">{{ __('product.sizes.legend') }}</legend>

    <div class="mt-3 grid grid-cols-3 gap-2 sm:gap-3">
        @foreach ($sizes as $size)
            <label class="group relative flex cursor-pointer items-center gap-3 rounded-sm border border-lh-border bg-lh-surface px-3 py-3.5 transition-colors hover:border-lh-sage-200 has-checked:border-lh-primary has-checked:bg-lh-sage-50 has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-lh-primary sm:px-4 motion-reduce:transition-none">
                <input type="radio" name="{{ $name }}" value="{{ $size['id'] }}" @checked($size['id'] === $current['id'])
                    data-size-option data-price="{{ $money($size['price']) }}" data-dimension="{{ $dimensionOf($size) }}"
                    class="radio radio-primary radio-sm shrink-0 border-lh-border bg-lh-surface checked:border-lh-primary focus-visible:outline-none max-sm:sr-only">
                <span class="min-w-0">
                    <span class="block text-sm">{{ $size['name'] }}</span>
                    <x-money :amount="$size['price']" class="mt-0.5 block text-[11px] whitespace-nowrap text-lh-muted tabular-nums" />
                </span>
                <x-lucide-check class="ml-auto size-4 shrink-0 stroke-[1.5] text-lh-primary opacity-0 group-has-checked:opacity-100 max-sm:hidden" aria-hidden="true" />
            </label>
        @endforeach
    </div>

    <p class="mt-3 text-xs text-lh-muted" data-size-dimension>{{ $dimensionOf($current) }}</p>
</fieldset>
