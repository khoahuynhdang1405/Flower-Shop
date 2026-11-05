@props([
    /** @var list<array{id: int|string, name: string, price: int, is_free: bool, note: ?string, image: string}> $addons */
    'addons',
    /** @var list<int|string> $selected  id các món được chọn sẵn */
    'selected' => [],
])

@php
    $chosen = collect($addons)->whereIn('id', $selected);
    $names = $chosen->map(fn (array $addon): string => Str::lower($addon['name']))->values();
    $total = $chosen->sum('price');
    $items = $names->count() > 1
        ? $names->slice(0, -1)->implode(', ').__('product.addons.and').$names->last()
        : $names->first();
    $summary = match (true) {
        $names->isEmpty() => __('product.addons.summary_empty'),
        $total === 0 => __('product.addons.summary_free', ['items' => $items]),
        default => __('product.addons.summary', [
            'items' => $items,
            'total' => number_format($total, 0, '', __('storefront.thousands_separator')).' '.__('storefront.currency'),
        ]),
    };
@endphp

{{--
    Khối "Thêm một chút thương yêu": tiêu đề + dòng tóm tắt món đã chọn (aria-live) + lưới thẻ món quà.
    Tóm tắt render sẵn từ server; resources/js/addon-picker.js dựng lại khi khách tích/bỏ tích, theo cùng các mẫu câu trong data-*.
--}}
<section {{ $attributes }} aria-labelledby="addons-title" data-addon-picker
    data-summary="{{ __('product.addons.summary') }}" data-summary-free="{{ __('product.addons.summary_free') }}"
    data-summary-empty="{{ __('product.addons.summary_empty') }}" data-and="{{ __('product.addons.and') }}"
    data-thousands="{{ __('storefront.thousands_separator') }}" data-currency="{{ __('storefront.currency') }}">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <h2 id="addons-title" class="font-serif text-3xl leading-tight text-lh-primary-dark sm:text-[32px]">{{ __('product.addons.title') }}</h2>
        <p class="text-[13px] text-lh-addon-muted" aria-live="polite" data-addon-summary>{{ $summary }}</p>
    </div>

    <div class="mt-6 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        @foreach ($addons as $addon)
            <x-storefront.addon-card :addon="$addon" :checked="in_array($addon['id'], $selected, true)" />
        @endforeach
    </div>
</section>
