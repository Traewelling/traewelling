<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <meta name="mobile-web-app-capable" content="yes">
        <meta name="theme-color" content="#c72730">
        <meta name="name" content="{{ config('app.name') }}">
        <link rel="author" href="/humans.txt">
        <meta name="copyright" content="Träwelling Team">
        <meta name="description" content="{{__('about.block1')}}">
        <meta name="keywords" content="Träwelling, Twitter, Deutsche, Bahn, Travel, Check-In, Zug, Bus, Tram, Mastodon">
        <meta name="audience" content="Travellers">
        <meta name="robots" content="index, nofollow">
        <meta name="DC.Rights" content="Träwelling Team">
        <meta name="DC.Description" content="{{__('about.block1')}}">
        <meta name="DC.Language" content="de">

        <link rel="shortcutt favicon" rel="{{ asset('images/icons/favicon.ico') }}">
        <link rel="shortcut icon" sizes="512x512" href="{{ asset('images/icons/logo512.png') }}">
        <link rel="shortcut icon" sizes="128x128" href="{{ asset('images/icons/logo128.png') }}">

        <link href="{{ asset('fonts/Nunito/Nunito.css') }}" rel="stylesheet">
        <link href="{{ asset('css/welcome.css') }}" rel="stylesheet">

    </head>
    <body>
        <div class="flex-center position-ref full-height">
            <div class="videoContainer">
                <div class="overlay"></div>
                <video loop muted autoplay class="fullscreen-bg__video">
                    <source src="{{ asset('img/vid1.mp4') }}" type="video/mp4">
                </video>
            </div>
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
