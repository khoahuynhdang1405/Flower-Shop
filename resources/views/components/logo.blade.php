@props(['href' => url('/')])

{{--
    Logo tròn Lá & Hoa dùng cho mọi trang (docs/designs/storefront/14-logo.png).
    Viền focus đôi (vòng trắng + viền xanh) để luôn thấy rõ trên cả nền tối lẫn nền kem.
--}}
<a href="{{ $href }}" aria-label="{{ __('storefront.brand') }}"
    {{ $attributes->class('relative grid size-17 shrink-0 place-items-center rounded-full bg-linear-to-b from-lh-logo-from to-lh-logo-to text-lh-heading shadow-lh-logo focus-visible:outline-2 focus-visible:outline-offset-[5px] focus-visible:outline-lh-primary focus-visible:ring-4 focus-visible:ring-white') }}>
    {{-- Viền nét đứt vẽ bằng SVG để giữ đúng độ dài nét như Figma (border: dashed không chỉnh được). --}}
    <svg class="pointer-events-none absolute inset-0 size-full text-lh-accent-soft" viewBox="0 0 68 68" fill="none" aria-hidden="true">
        <circle cx="34" cy="34" r="33.5" stroke="currentColor" stroke-width="1" pathLength="100" stroke-dasharray="2.5 1.5" />
    </svg>
    <span class="flex flex-col items-center gap-1" aria-hidden="true">
        <x-brand.flower-mark accent class="h-[23px] w-auto" />
        <span class="text-[10.5px] leading-none">{{ __('storefront.brand') }}</span>
    </span>
</a>
