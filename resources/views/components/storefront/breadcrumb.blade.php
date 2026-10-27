@props([
    // Danh sách [label, url]; mục cuối là trang hiện tại (url bỏ qua).
    'items' => [],
])

<nav aria-label="{{ __('storefront.nav.breadcrumb') }}" {{ $attributes }}>
    <ol class="flex flex-wrap items-center gap-1.5 text-xs text-lh-muted">
        @foreach ($items as [$label, $url])
            @if ($loop->last)
                <li><span aria-current="page">{{ $label }}</span></li>
            @else
                <li><a href="{{ $url }}" class="rounded hover:text-lh-heading focus-visible:outline-2 focus-visible:outline-lh-primary">{{ $label }}</a></li>
                <li aria-hidden="true">/</li>
            @endif
        @endforeach
    </ol>
</nav>
