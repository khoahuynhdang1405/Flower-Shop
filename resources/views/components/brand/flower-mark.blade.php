@props(['accent' => false])

{{--
    Biểu tượng bông hoa của Lá & Hoa — dựng theo vector Figma (khung đường tâm 15 × 21.67, nét 1.5).
    accent = true: nhụy màu hổ phách như logo; false: một màu (currentColor) cho icon menu.
--}}
<svg {{ $attributes }} viewBox="-0.75 -0.75 16.5 23.2" fill="none" stroke="currentColor" stroke-width="1.5"
    stroke-linejoin="miter" aria-hidden="true" focusable="false">
    {{-- Bốn cánh tiếp xúc nhau trên hai trục đi qua nhụy --}}
    <circle cx="4.25" cy="3.25" r="3.25" />
    <circle cx="10.75" cy="3.25" r="3.25" />
    <circle cx="4.25" cy="9.75" r="3.25" />
    <circle cx="10.75" cy="9.75" r="3.25" />
    {{-- Cuống --}}
    <path d="M7.5 8.65V21.67" />
    {{-- Lá trái, lá phải --}}
    <path d="M0 16.23C4 16.23 7.5 18.3 7.5 21.67C2.8 21.67 0 19.3 0 16.23Z" />
    <path d="M15 16.23C11 16.23 7.5 18.3 7.5 21.67C12.2 21.67 15 19.3 15 16.23Z" />
    {{-- Nhụy --}}
    <circle cx="7.5" cy="6.5" r="2.15" @class(['fill-lh-flower-center' => $accent, 'fill-none' => ! $accent]) />
</svg>
