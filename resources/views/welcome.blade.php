<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <title>{{ config('app.name', 'Laravel') }}</title>

        @include('layouts.app-head')

        <!-- Fonts -->
        <link href="{{ asset('fonts/Nunito/Nunito.css') }}" rel="stylesheet">

        <!-- Styles -->
        <link href="{{ asset('css/welcome.css') }}" rel="stylesheet"/>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            <div class="videoContainer">
                <div class="overlay"></div>
                <video loop muted autoplay class="fullscreen-bg__video">
                    <source src="{{ asset('img/vid1.mp4') }}" type="video/mp4">
                </video>
            </div>
            @if (Route::has('login'))
                <div class="top-right links">
                    @auth
                        <a href="{{ url('/dashboard') }}">{{__('menu.dashboard')}}</a>
                    @else
                        <a href="{{ route('login') }}">{{__('menu.login')}}</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}">{{__('menu.register')}}</a>
                        @endif
                    @endauth
                </div>
            @endif

            <div class="content">
                <div class="title m-b-md">
                    {{ config('app.name', 'Laravel') }}
                </div>

                <div class="links">
                    <a href="{{ url('/auth/redirect/twitter') }}">Twitter</a>
                    <a href="{{ url('/login') }}">Mastodon</a>
                </div>
                <div class="links">
                    <a href="{{ url('/leaderboard') }}">{{__('menu.leaderboard')}}</a>
                    <a href="{{ route('static.about') }}">{{ __('menu.about')}}</a>
                    <a href="{{ url('/statuses/active') }}">{{__('menu.active')}}</a>
                </div>
            </div>

            <div class="bottom-center links" style="">
                <a href="{{ route('static.privacy') }}">{{ __('menu.privacy') }}</a>

                <a href="{{ route('static.imprint') }}">{{ __('menu.imprint') }}</a>
            </div>
        </div>
    </body>
</html>
