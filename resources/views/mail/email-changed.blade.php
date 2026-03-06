@props([
'user' => null,
'mailChange' => null,
'locale' => app()->getLocale()
])
@php
    use App\Models\MailChange;
    use App\Models\User;
    use App\Helpers\Lang;
    /**
        * @var User $user
        * @var MailChange $mailChange
        */
@endphp
<x-mail::message>
# {{ Lang::trans(key: 'mail.hello', replace: ['username' => $user->username], locale: $locale) }}


{!! Lang::trans(key: 'mail.email_changed.body1', replace: ['new_email' => $mailChange->new_email], locale: $locale) !!}


{!! Lang::trans(key: 'mail.email_changed.body2', replace: ['reference' => $mailChange->id], locale: $locale) !!}


{{ Lang::trans(key: 'mail.email_changed.body3',  locale: $locale) }}


{{ Lang::trans(key: 'mail.signature',  locale: $locale) }}
</x-mail::message>
