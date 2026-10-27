{{-- Nút gửi form chính. Khi đang gửi: khóa nút + đổi nhãn (resources/js/forms.js). --}}
<button type="submit" data-busy-label="{{ __('auth.busy') }}"
    {{ $attributes->class('inline-flex h-12 w-full items-center justify-center gap-3 rounded bg-lh-primary px-6 text-[15px] font-medium text-white transition-colors hover:bg-lh-primary-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary disabled:cursor-wait disabled:opacity-80') }}>
    <span data-button-label>{{ $slot }}</span>
    <x-lucide-arrow-right class="size-5 stroke-[1.5]" aria-hidden="true" />
</button>
