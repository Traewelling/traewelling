@extends('layouts.app')

@section('title'){{ __('menu.settings') }}@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                @include('settings.cards.general')

                <div class="card mt-3">
                    <div class="card-header">🎄 {{ __('christmas-mode') }}</div>

                    <div class="card-body text-center">

                        <form method="POST" action="{{ route('christmas-mode') }}">
                            @csrf

                            @if(session()->get('christmas-mode') === true)
                                <input type="hidden" name="christmas-mode" value="0"/>
                                <button type="submit" class="btn btn-secondary">
                                    🎁 {{ __('christmas-mode.disable') }} ⛄️
                                </button>
                            @else
                                <input type="hidden" name="christmas-mode" value="1"/>
                                <button type="submit" class="btn btn-success">
                                    🎁 {{ __('christmas-mode.enable') }} ⛄️
                                </button>
                            @endif
                        </form>
                    </div>
                </div>


                @include('settings.cards.privacy')
                @include('settings.cards.password')
                @include('settings.cards.login-providers')
                @include('settings.cards.sessions')
                @include('settings.cards.ics')
                @include('settings.cards.api-token')
                @include('settings.cards.account-deletion')
            </div>
        </div>
    </div>
@endsection
