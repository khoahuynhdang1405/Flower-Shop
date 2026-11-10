@props([
    'name',
    'value',
    'label',
    'hint' => null,
    'checked' => false,
])

{{--
    Thẻ chọn một (radio thật, ẩn khỏi mắt nhưng vẫn nhận bàn phím): chấm tròn + nhãn (+ dòng phụ) + dấu ✓ khi chọn.
    Trạng thái đã chọn dựa vào :has(:checked) nên không cần JS.
--}}
<label {{ $attributes->class([
    'group relative flex min-h-12 cursor-pointer items-center gap-3 rounded-sm border border-lh-border bg-lh-surface px-4 py-3 transition-colors',
    'hover:border-lh-addon-muted has-checked:border-lh-addon-accent has-checked:bg-lh-addon-selected',
    'has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-lh-primary',
]) }}>
    <input type="radio" name="{{ $name }}" value="{{ $value }}" class="peer sr-only" @checked($checked)>
    <span class="grid size-4 shrink-0 place-items-center rounded-full border border-lh-border bg-lh-surface peer-checked:border-lh-addon-accent" aria-hidden="true">
        <span class="hidden size-2.5 rounded-full bg-lh-addon-accent group-has-checked:block"></span>
    </span>
    <span class="min-w-0 flex-1">
        <span class="block text-sm text-lh-primary-dark">{{ $label }}</span>
        @if ($hint)
            <span class="mt-0.5 block text-xs text-lh-addon-muted">{{ $hint }}</span>
        @endif
    </span>
    <x-lucide-check class="hidden size-4 shrink-0 stroke-2 text-lh-addon-accent group-has-checked:block" aria-hidden="true" />
</label>
