{{-- CAUTION! This template uses markdown! Never indent the lines below or you will break the layout --}}
{{-- Also never use __ or @lang, because mails handle translation very differently. Always use App\Helpers\Lang::trans() --}}
@props([
'user',
'locale' => app()->getLocale(),
])
@php
    use App\Helpers\Lang;
@endphp
<x-mail::message heading="{{ Lang::trans('mail.account_deletion_notification_two_weeks_before.subject') }}">
{{ Lang::trans('mail.hello', ['username' => $user->username]) }},<br/>
<br/>
{{ Lang::trans('mail.account_deletion_notification_two_weeks_before.body1', locale: $locale) }}<br/>
{{ Lang::trans('mail.account_deletion_notification_two_weeks_before.body2', locale: $locale) }}<br/>
<br/>
{{ Lang::trans('mail.account_deletion_notification_two_weeks_before.body3', locale: $locale) }}<br/>
<br/>
{{ Lang::trans('mail.bye', locale: $locale) }}<br/>
{{ Lang::trans('mail.signature', locale: $locale) }}<br/>
</x-mail::message>
