@extends('layouts.app')

@section('title')
    {{__('privacy.title') }}
@endsection

@section('content')
<style>
.cookiealert { /* Wir wollen keine doppelten Bottom-Bars auf der Privacy-Seite. */
    display: none;
}
</style>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">

            @if($user != null)

                @if($user->privacy_ack_at == NULL)
                    <div class="card mb-3">
                        <p class="card-body mb-0">
                            {!! __('privacy.not-signed-yet') !!}
                        </p>
                    </div>
                @elseif($user->privacy_ack_at < $agreement->valid_at)
                    <div class="card mb-3">
                        <p class="card-body mb-0">
                            {!! __('privacy.we-changed') !!}
                        </p>
                    </div>
                @endif

                @if($user->privacy_ack_at < $agreement->valid_at || $user->privacy_ack_at == NULL)
                <form method="POST" action="{{ route('gdpr.ack') }}" class="fixed-bottom bg-light text-right">
                    @csrf
                    <div class="container"><div class="row justify-content-center"><div class="col-md-8">

                        <a class="btn btn-link pr-0" href="#" role="button" data-toggle="modal" data-target="#deleteUserModal">{{ __('settings.delete-account') }}</a>
                        <input type="submit" value="{{__('privacy.sign')}}" class="btn btn-success">

                    </div></div></div>
                </form>

                <!-- Das ist das Delete Modal aus den Settings -->
                <div class="modal fade" id="deleteUserModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">{{__('settings.delete-account')}}</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                {!! __('settings.delete-account-verify', ['appname' => env('APP_NAME')])  !!}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-grey btn-sm" data-dismiss="modal">{{ __('settings.delete-account-btn-back') }}</button>
                                <a href="{{ route('account.destroy') }}" role="button" class="btn btn-red btn-sm">{{ __('settings.delete-account-btn-confirm') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            @endif

            <div class="privacy">
                @php($body = "")
                @switch(Lang::locale())
                    @case("de")
                        @php($body = $agreement->body_md_de)
                        @break
                    @case("en")
                        @php($body = $agreement->body_md_en)
                        @break
                @endswitch
                {!! Markdown::parse($body) !!}
            </div>
        </div>
    </div>
</div>

@endsection
