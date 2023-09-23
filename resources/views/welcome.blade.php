<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <title>{{ config('app.name', 'Träwelling') }}</title>

        @section('meta-description', __('about.block1'))
        @section('meta-robots', 'index')
        @section('canonical', route('static.welcome'))
        @include('layouts.includes.meta')

        <link rel="shortcut favicon" href="{{ asset('images/icons/favicon.ico') }}">
        <link rel="shortcut icon" sizes="512x512" href="{{ asset('images/icons/logo512.png') }}">
        <link rel="shortcut icon" sizes="128x128" href="{{ asset('images/icons/logo128.png') }}">

        <link href="{{ asset('fonts/Nunito/Nunito.css') }}" rel="stylesheet">
        @vite('resources/sass/welcome.scss')
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
                    <a href="{{ route('register') }}">{{__('menu.register')}}</a>
                @endauth
            </div>

            <div class="content">
                <div class="title m-b-md">
                    {{ config('app.name', 'Träwelling') }}
                </div>

                <div class="links">
                    <a href="{{ url('/login') }}">{{__('menu.login')}}</a>
                    <a href="{{ url('/leaderboard') }}">{{__('menu.leaderboard')}}</a>
                    <a href="{{ route('static.about') }}">{{ __('menu.about')}}</a>
                    <a href="{{ url('/statuses/active') }}">{{__('menu.active')}}</a>
                </div>
            </div>

            <div class="bottom-center links" style="">
                <a href="{{ route('legal.privacy') }}">{{ __('menu.privacy') }}</a>
                <a href="{{ route('legal.notice') }}">{{ __('menu.legal-notice') }}</a>
            </div>
        </div>
    </body>
</html>
