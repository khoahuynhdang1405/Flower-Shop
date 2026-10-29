@props([
    'name',
    'label',
    'hint' => null,
    'marker' => false,
    'value' => null,
    'rows' => 4,
])

@php
    $hasError = $errors->has($name);
    $describedBy = collect([$hint ? "{$name}-hint" : null, $hasError ? "{$name}-error" : null])->filter()->implode(' ');
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
        {{ $attributes->class([
            'w-full rounded border bg-lh-surface px-4 py-3 text-sm text-lh-heading placeholder:text-lh-muted transition-colors',
            'focus:outline-2 focus:-outline-offset-1 focus:outline-lh-primary',
            'border-lh-border' => ! $hasError,
            'border-lh-danger' => $hasError,
        ]) }}>{{ old($name, $value) }}</textarea>

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
