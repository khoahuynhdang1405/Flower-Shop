@props([
    'title',
    // Trang đăng nhập/đăng ký: header gọn (không tìm kiếm, giỏ hàng) + footer rút gọn.
    'compact' => false,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="lahoa" class="bg-lh-cream">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · {{ __('storefront.brand') }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="flex min-h-dvh flex-col bg-lh-cream font-sans text-lh-heading antialiased">
    <a href="#main-content"
        class="sr-only z-50 rounded bg-lh-primary px-4 py-2 text-sm text-white focus:not-sr-only focus:fixed focus:top-3 focus:left-3">
        {{ __('storefront.skip_to_content') }}
    </a>

    <x-storefront.top-bar />
    <x-storefront.header :compact="$compact" />

    <main id="main-content" tabindex="-1" class="flex-1 focus:outline-none">
        {{ $slot }}
    </main>

    @if ($compact)
        <x-storefront.footer-compact />
    @else
        <x-storefront.footer />
    @endif
</body>

</html>
