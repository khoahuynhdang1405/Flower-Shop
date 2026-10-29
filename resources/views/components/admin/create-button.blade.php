@props(['href' => '#'])

{{-- Nút hành động chính ở đầu trang admin ("Thêm bộ sưu tập +"). --}}
<a href="{{ $href }}"
    {{ $attributes->class('inline-flex h-12 items-center gap-4 rounded bg-lh-primary px-6 text-[15px] font-medium text-white transition-colors hover:bg-lh-primary-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary') }}>
    {{ $slot }}
    <x-lucide-plus class="size-5 stroke-[1.5]" aria-hidden="true" />
</a>
