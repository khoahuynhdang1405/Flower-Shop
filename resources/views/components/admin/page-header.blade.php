@props(['title', 'description' => null])

<div {{ $attributes->class('flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between') }}>
    <div class="min-w-0">
        <h1 class="font-serif text-3xl leading-tight font-normal [overflow-wrap:anywhere] text-lh-heading sm:text-4xl">{{ $title }}</h1>
        @if ($description)
            <p class="mt-2 text-sm text-lh-muted">{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex shrink-0 flex-wrap items-center gap-3">
            {{ $actions }}
        </div>
    @endisset
</div>
