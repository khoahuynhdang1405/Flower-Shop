{{-- Chuyển ngôn ngữ giao diện --}}
@php($locales = array_keys(config('app.supported_locales')))
@php($activeIndex = max(0, array_search(app()->getLocale(), $locales, true)))

<div {{ $attributes->class('relative isolate flex items-center rounded-full border border-lh-border p-0.5 text-xs') }}
    role="group" aria-label="{{ __('common.language') }}" data-locale-switcher
    style="--active-index: {{ $activeIndex }}">
    <span aria-hidden="true"
        class="absolute inset-y-0.5 left-0.5 -z-10 w-9 translate-x-[calc(var(--active-index)*100%)] rounded-full bg-lh-sage-50 transition-transform duration-200 ease-out motion-reduce:transition-none"></span>

    @foreach (config('app.supported_locales') as $code => $name)
        @php($isCurrent = app()->getLocale() === $code)
        <form method="POST" action="{{ route('locale.update', $code) }}" data-locale-form
            data-index="{{ $loop->index }}">
            @csrf
            <button type="submit" lang="{{ $code }}" title="{{ $name }}"
                aria-pressed="{{ $isCurrent ? 'true' : 'false' }}"
                class="w-9 rounded-full py-1.5 font-medium uppercase text-lh-muted transition-colors duration-200 hover:text-lh-heading aria-pressed:text-lh-heading focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-lh-primary">
                <span aria-hidden="true">{{ $code }}</span>
                <span class="sr-only">{{ $name }}</span>
            </button>
        </form>
    @endforeach
</div>
