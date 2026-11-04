{{-- Khung vòm (arch) dùng chung: đỉnh bo tròn hoàn toàn, đáy bo nhẹ. Tỉ lệ khung truyền qua class, vd. aspect-[520/480]. --}}
<div {{ $attributes->class('overflow-hidden rounded-t-full rounded-b-sm bg-lh-sage-100') }}>
    {{ $slot }}
</div>
