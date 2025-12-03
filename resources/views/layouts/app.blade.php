<!DOCTYPE html>
@php
    use App\Http\Controllers\Backend\VersionController;
    use App\Services\PrideService;
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark" data-bs-theme="dark">
<head>
    <title>@yield('title') - {{ config('app.name', 'Träwelling') }}</title>

    @include('layouts.includes.meta')

    <!-- Scripts -->
    <!-- Run this blocking script as early as possible to prevent flickering -->
    <script>
        if (localStorage.getItem("darkMode") === null) {
            localStorage.setItem("darkMode", "auto");
        }
        let darkModeSetting = localStorage.getItem("darkMode");
        if (darkModeSetting === "auto") {
            darkModeSetting = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
        }
        document.documentElement.classList.toggle("dark", darkModeSetting === "dark");
        document.documentElement.setAttribute("data-bs-theme", darkModeSetting);
    </script>

    <!-- Fonts -->
    <link href="{{ asset('fonts/Nunito/Nunito.css') }}" rel="stylesheet">

    <!-- Styles -->
    <link rel="mask-icon" href="{{ asset('images/icons/touch-icon-vector.svg') }}">
    <link rel="icon" href="{{ asset('images/icons/logo128.png') }}" sizes="128x128">
    <link rel="icon" href="{{ asset('images/icons/logo512.png') }}" sizes="512x512">
    <link rel="shortcut icon" href="{{ asset('images/icons/favicon.ico') }}">
    <link rel="author" href="/humans.txt">
    <link rel="manifest" href="/manifest.json">

    @vite(['resources/sass/app.scss', 'resources/sass/app-dark.scss', 'resources/js/app.js'])

    @yield('head')
</head>
<body>
<div id="app">
    <nav class="navbar navbar-expand-md navbar-dark bg-trwl" id="nav-main">
        <div class="container">
            <a class="navbar-brand {{ PrideService::getCssClassesForPrideFlag() }}" href="{{ url('/') }}">
                {{ config('app.name') }}
            </a>

            <div class="navbar-toggler">
                @auth
                    <notification-bell></notification-bell>
                @endauth
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                        aria-expanded="false" aria-label="{{ __('toggle-navigation') }}">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('dashboard/*') ? 'active' : '' }}"
                               href="{{ route('dashboard') }}">
                                {{ __('menu.dashboard') }}
                            </a>
                        </li>
                    @endauth
                    @if(!auth()->check() || auth()->user()->points_enabled)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('leaderboard') ? 'active' : '' }}"
                               href="{{ route('leaderboard') }}">
                                {{ __('menu.leaderboard') }}
                            </a>
                        </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('statuses/active') ? 'active' : '' }}"
                           href="{{ route('statuses.active') }}">
                            {{ __('menu.active') }}
                        </a>
                    </li>
                    @auth
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('stats') ? 'active' : '' }}"
                               href="{{ route('stats') }}">
                                {{__('stats')}}
                            </a>
                        </li>
                        @if(config('trwl.year_in_review.alert'))
                            <li class="nav-item">
                                <a class="nav-link" href="/year-in-review">
                                    <i class="fa-solid fa-champagne-glasses"></i>
                                    {{__('year-review')}}
                                </a>
                            </li>
                        @endif
                    @endauth
                </ul>

                <ul class="navbar-nav w-auto">
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">
                                {{ __('menu.login') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">
                                {{ __('menu.register') }}
                            </a>
                        </li>
                    @else
                        <form class="form-inline" action="{{ route('userSearch') }}">
                            <div class="input-group md-form form-sm form-2 ps-0 m-0">
                                <input name="searchQuery" type="text" value="{{ request()->get('searchQuery', '') }}"
                                       class="border border-white rounded-left form-control my-0 py-1"
                                       placeholder="{{ __('stationboard.submit-search') }}"
                                       aria-label="{{ __('stationboard.submit-search') }}"
                                       required
                                />
                                <button class="btn btn-primary" type="submit"
                                        aria-label="{{ __('stationboard.submit-search') }}">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                        <li class="nav-item d-none d-md-inline-block">
                            <notification-bell :link="true" :allow-fetch="false"></notification-bell>
                        </li>
                        <li class="nav-item dropdown">
                            <button id="navbarDropdown" class="nav-link dropdown-toggle select"
                                    data-bs-dropdown-animation="off" data-bs-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false">
                                {{ Auth::user()->name }}
                                <span class="caret"></span>
                            </button>

                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li>
                                    <a class="dropdown-item"
                                       href="{{ route('profile', ['username' => auth()->user()->username]) }}">
                                        <i class="fas fa-user"></i> {{ __('menu.profile') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('export') }}">
                                        <i class="fas fa-save"></i> {{ __('menu.export') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('settings') }}">
                                        <i class="fas fa-cog"></i> {{ __('menu.settings') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="https://help.traewelling.de/faq/"
                                       target="_blank">
                                        <i class="fa-solid fa-bug" aria-hidden="true"></i>
                                        {{ __('help') }}
                                    </a>
                                </li>
                                @if(auth()->user()->hasRole('admin') || auth()->user()->can('view-events'))
                                    <li>
                                        <a class="dropdown-item" href="{{route('admin.welcome')}}">
                                            <i class="fas fa-tools"></i> Backend
                                        </a>
                                    </li>
                                @endif
                                <li>
                                    <hr class="dropdown-divider"/>
                                </li>

                                <form method="POST" action="{{ route('logout') }}" id="logout-form">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt"></i> {{ __('menu.logout') }}
                                    </button>
                                </form>
                            </ul>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <main class="py-4" role="main">
        @include('includes.message-block')
        @yield('content')
    </main>

    <footer class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-6 col-md-2 mb-3">
                    <ul class="nav flex-column">
                        <li class="nav-item mb-2">
                            <a href="{{ route('events') }}" class="nav-link p-0 text-body-secondary">
                                {{ __('events') }}
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a href="https://help.traewelling.de/faq/" target="_blank"
                               class="nav-link p-0 text-body-secondary">
                                {{ __('menu.about') }}
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="col-6 col-md-2 mb-3">
                    <ul class="nav flex-column">
                        <li class="nav-item mb-2">
                            <a href="{{ route('legal.privacy') }}" class="nav-link p-0 text-body-secondary">
                                {{ __('menu.privacy') }}
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a href="{{ route('legal.notice') }}" class="nav-link p-0 text-body-secondary">
                                {{ __('menu.legal-notice') }}
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="col-6 col-md-2 mb-3">
                    <ul class="nav flex-column">
                        <li class="nav-item mb-2">
                            <a href="https://blog.traewelling.de"
                               target="blog"
                               class="nav-link p-0 text-body-secondary"
                            >
                                {{ __('menu.blog') }}
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a href="https://chaos.social/@traewelling"
                               target="_blank"
                               class="nav-link p-0 text-body-secondary">
                                Mastodon
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="col-md-auto ms-md-auto mb-3">
                    <ul class="nav flex-column">
                        <li class="nav-item mb-2">
                            <div class="btn-group dropup w-100">
                                <button type="button" class="btn btn-primary btn-block dropdown-toggle"
                                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-globe-europe"></i> {{ __('settings.language.set') }}
                                </button>
                                <div class="dropdown-menu">
                                    @foreach(config('app.locales') as $key => $lang)
                                        <a class="dropdown-item"
                                           href="{{request()->fullUrlWithQuery(['language' => $key])}}">
                                            {{ $lang }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </li>
                        <li class="nav-item mb-2">
                            <div class="btn-group dropup w-100">
                                <button type="button" class="btn btn-primary btn-block dropdown-toggle"
                                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-circle-half-stroke"></i>
                                    {{__('settings.colorscheme.set')}}
                                </button>
                                <div class="dropdown-menu">
                                    <div class="dropdown-item" id="colorModeToggleLight">
                                        <i class="fas fa-sun"></i>
                                        {{__('settings.colorscheme.light')}}
                                    </div>
                                    <div class="dropdown-item" id="colorModeToggleDark">
                                        <i class="fas fa-moon"></i>
                                        {{__('settings.colorscheme.dark')}}
                                    </div>
                                    <div class="dropdown-item" id="colorModeToggleAuto">
                                        <i class="fas fa-circle-half-stroke"></i>
                                        {{__('settings.colorscheme.auto')}}
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="d-flex flex-column flex-sm-row justify-content-between py-4 my-4 border-top">
                <p class="mb-0">&copy; {{ date('Y') }} Tr&auml;welling</p>
                <p class="mb-0">{!! __('menu.developed') !!}</p>
                <p class="mb-0 text-muted small">
                    Version
                    <a href="{{route('changelog')}}">
                        {{ VersionController::getVersion() }}
                    </a>
                </p>
            </div>
        </div>
    </footer>
</div>

<div class="alert text-center cookiealert" role="alert">
    <b>Do you like cookies?</b> &#x1F36A; {{ __('messages.cookie-notice') }}
    <a href="{{ route('legal.privacy') }}">{{ __('messages.cookie-notice-learn') }}</a>
    <button type="button" class="btn btn-primary btn-sm acceptcookies"
            aria-label="{{ __('messages.cookie-notice-button') }}">
        {{ __('messages.cookie-notice-button') }}
    </button>
</div>

<script>
    var token = '{{ csrf_token() }}';
    var mapprovider = '{{ Auth::user()->mapprovider ?? "default" }}';
</script>
@yield('footer')
</body>
</html>
