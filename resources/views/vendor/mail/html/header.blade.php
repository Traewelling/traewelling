{{-- CAUTION! This template uses markdown! Never indent the lines below or you will break the layout --}}
{{-- Also never use __ or @lang, because mails handle translation very differently. Always use App\Helpers\Lang::trans() --}}
@props(['url'])
<tr>
<td>
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="header" style="text-align: right;">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ $url  }}/images/icons/logo512.png" class="logo" alt="Träwelling Logo">
</a>
</td>
<td class="header" style="text-align: left;">
<a href="{{ $url }}" style="display: inline-block;">
{!! $slot !!}
</a>
</td>
</tr>
</table>
</td>
</tr>
