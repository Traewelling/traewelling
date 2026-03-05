{{-- CAUTION! This template uses markdown! Never indent the lines below or you will break the layout --}}
{{-- Also never use __ or @lang, because mails handle translation very differently. Always use App\Helpers\Lang::trans() --}}
<table class="subcopy" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td>
{{ Illuminate\Mail\Markdown::parse($slot) }}
</td>
</tr>
</table>
