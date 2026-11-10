@props([
    'name',
    'label',
    // Giới hạn chọn ngày, dạng Y-m-d.
    'min',
    'max',
    'placeholder',
    // Thông báo khi bỏ trống ô bắt buộc (thay cho bong bóng mặc định của trình duyệt).
    'requiredMessage' => null,
    'hint' => null,
    'value' => null,
])

{{--
    Ô chọn ngày (resources/js/date-picker.js): nút hiển thị "Thứ Năm, 08/10/2026" + lịch tháng bật xuống.
    Giá trị vẫn nằm trong <input type="date"> thật (gửi Y-m-d, nhận kiểm tra required/min/max của form);
    không có JS thì khách dùng ô ngày gốc của trình duyệt.
--}}
@php
    $hasError = $errors->has($name);
    $describedBy = collect([$hint ? "{$name}-hint" : null, "{$name}-error"])->filter()->implode(' ');
    $labels = [
        'weekdays' => __('common.date_picker.weekdays'),
        'month' => __('common.date_picker.month'),
        'required' => $requiredMessage,
    ];
@endphp

<div data-date-picker data-labels="{{ json_encode($labels) }}">
    <label for="{{ $name }}" id="{{ $name }}-label" class="mb-2 block text-sm font-medium text-lh-heading">{{ $label }}</label>

    <div class="relative">
        <input id="{{ $name }}" name="{{ $name }}" type="date" value="{{ old($name, $value) }}" min="{{ $min }}" max="{{ $max }}"
            aria-describedby="{{ $describedBy }}" @if ($hasError) aria-invalid="true" @endif data-date-picker-input
            {{ $attributes->class([
                'h-12 w-full rounded border bg-lh-surface px-4 text-sm text-lh-heading transition-colors',
                'focus:outline-2 focus:-outline-offset-1 focus:outline-lh-primary',
                'border-lh-border' => ! $hasError,
                'border-lh-danger' => $hasError,
            ]) }} />

        <button type="button" id="{{ $name }}-trigger" hidden aria-haspopup="dialog" aria-expanded="false" aria-controls="{{ $name }}-dialog"
            aria-labelledby="{{ $name }}-label {{ $name }}-value" aria-describedby="{{ $describedBy }}" @if ($hasError) aria-invalid="true" @endif
            data-date-picker-trigger
            class="flex h-12 w-full cursor-pointer items-center justify-between gap-3 rounded border border-lh-border bg-lh-surface px-4 text-left text-sm text-lh-heading tabular-nums transition-colors hover:border-lh-addon-muted focus-visible:outline-2 focus-visible:-outline-offset-1 focus-visible:outline-lh-primary aria-expanded:border-lh-addon-accent aria-invalid:border-lh-danger">
            <span id="{{ $name }}-value" class="truncate data-empty:text-lh-muted" data-date-picker-value data-placeholder="{{ $placeholder }}">{{ $placeholder }}</span>
            <x-lucide-calendar-days class="size-[18px] shrink-0 stroke-[1.5] text-lh-primary-dark" aria-hidden="true" />
        </button>

        <div id="{{ $name }}-dialog" role="dialog" aria-modal="false" aria-labelledby="{{ $name }}-month" hidden data-date-picker-dialog
            class="absolute top-full left-0 z-30 mt-2 w-full max-w-[320px] rounded-md border border-lh-border bg-lh-surface p-4 shadow-[0_12px_32px_-12px_rgb(39_63_51/0.25)]">
            <div class="flex items-center justify-between gap-2">
                <button type="button" aria-label="{{ __('common.date_picker.previous_month') }}" data-date-picker-prev
                    class="grid size-9 cursor-pointer place-items-center rounded-sm text-lh-primary-dark transition-colors hover:bg-lh-addon-selected focus-visible:outline-2 focus-visible:outline-lh-primary disabled:cursor-not-allowed disabled:text-lh-border disabled:hover:bg-transparent">
                    <x-lucide-chevron-left class="size-4 stroke-[1.5]" aria-hidden="true" />
                </button>
                <p id="{{ $name }}-month" aria-live="polite" class="font-serif text-lg text-lh-primary-dark" data-date-picker-month></p>
                <button type="button" aria-label="{{ __('common.date_picker.next_month') }}" data-date-picker-next
                    class="grid size-9 cursor-pointer place-items-center rounded-sm text-lh-primary-dark transition-colors hover:bg-lh-addon-selected focus-visible:outline-2 focus-visible:outline-lh-primary disabled:cursor-not-allowed disabled:text-lh-border disabled:hover:bg-transparent">
                    <x-lucide-chevron-right class="size-4 stroke-[1.5]" aria-hidden="true" />
                </button>
            </div>

            <table role="grid" aria-labelledby="{{ $name }}-month" class="mt-3 w-full table-fixed border-collapse text-center text-sm">
                <thead>
                    <tr>
                        @foreach (__('common.date_picker.weekdays_short') as $index => $short)
                            <th scope="col" abbr="{{ __('common.date_picker.weekdays')[$index] }}" class="pb-2 text-xs font-normal text-lh-addon-muted">{{ $short }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody data-date-picker-grid></tbody>
            </table>
        </div>
    </div>

    @if ($hint)
        <p id="{{ $name }}-hint" class="mt-2 text-xs text-lh-muted">{{ $hint }}</p>
    @endif

    <p id="{{ $name }}-error" @class(['mt-2 flex items-start gap-1.5 text-xs text-lh-danger', 'hidden' => ! $hasError]) data-date-picker-error>
        <x-lucide-circle-alert class="mt-px size-3.5 shrink-0 stroke-[1.5]" aria-hidden="true" />
        <span data-date-picker-error-text>{{ $errors->first($name) }}</span>
    </p>
</div>
