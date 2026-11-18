@php
    /**
     * @var \App\Models\Post $post
     * @var \App\DTOs\Post\RenderedPostContent $content
     * @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Post> $related
     */
    $container = 'mx-auto max-w-[1440px] px-4 sm:px-8 lg:px-20';
    $url = route('posts.show', $post->slug);
    $iconLink = 'grid size-8 place-items-center rounded-full text-lh-heading transition-colors hover:bg-lh-sage-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary';
@endphp

<x-layouts.storefront :title="$post->title">
    <div class="{{ $container }} py-5">
        <x-storefront.breadcrumb :items="[[__('storefront.menu.home'), url('/')], [__('posts.title'), route('posts.index')], [$post->title, null]]" />
    </div>

    <article aria-labelledby="post-title">
        <header class="{{ $container }} pt-6 text-center">
            <div class="mx-auto max-w-[720px]">
                <p class="text-[11px] font-semibold tracking-wide text-lh-primary uppercase">{{ __('posts.label') }}</p>
                <h1 id="post-title" class="mt-4 font-serif text-[38px] leading-tight sm:text-5xl">{{ $post->title }}</h1>
                @if ($post->summary)
                    <p class="mt-5 text-[15px] leading-relaxed text-lh-muted">{{ $post->summary }}</p>
                @endif
                <p class="mt-5 flex flex-wrap items-center justify-center gap-x-2 gap-y-1 text-xs text-lh-muted">
                    <span class="text-lh-heading">{{ __('posts.by', ['name' => $post->author->name]) }} · {{ __('posts.author_role') }}</span>
                    <span aria-hidden="true">·</span>
                    <time datetime="{{ $post->created_at->toDateString() }}">{{ $post->created_at->format('d/m/Y') }}</time>
                    <span aria-hidden="true">·</span>
                    <span>{{ trans_choice('posts.reading_time', $post->reading_minutes, ['minutes' => $post->reading_minutes]) }}</span>
                </p>
            </div>
        </header>

        @if ($post->thumbnail_url)
            <div class="{{ $container }} mt-10">
                <img src="{{ $post->thumbnail_url }}" alt="{{ __('posts.cover_alt', ['title' => $post->title]) }}" fetchpriority="high"
                    class="aspect-[16/9] w-full rounded-sm bg-lh-sage-100 object-cover sm:aspect-[21/9]">
            </div>
        @endif

        <div class="{{ $container }} mt-12 grid gap-12 pb-20 lg:grid-cols-[minmax(0,1fr)_300px] lg:gap-20">
            <div class="min-w-0 max-w-[720px]">
                <div class="post-content">{!! $content->html !!}</div>

                <footer class="mt-14 flex flex-wrap items-center justify-end gap-3 border-t border-lh-border pt-6 text-xs text-lh-muted">
                    <span>{{ __('posts.show.share') }}</span>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($url) }}" target="_blank" rel="noopener noreferrer" class="{{ $iconLink }}">
                        <x-lucide-facebook class="size-4 stroke-[1.5]" aria-hidden="true" />
                        <span class="sr-only">{{ __('posts.show.share_facebook') }}</span>
                    </a>
                    <button type="button" class="{{ $iconLink }}" data-copy-link="{{ $url }}" data-copied-label="{{ __('posts.show.copied') }}">
                        <x-lucide-link class="size-4 stroke-[1.5]" aria-hidden="true" />
                        <span class="sr-only">{{ __('posts.show.copy_link') }}</span>
                    </button>
                    <span data-copy-link-status class="sr-only" aria-live="polite"></span>
                </footer>
            </div>

            <aside class="flex flex-col gap-6 lg:sticky lg:top-8 lg:self-start">
                @if ($content->headings !== [])
                    <nav aria-labelledby="toc-title" class="rounded-sm bg-lh-sage-50 px-6 py-6">
                        <h2 id="toc-title" class="text-[11px] font-semibold tracking-wide text-lh-primary uppercase">{{ __('posts.show.toc') }}</h2>
                        <ol class="mt-4 flex flex-col gap-3 text-[13px]" role="list">
                            @foreach ($content->headings as $heading)
                                <li>
                                    <a href="#{{ $heading['id'] }}" class="rounded text-lh-heading transition-colors hover:text-lh-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
                                        {{ $heading['title'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ol>
                    </nav>
                @endif

                <section aria-labelledby="help-title" class="rounded-sm border border-lh-border px-6 py-6">
                    <x-brand.flower-mark class="h-6 w-auto text-lh-primary" />
                    <h2 id="help-title" class="mt-4 font-serif text-xl">{{ __('posts.show.help_title') }}</h2>
                    <p class="mt-3 text-xs leading-relaxed text-lh-muted">{{ __('posts.show.help_body') }}</p>
                    <a href="tel:{{ config('shop.hotline') }}" class="mt-4 inline-block rounded text-sm font-medium text-lh-heading focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
                        {{ config('shop.hotline') }}
                    </a>
                    <p class="mt-1 text-xs text-lh-muted">{{ __('posts.show.help_hours', ['hours' => config('shop.opening_hours')]) }}</p>
                </section>
            </aside>
        </div>
    </article>

    @if ($related->isNotEmpty())
        <section aria-labelledby="related-title" class="bg-lh-canvas">
            <div class="{{ $container }} py-16 lg:py-20">
                <x-storefront.section-heading id="related-title" :eyebrow="__('posts.show.more_eyebrow')" :title="__('posts.show.more_title')">
                    <x-slot:aside>
                        <a href="{{ route('posts.index') }}"
                            class="inline-flex items-center gap-2 rounded text-sm text-lh-heading underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
                            {{ __('posts.show.back') }}
                            <x-lucide-arrow-right class="size-4 stroke-[1.5]" aria-hidden="true" />
                        </a>
                    </x-slot:aside>
                </x-storefront.section-heading>

                <ul class="mt-10 grid gap-x-8 gap-y-12 sm:grid-cols-2 lg:grid-cols-3" role="list">
                    @foreach ($related as $relatedPost)
                        <li><x-storefront.post-card :post="$relatedPost" /></li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif
</x-layouts.storefront>
