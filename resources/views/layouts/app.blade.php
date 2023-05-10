<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <title>@yield('title') - {{ config('app.name', 'Träwelling') }}</title>

        @include('layouts.includes.meta')

        <!-- Scripts -->
        <script src="{{ mix('js/app.js') }}"></script>

        <!-- Fonts -->
        <link href="{{ asset('fonts/Nunito/Nunito.css') }}" rel="stylesheet">

        <!-- Styles -->
        <link href="{{ mix('css/app.css') }}" rel="stylesheet">
        <link rel="mask-icon" href="{{ asset('images/icons/touch-icon-vector.svg') }}">
        <link rel="shortcut favicon" href="{{ asset('images/icons/favicon.ico') }}">
        <link rel="shortcut icon" sizes="512x512" href="{{ asset('images/icons/logo512.png') }}">
        <link rel="shortcut icon" sizes="128x128" href="{{ asset('images/icons/logo128.png') }}">
        <link rel="author" href="/humans.txt">
        <link rel="manifest" href="/manifest.json"/>

        @yield('head')
    </head>
    <body>
        <div id="app">
            <nav class="navbar navbar-expand-md navbar-dark bg-trwl" id="nav-main">
                <div class="container">
                    <a class="navbar-brand" href="{{ url('/') }}">
                        {{ config('app.name') }}
                    </a>
                    <div class="navbar-toggler">
                        @auth
                            <button class="navbar-toggler notifications-board-toggle" type="button"
                                    data-mdb-toggle="modal"
                                    data-mdb-target="#notifications-board" aria-controls="navbarSupportedContent"
                                    aria-expanded="false"
                                    aria-label="{{ __('Show notifications') }}">
                                <span class="notifications-bell far fa-bell"></span>
                                <span class="notifications-pill badge rounded-pill badge-notification" hidden>0</span>
                            </button>
                        @endauth
                        <button class="navbar-toggler" type="button" data-mdb-toggle="collapse"
                                data-mdb-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                                aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                            <i class="fas fa-bars"></i>
                        </button>
                    </div>
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav me-auto">
                            @auth
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('dashboard/*') ? 'active' : '' }}"
                                       href="{{ route('dashboard') }}">{{ __('menu.dashboard') }}</a>
                                </li>
                            @endauth
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('leaderboard') ? 'active' : '' }}"
                                   href="{{ route('leaderboard') }}">{{ __('menu.leaderboard') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('statuses/active') ? 'active' : '' }}"
                                   href="{{ route('statuses.active') }}">{{ __('menu.active') }}</a>
                            </li>
                            @auth
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('stats') ? 'active' : '' }}"
                                       href="{{ route('stats') }}">
                                        {{__('stats')}}
                                    </a>
                                </li>
                                @if(config('trwl.year_in_review_active'))
                                    <li class="nav-item">
                                        <a class="nav-link" href="/your-year/">
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
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('menu.login') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('menu.register') }}</a>
                                </li>
                            @else
                                <form class="form-inline" action="{{ route('userSearch') }}">
                                    <div class="input-group md-form form-sm form-2 ps-0 m-0">
                                        <input name="searchQuery" type="text"
                                               value="{{request()->has('searchQuery') ? request()->searchQuery : ''}}"
                                               class="border border-white rounded-left form-control my-0 py-1"
                                               placeholder="{{ __('stationboard.submit-search') }}"
                                               aria-label="{{ __('stationboard.submit-search') }}"
                                               required
                                        />
                                        <button class="btn btn-primary" type="submit">
                                            <i class="fas fa-search" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </form>
                                <li class="nav-item d-none d-md-inline-block">
                                    <a href="javascript:void(0)" id="notifications-toggle"
                                       class="nav-link notifications-board-toggle"
                                       data-mdb-toggle="modal"
                                       data-mdb-target="#notifications-board">
                                        <span class="notifications-bell far fa-bell"></span>
                                        <span class="notifications-pill badge rounded-pill badge-notification" hidden>
                                            0
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item dropdown">
                                    <a id="navbarDropdown" href="#" class="nav-link dropdown-toggle mdb-select"
                                       role="button" data-mdb-dropdown-animation="off" data-mdb-toggle="dropdown"
                                       aria-haspopup="true" aria-expanded="false">
                                        {{ Auth::user()->name }}
                                        <span class="caret"></span>
                                    </a>

                                    <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                        <li>
                                            <a class="dropdown-item"
                                               href="{{ route('profile', ['username' => auth()->user()->username]) }}">
                                                <i class="fas fa-user"></i> {{ __('menu.profile') }}
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('export.landing') }}">
                                                <i class="fas fa-save"></i> {{ __('menu.export') }}
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('settings') }}">
                                                <i class="fas fa-cog"></i> {{ __('menu.settings') }}
                                            </a>
                                        </li>
                                        @if(config('ticket.host') !== null)
                                            <li>
                                                <a class="dropdown-item" href="{{ route('static.about') }}">
                                                    <i class="fa-solid fa-bug" aria-hidden="true"></i>
                                                    {{ __('help') }}
                                                </a>
                                            </li>
                                        @endif
                                        @admin
                                            <li>
                                                <a class="dropdown-item" href="{{route('admin.dashboard')}}">
                                                    <i class="fas fa-tools"></i> {{__('menu.admin')}}
                                                </a>
                                            </li>
                                        @endadmin
                                        <li>
                                            <hr class="dropdown-divider" />
                                        </li>

                                        <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                              style="display: none;">
                                            @csrf
                                        </form>

                                        <li>
                                            <button class="dropdown-item" form="logout-form" type="submit">
                                                <i class="fas fa-sign-out-alt"></i> {{ __('menu.logout') }}
                                            </button>
                                        </li>
                                    </ul>
                                </li>
                            @endguest
                        </ul>
                    </div>
                </div>
            </nav>

            <main class="py-4">
                @include('includes.message-block')

                @yield('content')
            </main>
            <footer class="footer mt-auto py-3">
                <div class="container">
                    <div class="btn-group dropup float-end">
                        <button type="button" class="btn btn-primary dropdown-toggle" data-mdb-dropdown-animation="off"
                                data-mdb-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-globe-europe"></i> {{__('settings.language.set')}}
                        </button>
                        <div class="dropdown-menu">
                            @foreach(config('app.locales') as $key => $lang)
                                <a class="dropdown-item" href="{{request()->fullUrlWithQuery(['language' => $key])}}">
                                    {{ $lang }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                    <p class="text-muted mb-0">
                <span class="footer-nav-link">
                    <a href="{{ route('static.about') }}">{{ __('menu.about')}}</a>
                </span>
                        <span class="footer-nav-link">
                    / <a href="{{ route('globaldashboard') }}">{{ __('menu.globaldashboard')}}</a>
                </span>
                        <span class="footer-nav-link">
                    / <a href="{{ route('events') }}">{{ __('events') }}</a>
                </span>
                        <span class="footer-nav-link">
                    / <a href="{{ route('legal.privacy') }}">{{ __('menu.privacy') }}</a>
                </span>
                        <span class="footer-nav-link">
                    / <a href="{{ route('legal.notice') }}">{{ __('menu.legal-notice') }}</a>
                </span>
                        <span class="footer-nav-link">
                    / <a href="https://blog.traewelling.de" target="blog">{{ __('menu.blog') }}</a>
                </span>
                    </p>
                    <p class="mb-0">{!! __('menu.developed') !!}</p>
                    <p class="mb-0">&copy; {{date('Y')}} Tr&auml;welling</p>
                    <p class="mb-0 text-muted small">
                        Version
                        <a href="https://github.com/Traewelling/traewelling/commit/{{ \App\Http\Controllers\Backend\VersionController::getVersion() }}"
                           class="text-muted">
                            {{ \App\Http\Controllers\Backend\VersionController::getVersion() }}
                        </a>
                    </p>
                </div>
            </footer>
        </div>

        <div class="alert text-center cookiealert" role="alert">
            <b>Do you like cookies?</b> &#x1F36A; {{ __('messages.cookie-notice') }}
            <a href="{{route('legal.privacy')}}">{{ __('messages.cookie-notice-learn') }}</a>

            <button type="button" class="btn btn-primary btn-sm acceptcookies" aria-label="Close">
                {{ __('messages.cookie-notice-button') }}
            </button>
        </div>

        <script>
            /**
             * Let's only keep the JS here that is needed, e.g. Routes or CSRF tokens and put the rest
             * in the compontents folder. I moved the touch controls that were here and are needed for
             * checkin into components/stationboard.js.
             */
            var token            = '{{ csrf_token() }}';
            var urlAvatarUpload  = '{{route('settings.upload-image')}}';
            var urlDisconnect    = '{{ route('provider.destroy') }}';
            var urlDislike       = '{{ route('like.destroy') }}';
            var urlFollow        = '{{ route('follow.create') }}';
            var urlFollowRequest = '{{ route('follow.request') }}';
            var urlLike          = '{{ route('like.create') }}';
            var urlTrainTrip     = '{{ route('trains.trip') }}';
            var urlUnfollow      = '{{ route('follow.destroy') }}';
            var urlAutocomplete  = '{{ url('transport/train/autocomplete') }}';

            let translations = {
                stationboard: {
                    position_unavailable: '{{__('stationboard.position-unavailable')}}',
                }
            };
        </script>
    </body>

    @include('includes.check-in-modal')
    @include('includes.modals.notifications-board')
    @yield('footer')
</html>
