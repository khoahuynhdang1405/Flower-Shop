{{-- Thông báo sau khi thêm / sửa / xóa (session 'status' hoặc 'error'). --}}
@if (session('status'))
    <p role="status" {{ $attributes->class('flex items-start gap-2 rounded-md bg-lh-sage-50 px-5 py-4 text-sm') }}>
        <x-lucide-circle-check class="mt-px size-4 shrink-0 stroke-[1.5]" aria-hidden="true" />
        {{ session('status') }}
    </p>
@endif

@if (session('error'))
    <p role="alert" {{ $attributes->class('flex items-start gap-2 rounded-md bg-lh-warning-bg px-5 py-4 text-sm text-lh-amber') }}>
        <x-lucide-circle-alert class="mt-px size-4 shrink-0 stroke-[1.5]" aria-hidden="true" />
        {{ session('error') }}
    </p>
@endif
