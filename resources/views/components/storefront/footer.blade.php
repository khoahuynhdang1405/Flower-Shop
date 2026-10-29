@php
    $columns = [
        'explore' => ['all_flowers', 'occasions', 'custom', 'our_story'],
        'support' => ['how_to_order', 'shipping', 'returns', 'faq'],
    ];
    $link = 'rounded text-sm text-white/80 transition-colors hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white';
    $heading = 'text-xs font-semibold tracking-wide text-white uppercase';
@endphp

{{-- Footer đầy đủ 4 cột (nền xanh đậm) cho các trang storefront. --}}
<footer class="bg-lh-primary-dark text-white">
    <div class="mx-auto max-w-[1440px] px-4 pt-14 pb-8 sm:px-8 lg:px-20 lg:pt-16">
        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-[minmax(0,1.4fr)_repeat(3,minmax(0,1fr))] lg:gap-12">
            <div>
                <a href="{{ url('/') }}" class="inline-flex items-center gap-3 rounded focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white">
                    <x-brand.flower-mark class="h-7 w-auto" />
                    <span class="font-serif text-[32px] leading-none">{{ __('storefront.brand') }}</span>
                </a>
                <p class="mt-5 max-w-[260px] text-sm leading-relaxed text-white/80">{{ __('storefront.footer.about') }}</p>
                <ul class="mt-6 flex items-center gap-2" aria-label="{{ __('storefront.footer.social') }}">
                    @foreach (config('shop.socials') as $network => $url)
                        <li>
                            <a href="{{ $url }}" class="grid size-9 place-items-center rounded-full text-white/85 transition-colors hover:bg-white/10 hover:text-white focus-visible:outline-2 focus-visible:outline-white">
                                <x-dynamic-component :component="'lucide-'.$network" class="size-5 stroke-[1.5]" aria-hidden="true" />
                                <span class="sr-only">{{ ucfirst($network) }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            @foreach ($columns as $column => $items)
                <nav aria-labelledby="footer-{{ $column }}">
                    <h2 id="footer-{{ $column }}" class="{{ $heading }}">{{ __('storefront.footer.'.$column) }}</h2>
                    <ul class="mt-5 flex flex-col gap-3">
                        @foreach ($items as $item)
                            {{-- TODO: trỏ tới trang tương ứng khi có. --}}
                            <li><a href="#" class="{{ $link }}">{{ __('storefront.footer.links.'.$item) }}</a></li>
                        @endforeach
                    </ul>
                </nav>
            @endforeach

            <div>
                <h2 class="{{ $heading }}">{{ __('storefront.footer.visit') }}</h2>
                <address class="mt-5 flex flex-col gap-3 text-sm text-white/80 not-italic">
                    <span>{{ config('shop.address') }}</span>
                    <span>{{ __('storefront.footer.daily', ['hours' => config('shop.opening_hours')]) }}</span>
                    <a href="tel:{{ config('shop.hotline') }}" class="rounded text-lg font-medium text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">{{ config('shop.hotline') }}</a>
                    <a href="mailto:{{ config('shop.email') }}" class="{{ $link }}">{{ config('shop.email') }}</a>
                </address>
            </div>
        </div>

        <div class="mt-14 flex flex-col gap-4 border-t border-lh-on-dark-divider pt-6 text-xs text-white/70 sm:flex-row sm:items-center sm:justify-between">
            <p>{{ __('storefront.footer.tagline', ['year' => now()->year]) }}</p>
            <nav aria-label="{{ __('storefront.footer.legal') }}">
                <ul class="flex items-center gap-3">
                    {{-- TODO: trỏ tới trang chính sách khi có. --}}
                    <li><a href="#" class="rounded hover:text-white focus-visible:outline-2 focus-visible:outline-white">{{ __('storefront.legal.privacy') }}</a></li>
                    <li aria-hidden="true">·</li>
                    <li><a href="#" class="rounded hover:text-white focus-visible:outline-2 focus-visible:outline-white">{{ __('storefront.legal.terms') }}</a></li>
                    <li aria-hidden="true">·</li>
                    <li><a href="{{ route('admin.login') }}" class="rounded hover:text-white focus-visible:outline-2 focus-visible:outline-white">{{ __('storefront.legal.staff') }}</a></li>
                </ul>
            </nav>
        </div>
    </div>
</footer>
