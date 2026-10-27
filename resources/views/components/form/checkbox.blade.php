@props(['name', 'checked' => false])

@php($hasError = $errors->has($name))

<div>
    <label class="flex cursor-pointer items-start gap-3 text-sm text-lh-heading">
        <input type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $checked))
            @if ($hasError) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
            {{ $attributes->class('mt-0.5 size-[18px] shrink-0 cursor-pointer rounded-[3px] accent-lh-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary') }} />
        <span>{{ $slot }}</span>
    </label>

    @error($name)
        <p id="{{ $name }}-error" class="mt-2 ml-[30px] text-xs text-lh-danger">{{ $message }}</p>
    @enderror
</div>
