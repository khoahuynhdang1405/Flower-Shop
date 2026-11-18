@php
    $status = session('newsletter_status');
    $error = $errors->newsletter->first('email');
@endphp

{{-- Khối "Nhận một chút dịu dàng từ Lá" ngay trên footer Trang chủ. Gửi xong quay lại #newsletter kèm lời cảm ơn. --}}
<section id="newsletter" aria-labelledby="newsletter-title" class="scroll-mt-24 bg-lh-sage-50">
    <div class="mx-auto grid max-w-[1440px] items-center gap-8 px-4 py-12 sm:px-8 lg:grid-cols-[minmax(0,1fr)_minmax(0,460px)] lg:gap-16 lg:px-20 lg:py-14">
        <div class="flex items-start gap-5 sm:items-center sm:gap-6">
            <x-lucide-flower-2 class="size-12 shrink-0 stroke-1 text-lh-heading sm:size-16" aria-hidden="true" />
            <div>
                <h2 id="newsletter-title" class="font-serif text-2xl leading-snug text-lh-heading sm:text-[28px]">{{ __('newsletter.title') }}</h2>
                <p class="mt-2 text-sm leading-relaxed text-lh-muted">
                    {{ __('newsletter.body') }}<br class="hidden sm:inline">
                    {{ __('newsletter.offer') }}
                </p>
            </div>
        </div>

        <div aria-live="polite">
            @if ($status)
                <p class="flex items-start gap-3 rounded border border-lh-sage-200 bg-lh-surface px-4 py-3.5 text-sm leading-relaxed text-lh-heading">
                    <x-lucide-mail-check class="mt-0.5 size-5 shrink-0 stroke-[1.5] text-lh-primary" aria-hidden="true" />
                    {{ $status }}
                </p>
            @else
                <form method="POST" action="{{ route('newsletter.store') }}" novalidate>
                    @csrf
                    <div @class([
                        'flex rounded border bg-lh-cream p-1.5 transition-colors focus-within:border-lh-primary',
                        'border-lh-danger' => $error,
                        'border-lh-border' => ! $error,
                    ])>
                        <label for="newsletter-email" class="sr-only">{{ __('newsletter.email_label') }}</label>
                        <input id="newsletter-email" type="email" name="email" value="{{ old('email') }}" required maxlength="255"
                            autocomplete="email" placeholder="{{ __('newsletter.email_placeholder') }}"
                            @if ($error) aria-invalid="true" aria-describedby="newsletter-error" @endif
                            class="min-w-0 flex-1 bg-transparent px-3 text-sm text-lh-heading placeholder:text-lh-muted focus:outline-none">
                        <button type="submit"
                            class="inline-flex h-11 shrink-0 items-center gap-2.5 rounded bg-lh-primary px-5 text-sm font-medium text-white transition-colors hover:bg-lh-primary-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
                            {{ __('newsletter.submit') }}
                            <x-lucide-arrow-right class="size-4 stroke-[1.5]" aria-hidden="true" />
                        </button>
                    </div>

                    @if ($error)
                        <p id="newsletter-error" class="mt-2 text-xs text-lh-danger">{{ $error }}</p>
                    @else
                        <p class="mt-2 text-[11px] text-lh-muted">{{ __('newsletter.privacy') }}</p>
                    @endif
                </form>
            @endif
        </div>
    </div>
</section>
