@props([
    /** @var array{id: int, name: string, price: int, is_free: bool, image_url: ?string} $addon  image_url null = khung giữ chỗ */
    'addon',
    'checked' => false,
])

@php
    $priceText = $addon['is_free']
        ? __('product.addons.free')
        : number_format($addon['price'], 0, '', __('storefront.thousands_separator')).' '.__('storefront.currency');
@endphp

{{--
    Một món quà kèm: cả thẻ là <label> bọc checkbox thật (bấm chỗ nào cũng chọn, Tab/Space dùng được).
    Trạng thái chọn vẽ bằng has-checked; resources/js/addon-picker.js đọc data-name / data-price để cập nhật dòng tóm tắt.
--}}
<label class="group flex cursor-pointer flex-col rounded-md border border-lh-border bg-lh-surface p-3 transition-colors hover:border-lh-sage-200 has-checked:border-lh-addon-accent has-checked:bg-lh-addon-selected has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-lh-primary motion-reduce:transition-none">
    @if ($addon['image_url'])
        <img src="{{ $addon['image_url'] }}" alt="" loading="lazy" class="aspect-[278/158] w-full rounded-sm bg-lh-sage-100 object-cover">
    @else
        <x-flower-image name="" class="aspect-[278/158] rounded-sm" />
    @endif

    <span class="mt-3 text-[15px] text-lh-primary-dark">{{ $addon['name'] }}</span>
    {{-- TODO: dòng phụ "Hộp 6 viên" / "Lời nhắn riêng của bạn" chưa có cột trong bảng addons. --}}
    <span class="mt-1 text-[13px] text-lh-addon-muted">{{ $priceText }}</span>

    <span class="mt-4 flex items-center gap-2 text-[13px] text-lh-primary-dark">
        <input type="checkbox" name="addons[]" value="{{ $addon['id'] }}" @checked($checked)
            data-addon-option data-name="{{ Str::lower($addon['name']) }}" data-price="{{ $addon['price'] }}"
            aria-label="{{ __('product.addons.add_label', ['name' => $addon['name']]) }}"
            class="checkbox checkbox-xs rounded-[3px] border-lh-addon-accent bg-lh-surface checked:border-lh-addon-accent checked:bg-lh-addon-selected checked:text-lh-addon-accent focus-visible:outline-none">
        <span class="group-has-checked:hidden" aria-hidden="true">{{ __('product.addons.add') }}</span>
        <span class="hidden group-has-checked:inline" aria-hidden="true">{{ __('product.addons.selected') }}</span>
    </span>
</label>
