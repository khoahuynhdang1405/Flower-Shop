<x-layouts.storefront :title="__('auth.login.title')">
    <x-auth.split :eyebrow="__('auth.login.eyebrow')" :title="__('auth.login.title')" :subtitle="__('auth.login.subtitle')">
        <form method="POST" action="{{ route('login.store') }}" novalidate data-busy-form class="flex flex-col gap-6">
            @csrf

            <x-form.input name="email" type="email" :label="__('auth.login.email')"
                :placeholder="__('auth.login.email_placeholder')" autocomplete="email" required autofocus />

            <x-form.password name="password" :label="__('auth.login.password')"
                :placeholder="__('auth.login.password_placeholder')" autocomplete="current-password" required />

            <div class="flex flex-wrap items-center justify-between gap-3">
                <x-form.checkbox name="remember" :checked="true">{{ __('auth.login.remember') }}</x-form.checkbox>
                {{-- TODO: luồng quên mật khẩu (chưa có thiết kế; bảng password_reset_tokens đã sẵn sàng). --}}
                <a href="#" class="rounded text-sm underline underline-offset-4 hover:text-lh-primary focus-visible:outline-2 focus-visible:outline-lh-primary">
                    {{ __('auth.login.forgot') }}
                </a>
            </div>

            <x-auth.submit-button>{{ __('auth.login.submit') }}</x-auth.submit-button>

            <x-auth.google-button />

            <p class="text-center text-sm">
                {{ __('auth.login.no_account') }}
                <a href="{{ route('register') }}" class="ml-1 rounded font-medium underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-lh-primary">
                    {{ __('auth.login.register_link') }}
                </a>
            </p>

            <p class="flex items-center justify-center gap-2 text-xs text-lh-muted">
                <x-lucide-lock-keyhole class="size-4 stroke-[1.5] text-lh-heading" aria-hidden="true" />
                {{ __('auth.login.privacy') }}
            </p>
        </form>

        <x-slot:aside>
            <x-auth.arch-image name="anh-hoa-dang-nhap" :alt="__('auth.login.image_alt')" />
            <p class="mt-8 font-serif text-[26px] leading-snug italic">{{ __('auth.login.quote') }}</p>
            <p class="mt-5 max-w-[500px] text-sm leading-relaxed text-lh-muted">{{ __('auth.login.quote_body') }}</p>
        </x-slot:aside>
    </x-auth.split>
</x-layouts.storefront>
