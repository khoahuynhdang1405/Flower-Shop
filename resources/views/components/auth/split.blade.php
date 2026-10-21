@props(['eyebrow', 'title', 'subtitle'])

{{--
    Bố cục hai cột của trang đăng nhập/đăng ký: ảnh vòm + lời nhắn (slot aside) bên trái,
    form bên phải. Trên màn hình nhỏ form lên trước (đúng thứ tự đọc), ảnh xuống sau.
--}}
<div class="mx-auto grid max-w-[1200px] gap-12 px-4 py-10 sm:px-8 lg:grid-cols-[minmax(0,520px)_minmax(0,1fr)] lg:gap-x-[120px] lg:py-[70px]">
    <section class="lg:order-2" aria-labelledby="page-title">
        <div class="max-w-[440px] max-lg:mx-auto">
            <p class="text-[11px] font-semibold tracking-wide text-lh-primary uppercase">{{ $eyebrow }}</p>
            <h1 id="page-title" class="mt-3 font-serif text-[40px] leading-tight font-normal sm:text-5xl">{{ $title }}</h1>
            <p class="mt-4 text-[15px] text-lh-muted">{{ $subtitle }}</p>

            @if (session('auth_error'))
                <div role="alert" class="mt-6 flex items-start gap-2 rounded bg-lh-warning-bg px-4 py-3 text-sm text-lh-warning-fg">
                    <x-lucide-circle-alert class="mt-0.5 size-4 shrink-0 stroke-[1.5]" aria-hidden="true" />
                    {{ session('auth_error') }}
                </div>
            @endif

            <div class="mt-8">
                {{ $slot }}
            </div>
        </div>
    </section>

    <aside class="lg:order-1">
        {{ $aside }}
    </aside>
</div>
