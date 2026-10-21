@props([
    'name',
    'label',
    'type' => 'text',
    'hint' => null,
    // Hiện dấu * cạnh nhãn (trang đăng ký có, trang đăng nhập không).
    'marker' => false,
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
            @if ($type !== 'password') value="{{ old($name) }}" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            @if ($hasError) aria-invalid="true" @endif
            {{ $attributes->class([
                'h-12 w-full rounded border bg-lh-surface px-4 text-sm text-lh-heading placeholder:text-lh-muted transition-colors',
                'focus:outline-2 focus:-outline-offset-1 focus:outline-lh-primary',
                'border-lh-border' => ! $hasError,
                'border-lh-danger' => $hasError,
            ]) }} />
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
