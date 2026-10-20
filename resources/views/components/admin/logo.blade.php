<a href="{{ route('admin.dashboard') }}"
    class="relative grid size-17 place-items-center rounded-full bg-linear-to-b from-lh-logo-from to-lh-logo-to text-lh-heading shadow-lh-logo outline-offset-4 focus-visible:outline-2 focus-visible:outline-white">
    {{-- Viền nét đứt vẽ bằng SVG để giữ đúng độ dài nét như Figma (border: dashed không chỉnh được). --}}
    <svg class="pointer-events-none absolute inset-0 size-full text-lh-accent-soft" viewBox="0 0 68 68" fill="none" aria-hidden="true">
        <circle cx="34" cy="34" r="33.5" stroke="currentColor" stroke-width="1" pathLength="100" stroke-dasharray="2.5 1.5" />
    </svg>
    <span class="flex flex-col items-center gap-1">
        <x-brand.flower-mark accent class="h-[23px] w-auto" />
        <span class="text-[10.5px] leading-none">{{ __('admin.brand') }}</span>
    </span>
</a>
