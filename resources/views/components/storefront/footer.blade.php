{{-- Footer rút gọn (trang đăng nhập/đăng ký). TODO: footer 4 cột cho các trang còn lại. --}}
<footer class="border-t border-lh-border">
    <div class="mx-auto flex max-w-[1440px] flex-col gap-8 px-4 py-12 sm:px-8 md:flex-row md:items-end md:justify-between lg:px-20 lg:py-14">
        <div>
            <a href="{{ url('/') }}" class="inline-flex items-center gap-4 rounded text-lh-ink focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-lh-primary">
                <x-brand.flower-mark class="h-[30px] w-auto" />
                <span class="font-serif text-[40px] leading-none">{{ __('storefront.brand') }}</span>
            </a>
            <p class="mt-5 text-xs text-lh-muted">{{ __('storefront.footer.tagline', ['year' => now()->year]) }}</p>
        </div>

        <div class="flex flex-col gap-3 text-lh-muted md:items-end">
            <p class="text-sm">
                {{ __('storefront.footer.help', ['hotline' => config('shop.hotline'), 'email' => config('shop.email')]) }}
            </p>
            <nav aria-label="{{ __('storefront.footer.legal') }}">
                <ul class="flex items-center gap-3 text-xs">
                    {{-- TODO: trỏ tới trang chính sách khi có. --}}
                    <li><a href="#" class="rounded hover:text-lh-heading focus-visible:outline-2 focus-visible:outline-lh-primary">{{ __('storefront.legal.privacy') }}</a></li>
                    <li aria-hidden="true">·</li>
                    <li><a href="#" class="rounded hover:text-lh-heading focus-visible:outline-2 focus-visible:outline-lh-primary">{{ __('storefront.legal.terms') }}</a></li>
                </ul>
            </nav>
        </div>
    </div>
</footer>
