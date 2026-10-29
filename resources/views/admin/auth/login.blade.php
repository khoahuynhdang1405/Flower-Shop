<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="lahoa" class="bg-lh-canvas">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('admin.auth.title') }} · {{ __('admin.brand') }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

{{-- Đăng nhập quản trị (guard admin).--}}
<body class="flex min-h-dvh flex-col bg-lh-canvas font-sans text-lh-heading antialiased">
    <div class="flex justify-end px-4 pt-4 sm:px-8">
        <x-locale-switcher />
    </div>

    <main class="flex flex-1 items-center justify-center px-4 py-10">
        <div class="w-full max-w-[420px] rounded-md border border-lh-border bg-lh-surface px-6 py-10 sm:px-10">
            <div class="flex flex-col items-center text-center">
                <x-logo :href="url('/')" />
                <p class="mt-4 text-[11px] tracking-wide text-lh-muted uppercase">{{ __('admin.workspace') }}</p>
                <h1 class="mt-6 font-serif text-3xl font-normal">{{ __('admin.auth.title') }}</h1>
                <p class="mt-2 text-sm text-lh-muted">{{ __('admin.auth.subtitle') }}</p>
            </div>

            <form method="POST" action="{{ route('admin.login.store') }}" novalidate data-busy-form class="mt-8 flex flex-col gap-6">
                @csrf

                <x-form.input name="email" type="email" :label="__('auth.login.email')" autocomplete="username" required autofocus />
                <x-form.password name="password" :label="__('auth.login.password')" autocomplete="current-password" required />
                <x-form.checkbox name="remember">{{ __('auth.login.remember') }}</x-form.checkbox>

                <x-auth.submit-button>{{ __('auth.login.submit') }}</x-auth.submit-button>
            </form>
        </div>
    </main>

    <x-admin.footer />
</body>

</html>
