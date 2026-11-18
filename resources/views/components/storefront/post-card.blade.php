@props([
    /** @var \App\Models\Post */
    'post',
    // Thẻ to cho bài mới nhất đầu trang Góc nhỏ.
    'featured' => false,
    'headingLevel' => 'h3',
])

@php
    $link = route('posts.show', $post->slug);
@endphp

{{-- Thẻ bài Góc nhỏ: ảnh bìa, nhãn + thời gian đọc, tiêu đề Lora, "Đọc câu chuyện →". --}}
<article {{ $attributes->class(['group', 'grid gap-8 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)] lg:items-center lg:gap-14' => $featured, 'flex flex-col' => ! $featured]) }}>
    <a href="{{ $link }}" tabindex="-1" aria-hidden="true" class="block overflow-hidden rounded-sm">
        @if ($post->thumbnail_url)
            <img src="{{ $post->thumbnail_url }}" alt="" loading="lazy"
                class="{{ $featured ? 'aspect-[16/10]' : 'aspect-[4/3]' }} w-full bg-lh-sage-100 object-cover transition-transform duration-500 group-hover:scale-[1.03] motion-reduce:transition-none">
        @else
            <x-flower-image :name="'goc-nho-'.$post->slug" class="{{ $featured ? 'aspect-[16/10]' : 'aspect-[4/3]' }}" />
        @endif
    </a>

    <div @class(['mt-5' => ! $featured])>
        <p class="flex items-center justify-between gap-3 text-[11px] text-lh-muted">
            <span class="font-semibold tracking-wide text-lh-primary uppercase">{{ __('posts.label') }}</span>
            <span>{{ trans_choice('posts.reading_time', $post->reading_minutes, ['minutes' => $post->reading_minutes]) }}</span>
        </p>
        <{{ $headingLevel }} @class(['mt-3 font-serif leading-snug', 'text-3xl sm:text-[34px]' => $featured, 'text-xl' => ! $featured])>
            <a href="{{ $link }}" class="rounded transition-colors hover:text-lh-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
                {{ $post->title }}
            </a>
        </{{ $headingLevel }}>
        @if ($featured && $post->summary)
            <p class="mt-4 text-[15px] leading-relaxed text-lh-muted">{{ $post->summary }}</p>
        @endif
        <p class="mt-4 inline-flex items-center gap-1.5 text-xs font-medium text-lh-heading" aria-hidden="true">
            {{ __('posts.read_more') }}
            <x-lucide-arrow-right class="size-3.5 stroke-[1.5] transition-transform group-hover:translate-x-0.5 motion-reduce:transition-none" />
        </p>
    </div>
</article>
