@props([
    'title',
    // TODO: thay bằng số đơn chờ xử lý thật khi có query đơn hàng.
    'orderCount' => 8,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="lahoa" class="bg-lh-canvas">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · {{ __('admin.brand') }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-dvh bg-lh-canvas font-sans text-lh-heading antialiased" data-admin-shell>
    <a href="#main-content"
        class="sr-only z-50 rounded bg-lh-primary px-4 py-2 text-sm text-white focus:not-sr-only focus:fixed focus:top-3 focus:left-3">
        {{ __('admin.skip_to_content') }}
    </a>

    <x-admin.sidebar :order-count="$orderCount" />

    <div class="flex min-h-dvh flex-col lg:pl-56">
        <x-admin.header :fallback-page="$title" />

        <main id="main-content" tabindex="-1" class="flex-1 px-4 pt-8 pb-10 focus:outline-none sm:px-6 lg:px-8 lg:pt-10">
            @isset($pageHeader)
                {{ $pageHeader }}
            @endisset

            {{ $slot }}
        </main>

        <x-admin.footer />
    </div>
</body>

</html>
