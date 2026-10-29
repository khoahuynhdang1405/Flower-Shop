@props([
    'name',
    'label',
    'type' => 'text',
    'hint' => null,
    // Hiện dấu * cạnh nhãn (trang đăng ký có, trang đăng nhập không).
    'marker' => false,
    // Giá trị sẵn có (old() vẫn được ưu tiên khi form vừa gửi lỗi).
    'value' => null,
    // Kiểm tra ngay khi gõ (resources/js/live-validation.js): hợp lệ thì viền đậm + dấu ✓.
    'validate' => false,
])

@php
    $hasError = $errors->has($name);
    $describedBy = collect([$hint ? "{$name}-hint" : null, $hasError ? "{$name}-error" : null])->filter()->implode(' ');
@endphp

<div>
    <label for="{{ $name }}" class="mb-2 block text-sm font-medium text-lh-heading">
        {{ $label }}
        @if ($marker)
            <span aria-hidden="true">*</span>
            <span class="sr-only">{{ __('auth.required') }}</span>
        @endif
    </label>

    <div class="relative">
        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
            @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            @if ($hasError) aria-invalid="true" @endif
            @if ($validate) data-validate @endif
            {{ $attributes->class([
                'peer h-12 w-full rounded border bg-lh-surface px-4 text-sm text-lh-heading placeholder:text-lh-muted transition-colors',
                'focus:outline-2 focus:-outline-offset-1 focus:outline-lh-primary',
                'border-lh-border' => ! $hasError,
                'border-lh-danger' => $hasError,
                'pr-11 data-valid:border-lh-heading' => $validate,
            ]) }} />
        @if ($validate)
            <x-lucide-check class="pointer-events-none absolute inset-y-0 right-4 my-auto hidden size-4 stroke-[1.5] text-lh-heading peer-data-valid:block" aria-hidden="true" />
        @endif
        {{ $suffix ?? '' }}
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
