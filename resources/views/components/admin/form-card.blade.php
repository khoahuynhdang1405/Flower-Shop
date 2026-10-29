@props([
    'action',
    // POST (thêm) hoặc PUT (sửa).
    'method' => 'POST',
    'cancel',
    'submit',
])

{{-- Khung form Thêm/Sửa trong admin: thẻ trắng, nút Lưu + Hủy ở cuối. --}}
<form method="POST" action="{{ $action }}" novalidate data-busy-form
    {{ $attributes->class('mt-8 rounded-md border border-lh-border bg-lh-surface') }}>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid gap-6 px-6 py-8 md:grid-cols-2 sm:px-8">
        {{ $slot }}
    </div>

    <div class="flex flex-col-reverse gap-3 border-t border-lh-border px-6 py-5 sm:flex-row sm:justify-end sm:px-8">
        <a href="{{ $cancel }}"
            class="inline-flex h-12 items-center justify-center rounded border border-lh-border px-6 text-[15px] transition-colors hover:bg-lh-canvas focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
            {{ __('admin.list.cancel') }}
        </a>
        <button type="submit" data-busy-label="{{ __('auth.busy') }}"
            class="inline-flex h-12 items-center justify-center rounded bg-lh-primary px-6 text-[15px] font-medium text-white transition-colors hover:bg-lh-primary-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary disabled:cursor-wait disabled:opacity-80">
            <span data-button-label>{{ $submit }}</span>
        </button>
    </div>
</form>
