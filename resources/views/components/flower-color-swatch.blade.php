@props([
    /** @var App\Enums\FlowerColor */
    'color',
    'name',
    'checked' => false,
])

{{-- Checkbox sắc hoa hiển thị thành chấm màu (storefront: bộ lọc; admin: chọn sắc cho sản phẩm). Viền ring khi được chọn. --}}
<label title="{{ $color->label() }}" style="background-color: {{ $color->hex() }}"
    {{ $attributes->class('relative size-[22px] shrink-0 cursor-pointer rounded-full border border-lh-border ring-lh-primary ring-offset-2 transition-shadow has-checked:ring-2 has-focus-visible:outline-2 has-focus-visible:outline-offset-4 has-focus-visible:outline-lh-primary motion-reduce:transition-none') }}>
    <input type="checkbox" name="{{ $name }}" value="{{ $color->value }}" class="sr-only" @checked($checked)>
    <span class="sr-only">{{ $color->label() }}</span>
</label>
