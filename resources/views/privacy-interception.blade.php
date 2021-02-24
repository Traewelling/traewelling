@extends('layouts.app')

@section('title'){{ html_entity_decode(__('privacy.title')) }}@endsection

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

                    @if($user->privacy_ack_at == null)
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

                    @if($user->privacy_ack_at < $agreement->valid_at || $user->privacy_ack_at == null)
                        <form method="POST" action="{{ route('gdpr.ack') }}" class="fixed-bottom bg-light text-right">
                            @csrf
                            <div class="container">
                                <div class="row justify-content-center">
                                    <div class="col-md-8">

                                        <a class="btn btn-link pr-0" href="#" role="button" data-toggle="modal"
                                           data-target="#deleteUserModal">{{ __('settings.delete-account') }}</a>
                                        <input type="submit" value="{{__('privacy.sign')}}" class="btn btn-success">

                                    </div>
                                </div>
                            </div>
                        </form>

                        @include('settings.modals.deleteUserModal')
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
