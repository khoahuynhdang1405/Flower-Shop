@php
    /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, \App\Models\Post> $posts */
    $container = 'mx-auto max-w-[1440px] px-4 sm:px-8 lg:px-20';
    $featured = $posts->onFirstPage() ? $posts->first() : null;
    $others = $featured ? $posts->slice(1) : $posts->items();
@endphp

<x-layouts.storefront :title="__('posts.title')">
    <div class="{{ $container }} py-5">
        <x-storefront.breadcrumb :items="[[__('storefront.menu.home'), url('/')], [__('posts.title'), null]]" />
    </div>

    <div class="{{ $container }} pt-4 pb-20">
        <header class="max-w-[640px]">
            <p class="text-[11px] font-semibold tracking-wide text-lh-primary uppercase">{{ __('posts.index.eyebrow') }}</p>
            <h1 class="mt-3 font-serif text-[40px] leading-tight sm:text-5xl">{{ __('posts.index.title') }}</h1>
            <p class="mt-4 text-[15px] leading-relaxed text-lh-muted">{{ __('posts.index.description') }}</p>
        </header>

        @if ($posts->isEmpty())
            <div class="mt-12 rounded-sm border border-dashed border-lh-border px-6 py-16 text-center">
                <p class="font-serif text-2xl">{{ __('posts.index.empty_title') }}</p>
                <p class="mt-2 text-sm text-lh-muted">{{ __('posts.index.empty_body') }}</p>
            </div>
        @else
            @if ($featured)
                <section aria-label="{{ __('posts.index.latest') }}" class="mt-12 border-b border-lh-border pb-14">
                    <x-storefront.post-card :post="$featured" featured heading-level="h2" />
                </section>
            @endif

            @if (count($others) > 0)
                <ul class="mt-14 grid gap-x-8 gap-y-14 sm:grid-cols-2 lg:grid-cols-3" role="list">
                    @foreach ($others as $post)
                        <li><x-storefront.post-card :post="$post" heading-level="h2" /></li>
                    @endforeach
                </ul>
            @endif

            <x-admin.pagination :paginator="$posts" class="mt-14 flex justify-center" />
        @endif
    </div>
</x-layouts.storefront>
