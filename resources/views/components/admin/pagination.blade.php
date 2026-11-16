@props(['paginator'])

@php
    /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator */
    $currentPage = $paginator->currentPage();
    $lastPage = $paginator->lastPage();
    $pages = collect(range(1, $lastPage))
        ->filter(fn (int $page): bool => $page <= 3 || $page === $lastPage || abs($page - $currentPage) <= 1)
        ->values();
    $pageButton = 'grid size-8 place-items-center rounded-full text-sm tabular-nums transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary';
@endphp

{{-- Phân trang danh sách admin: ‹ 1 2 3 … › (trang hiện tại nền sage như thiết kế). --}}
@if ($lastPage > 1)
    <nav aria-label="{{ __('flowers.pagination.label') }}" {{ $attributes }}>
        <ul class="flex items-center gap-1" role="list">
            <li>
                @if ($currentPage > 1)
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $pageButton }} hover:bg-lh-sage-50">
                        <x-lucide-chevron-left class="size-4 stroke-[1.5]" aria-hidden="true" />
                        <span class="sr-only">{{ __('flowers.pagination.previous') }}</span>
                    </a>
                @else
                    <span class="{{ $pageButton }} text-lh-border" aria-hidden="true"><x-lucide-chevron-left class="size-4 stroke-[1.5]" /></span>
                @endif
            </li>

            @foreach ($pages as $page)
                @if ($loop->index > 0 && $page - $pages[$loop->index - 1] > 1)
                    <li aria-hidden="true"><span class="{{ $pageButton }} text-lh-muted">…</span></li>
                @endif
                <li>
                    <a href="{{ $paginator->url($page) }}" aria-label="{{ __('flowers.pagination.page', ['page' => $page]) }}"
                        @if ($page === $currentPage) aria-current="page" @endif
                        class="{{ $pageButton }} hover:bg-lh-sage-50 aria-[current=page]:bg-lh-sage-100 aria-[current=page]:font-medium">
                        {{ $page }}
                    </a>
                </li>
            @endforeach

            <li>
                @if ($currentPage < $lastPage)
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $pageButton }} hover:bg-lh-sage-50">
                        <x-lucide-chevron-right class="size-4 stroke-[1.5]" aria-hidden="true" />
                        <span class="sr-only">{{ __('flowers.pagination.next') }}</span>
                    </a>
                @else
                    <span class="{{ $pageButton }} text-lh-border" aria-hidden="true"><x-lucide-chevron-right class="size-4 stroke-[1.5]" /></span>
                @endif
            </li>
        </ul>
    </nav>
@endif
