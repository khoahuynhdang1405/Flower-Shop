@php
    $legalLink = fn (string $label): string => '<a href="#" class="rounded underline underline-offset-4 hover:text-lh-primary focus-visible:outline-2 focus-visible:outline-lh-primary">'.e($label).'</a>';
@endphp

<x-layouts.storefront :title="__('auth.register.title')" compact>
    <x-auth.split :eyebrow="__('auth.register.eyebrow')" :title="__('auth.register.title')" :subtitle="__('auth.register.subtitle')">
        <form method="POST" action="{{ route('register.store') }}" novalidate data-busy-form class="flex flex-col gap-6">
            @csrf

            <x-form.input name="name" :label="__('auth.register.name')" marker
                :placeholder="__('auth.register.name_placeholder')" autocomplete="name" required autofocus />

            <x-form.input name="email" type="email" :label="__('auth.register.email')" marker
                :placeholder="__('auth.register.email_placeholder')" autocomplete="email" required />

            <x-form.input name="phone" type="tel" :label="__('auth.register.phone')" marker
                :placeholder="__('auth.register.phone_placeholder')" autocomplete="tel" inputmode="tel" required />

            <x-form.password name="password" :label="__('auth.register.password')" marker
                :hint="__('auth.register.password_hint')" :placeholder="__('auth.register.password_placeholder')"
                autocomplete="new-password" required />

            <x-form.password name="password_confirmation" :label="__('auth.register.password_confirmation')" marker
                :placeholder="__('auth.register.password_confirmation_placeholder')" autocomplete="new-password" required />

            <div class="flex flex-col gap-3">
                <x-form.checkbox name="terms" required>
                    {{-- TODO: trỏ tới trang điều khoản / chính sách khi có. --}}
                    {!! __('auth.register.terms', [
                        'terms' => $legalLink(__('storefront.legal.terms')),
                        'privacy' => $legalLink(__('storefront.legal.privacy')),
                    ]) !!}
                </x-form.checkbox>
                <x-form.checkbox name="newsletter">{{ __('auth.register.newsletter') }}</x-form.checkbox>
            </div>

            <x-auth.submit-button>{{ __('auth.register.submit') }}</x-auth.submit-button>

            <x-auth.google-button />

            <p class="text-center text-sm">
                {{ __('auth.register.has_account') }}
                <a href="{{ route('login') }}" class="ml-1 rounded font-medium underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-lh-primary">
                    {{ __('auth.register.login_link') }}
                </a>
            </p>
        </form>

        <x-slot:aside>
            <x-auth.arch-image name="anh-hoa-dang-ki" :alt="__('auth.register.image_alt')" />
            <p class="mt-10 font-serif text-[33px] leading-tight">
                {{ __('auth.register.heading_line_1') }}<br>{{ __('auth.register.heading_line_2') }}
            </p>
            <p class="mt-6 max-w-[500px] text-sm leading-relaxed text-lh-muted">{{ __('auth.register.body') }}</p>

            <div class="mt-10 flex items-start gap-4 rounded bg-lh-sage-50 px-6 py-6">
                <x-lucide-gift class="size-7 shrink-0 stroke-[1.5]" aria-hidden="true" />
                <div>
                    <p class="text-[15px] font-medium">{{ __('auth.register.promo_title') }}</p>
                    <p class="mt-1.5 text-xs text-lh-muted">{{ __('auth.register.promo_detail') }}</p>
                </div>
            </div>
        </x-slot:aside>
    </x-auth.split>
</x-layouts.storefront>
