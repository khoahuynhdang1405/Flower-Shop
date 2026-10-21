<div class="flex items-center gap-4 text-xs text-lh-muted" role="separator" aria-label="{{ __('auth.or') }}">
    <span class="h-px flex-1 bg-lh-border"></span>
    <span aria-hidden="true">{{ __('auth.or') }}</span>
    <span class="h-px flex-1 bg-lh-border"></span>
</div>

<a href="{{ route('auth.google.redirect') }}"
    class="inline-flex h-12 w-full items-center justify-center rounded border border-lh-heading px-6 text-[15px] font-medium text-lh-heading transition-colors hover:bg-lh-surface focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
    {{ __('auth.google_button') }}
</a>
