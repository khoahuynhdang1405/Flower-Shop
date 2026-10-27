@props(['id', 'title', 'description' => null])
<section id="{{ $id }}" aria-labelledby="{{ $id }}-title"
    {{ $attributes->class('scroll-mt-6 overflow-hidden rounded-md border border-lh-border bg-lh-surface') }}>
    <div class="px-5 pt-6 sm:px-7 sm:pt-7">
        <div class="flex items-start justify-between gap-4">
            <h2 id="{{ $id }}-title" class="font-serif text-[26px] leading-tight font-normal sm:text-[28px]">{{ $title }}</h2>
            {{ $badge ?? '' }}
        </div>
        @if ($description)
            <p class="mt-3 text-[13px] text-lh-muted">{{ $description }}</p>
        @endif
    </div>

    {{ $slot }}
</section>
