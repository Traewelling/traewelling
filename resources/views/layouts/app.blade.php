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
    <div id="nav-main">
        <vue-navbar></vue-navbar>
    </div>
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
