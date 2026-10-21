@props(['triggerClass' => ''])

{{--
    Chọn ngôn ngữ bằng icon quả địa cầu (header storefront). Dùng chung luồng với
    x-locale-switcher: form POST data-locale-form, resources/js/locale-switcher.js đổi trang không tải lại.
--}}
<details class="relative" data-dropdown>
    <summary class="{{ $triggerClass }} cursor-pointer list-none [&::-webkit-details-marker]:hidden">
        <x-lucide-globe class="size-5 stroke-[1.5]" aria-hidden="true" />
        <span class="sr-only">{{ __('storefront.nav.language') }}</span>
    </summary>

    <div class="absolute top-full right-0 z-30 mt-3 w-44 rounded-md border border-lh-border bg-lh-surface p-2 shadow-sm"
        role="group" aria-label="{{ __('common.language') }}" data-locale-switcher>
        @foreach (config('app.supported_locales') as $code => $name)
            @php($isCurrent = app()->getLocale() === $code)
            <form method="POST" action="{{ route('locale.update', $code) }}" data-locale-form data-index="{{ $loop->index }}">
                @csrf
                <button type="submit" lang="{{ $code }}" aria-pressed="{{ $isCurrent ? 'true' : 'false' }}"
                    class="flex w-full items-center justify-between rounded px-3 py-2.5 text-left text-sm transition-colors hover:bg-lh-sage-50 focus-visible:outline-2 focus-visible:outline-lh-primary aria-pressed:font-medium">
                    {{ $name }}
                    <x-lucide-check class="size-4 stroke-[1.5] text-lh-primary {{ $isCurrent ? '' : 'invisible' }}" aria-hidden="true" />
                </button>
            </form>
        @endforeach
    </div>
</details>
