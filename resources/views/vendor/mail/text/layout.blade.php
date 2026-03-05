{{-- CAUTION! This template uses markdown! Never indent the lines below or you will break the layout --}}
{{-- Also never use __ or @lang, because mails handle translation very differently. Always use App\Helpers\Lang::trans() --}}
{!! strip_tags($header ?? '') !!}

{!! strip_tags($slot) !!}
@isset($subcopy)

{!! strip_tags($subcopy) !!}
@endisset

{!! strip_tags($footer ?? '') !!}
