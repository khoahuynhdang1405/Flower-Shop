@props(['name', 'label', 'hint' => null, 'marker' => false])

{{-- Ô mật khẩu có nút ẩn/hiện (resources/js/forms.js). --}}
<x-form.input :name="$name" :label="$label" type="password" :hint="$hint" :marker="$marker" class="pr-12"
    {{ $attributes }}>
    <x-slot:suffix>
        <button type="button" data-password-toggle aria-controls="{{ $name }}" aria-pressed="false"
            data-label-show="{{ __('auth.show_password') }}" data-label-hide="{{ __('auth.hide_password') }}"
            aria-label="{{ __('auth.show_password') }}"
            class="absolute inset-y-0 right-1.5 my-auto grid size-9 place-items-center rounded text-lh-heading transition-colors hover:bg-lh-sage-50 focus-visible:outline-2 focus-visible:outline-lh-primary">
            <x-lucide-eye class="size-5 stroke-[1.5]" data-icon-show aria-hidden="true" />
            <x-lucide-eye-off class="hidden size-5 stroke-[1.5]" data-icon-hide aria-hidden="true" />
        </button>
    </x-slot:suffix>
</x-form.input>
