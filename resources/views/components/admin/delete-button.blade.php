@props(['action', 'confirm', 'label'])

{{-- Nút xóa: hỏi lại trước khi gửi (resources/js/forms.js, [data-confirm]). --}}
<form method="POST" action="{{ $action }}" data-confirm="{{ $confirm }}" class="inline">
    @csrf
    @method('DELETE')
    <button type="submit" aria-label="{{ $label }}"
        {{ $attributes->class('inline-flex items-center gap-2 rounded text-sm whitespace-nowrap text-lh-muted transition-colors hover:text-lh-danger focus-visible:outline-2 focus-visible:outline-lh-primary') }}>
        <x-lucide-trash-2 class="size-[18px] stroke-[1.5]" aria-hidden="true" />
        {{ __('admin.list.delete') }}
    </button>
</form>
