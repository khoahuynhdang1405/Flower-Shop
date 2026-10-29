@props([
    'name',
    'label',
    // Danh sách lựa chọn: giá trị lưu cũng là nhãn hiển thị.
    'options' => [],
    'placeholder' => null,
    'hint' => null,
    'marker' => false,
    'value' => null,
])

@php
    $hasError = $errors->has($name);
    $selected = old($name, $value);
    $describedBy = collect([$hint ? "{$name}-hint" : null, $hasError ? "{$name}-error" : null])->filter()->implode(' ');
@endphp

{{-- Ô chọn cùng kiểu với x-form.input. --}}
<div>
    <label for="{{ $name }}" class="mb-2 block text-sm font-medium text-lh-heading">
        {{ $label }}
        @if ($marker)
            <span aria-hidden="true">*</span>
            <span class="sr-only">{{ __('auth.required') }}</span>
        @endif
    </label>

    <div class="relative">
        <select id="{{ $name }}" name="{{ $name }}"
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            @if ($hasError) aria-invalid="true" @endif
            {{ $attributes->class([
                'h-12 w-full appearance-none rounded border bg-lh-surface pr-11 pl-4 text-sm text-lh-heading transition-colors',
                'focus:outline-2 focus:-outline-offset-1 focus:outline-lh-primary',
                'border-lh-border' => ! $hasError,
                'border-lh-danger' => $hasError,
            ]) }}>
            @if ($placeholder)
                <option value="" disabled @selected(blank($selected))>{{ $placeholder }}</option>
            @endif
            @foreach ($options as $option)
                <option value="{{ $option }}" @selected($option === $selected)>{{ $option }}</option>
            @endforeach
        </select>
        <x-lucide-chevron-down class="pointer-events-none absolute inset-y-0 right-4 my-auto size-4 stroke-[1.5] text-lh-muted" aria-hidden="true" />
    </div>

    @if ($hint)
        <p id="{{ $name }}-hint" class="mt-2 text-xs text-lh-muted">{{ $hint }}</p>
    @endif

    @error($name)
        <p id="{{ $name }}-error" class="mt-2 flex items-start gap-1.5 text-xs text-lh-danger">
            <x-lucide-circle-alert class="mt-px size-3.5 shrink-0 stroke-[1.5]" aria-hidden="true" />
            {{ $message }}
        </p>
    @enderror
</div>
