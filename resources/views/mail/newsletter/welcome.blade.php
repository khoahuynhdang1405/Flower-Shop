@php
    /**
     * @var \App\Models\Subscriber $subscriber
     *
     * Markdown: không thụt dòng nội dung.
     */
@endphp
<x-mail::message>
# {{ __('newsletter.mail.title') }}

{{ __('newsletter.mail.intro') }}

{{ __('newsletter.mail.gift') }}

<x-mail::panel>
**{{ __('newsletter.mail.code', ['code' => $subscriber->coupon_given]) }}**<br>
{{ __('newsletter.mail.conditions') }}
</x-mail::panel>

{{ __('newsletter.mail.how_to') }}

<x-mail::button :url="route('flowers.index')">
{{ __('newsletter.mail.shop') }}
</x-mail::button>

{{ __('newsletter.mail.signature') }}<br>
{{ config('app.name') }}

<x-mail::subcopy>
{{ __('newsletter.mail.unsubscribe') }}
</x-mail::subcopy>
</x-mail::message>
