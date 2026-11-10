@props([
    'name',
    'label',
    'hint' => null,
    'marker' => false,
    'value' => null,
    'rows' => 4,
    // Hiện "đã gõ/tối đa" theo maxlength (resources/js/forms.js), đổi màu khi còn ≤ 10% ký tự.
    'counter' => false,
])

@php
    $hasError = $errors->has($name);
    $value = old($name, $value);
    $maxLength = $counter ? (int) $attributes->get('maxlength') : 0;
    $describedBy = collect([
        $hint ? "{$name}-hint" : null,
        $maxLength ? "{$name}-counter" : null,
        $hasError ? "{$name}-error" : null,
    ])->filter()->implode(' ');
@endphp

{{-- Ô nhập nhiều dòng cùng kiểu với x-form.input. --}}
<div>
    <label for="{{ $name }}" class="mb-2 block text-sm font-medium text-lh-heading">
        {{ $label }}
        @if ($marker)
            <span aria-hidden="true">*</span>
            <span class="sr-only">{{ __('auth.required') }}</span>
        @endif
    </label>

    <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}"
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        @if ($hasError) aria-invalid="true" @endif
        @if ($maxLength) data-char-count="{{ $name }}-counter" @endif
        {{ $attributes->class([
            'w-full rounded border bg-lh-surface px-4 py-3 text-sm text-lh-heading placeholder:text-lh-muted transition-colors',
            'focus:outline-2 focus:-outline-offset-1 focus:outline-lh-primary',
            'border-lh-border' => ! $hasError,
            'border-lh-danger' => $hasError,
        ]) }}>{{ $value }}</textarea>

    @if ($hint || $maxLength)
        <div class="mt-2 flex items-start justify-between gap-4 text-xs">
            @if ($hint)
                <p id="{{ $name }}-hint" class="text-lh-muted">{{ $hint }}</p>
            @endif
            @if ($maxLength)
                @php($length = mb_strlen((string) $value))
                <p id="{{ $name }}-counter" class="ml-auto shrink-0 text-lh-muted tabular-nums data-near-limit:font-medium data-near-limit:text-lh-danger"
                    data-max="{{ $maxLength }}" @if ($maxLength - $length <= $maxLength * 0.1) data-near-limit @endif>{{ $length }}/{{ $maxLength }}</p>
            @endif
        </div>
    @endif

    @error($name)
        <p id="{{ $name }}-error" class="mt-2 flex items-start gap-1.5 text-xs text-lh-danger">
            <x-lucide-circle-alert class="mt-px size-3.5 shrink-0 stroke-[1.5]" aria-hidden="true" />
            {{ $message }}
        </p>
    @enderror
</div>
