{{-- CAUTION! This template uses markdown! Never indent the lines below or you will break the layout --}}
{{-- Also never use __ or @lang, because mails handle translation very differently. Always use App\Helpers\Lang::trans() --}}
@props([
'level' => 'success',
'greeting' => null,
'introLines' => [],
'outroLines' => [],
'actionText' => null,
'actionUrl' => null,
'displayableActionUrl' => null,
'locale' => app()->getLocale(),
])
@php
    use App\Helpers\Lang;
@endphp
<x-mail::message>
{{-- Greeting --}}
@if (! empty($greeting))
# {{ $greeting }}
@else
@if ($level === 'error')
# {{ Lang::trans('mail.whoops', locale: $locale) }}
@else
# {{ Lang::trans('mail.hello2', locale: $locale) }}
@endif
@endif

{{-- Intro Lines --}}
@foreach ($introLines as $line)
{{ $line }}

@endforeach


{{-- Action Button --}}
@isset($actionText)
<?php
    $color = match ($level) {
        'success', 'error' => $level,
        default => 'primary',
    };
?>
<x-mail::button :url="$actionUrl" :color="$color">
{{ $actionText }}
</x-mail::button>
@endisset

{{-- Outro Lines --}}
@foreach ($outroLines as $line)
{{ $line }}

@endforeach

{{-- Salutation --}}
@if (! empty($salutation))
{{ $salutation }}
@else
{{ Lang::trans('mail.bye', locale: $locale) }}<br>
{{ Lang::trans('mail.signature', locale: $locale) }}
@endif

{{-- Subcopy --}}
@isset($actionText)
<x-slot:subcopy>
{{ Lang::trans('mail.action.trouble', ['actionText' => $actionText], locale: $locale) }} <span class="break-all">[{{ $displayableActionUrl }}]({{ $actionUrl }})</span>
</x-slot:subcopy>
@endisset
</x-mail::message>
