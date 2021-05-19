<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title') - {{ config('app.name', 'Träwelling') }}</title>

        <!-- Scripts -->
        <script src="{{ asset('js/app.js') }}?1611964800"></script>

        <!-- Fonts -->
        <link href="{{ asset('fonts/Nunito/Nunito.css') }}" rel="stylesheet">

        <!-- Styles -->
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
        <link rel="mask-icon" href="{{ asset('images/icons/touch-icon-vector.svg') }}">
        <link rel="shortcut favicon" href="{{ asset('images/icons/favicon.ico') }}">
        <link rel="shortcut icon" sizes="512x512" href="{{ asset('images/icons/logo512.png') }}">
        <link rel="shortcut icon" sizes="128x128" href="{{ asset('images/icons/logo128.png') }}">
        <link rel="author" href="/humans.txt">
        <meta name="copyright" content="Träwelling Team">
        <meta name="description" content="{{__('about.block1')}}">
        <meta name="keywords" content="Träwelling, Twitter, Deutsche, Bahn, Travel, Check-In, Zug, Bus, Tram, Mastodon">
        <meta name="audience" content="Travellers">
        <meta name="DC.Rights" content="Träwelling Team">
        <meta name="DC.Description" content="{{__('about.block1')}}">
        <meta name="DC.Language" content="de">

        @include('layouts.includes.meta-pwa')
        @yield('metadata')
        @yield('head')
    </head>
    <body>
        <div class="modal fade bd-example-modal-lg" id="notifications-board" tabindex="-1" role="dialog"
             aria-hidden="true" aria-labelledby="notifications-board-title">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="notifications-board-title">
                            {{ __('notifications.title') }}
                        </h4>
                        <button type="button" class="close" id="mark-all-read"
                                aria-label="{{ __('notifications.mark-all-read') }}">
                            <span aria-hidden="true"><i class="fas fa-check-double"></i></span>
                        </button>
                        <button type="button" class="close" data-mdb-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="notifications-list">
                        <div id="notifications-empty" class="text-center text-muted">{{ __('notifications.empty') }}
                            <br/>¯\_(ツ)_/¯
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="app">
            <nav class="navbar navbar-expand-md navbar-dark bg-trwl">
                <div class="container">
                    <a class="navbar-brand" href="{{ url('/') }}">
                        {{ config('app.name', 'Träwelling') }}
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
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('stats') ? 'active' : '' }}"
                                   href="{{ route('stats') }}">
                                    {{__('stats')}}
                                </a>
                            </li>
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
                                               class="border border-white rounded-left form-control my-0 py-1"
                                               placeholder="{{ __('stationboard.submit-search') }}"
                                               aria-label="User suchen"/>
                                        <button class="input-group-text btn-primary" type="submit">
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
                                        <span class="notifications-pill badge rounded-pill badge-notification" hidden>0</span>
                                    </a>
                                </li>
                                <li class="nav-item dropdown">
                                    <a id="navbarDropdown" href="#" class="nav-link dropdown-toggle mdb-select"
                                       role="button" data-mdb-toggle="dropdown" aria-haspopup="true"
                                       aria-expanded="false">
                                        {{ Auth::user()->name }} <span class="caret"></span>
                                    </a>

                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                        <a class="dropdown-item" href="{{ url('profile/'.Auth::user()->username) }}">
                                            <i class="fas fa-user"></i> {{ __('menu.profile') }}
                                        </a>
                                        <a class="dropdown-item" href="{{ route('export.landing') }}">
                                            <i class="fas fa-save"></i> {{ __('menu.export') }}
                                        </a>
                                        <a class="dropdown-item" href="{{ route('settings') }}">
                                            <i class="fas fa-cog"></i> {{ __('menu.settings') }}
                                        </a>
                                        @if(Auth::user()->role >= 5)
                                            <a class="dropdown-item" href="{{route('admin.dashboard')}}">
                                                <i class="fas fa-tools"></i> {{__('menu.admin')}}
                                            </a>
                                        @endif
                                        <div class="dropdown-divider"></div>
                                        <button class="dropdown-item" form="logout-form" type="submit">
                                            <i class="fas fa-sign-out-alt"></i> {{ __('menu.logout') }}
                                        </button>

                                        <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                              style="display: none;">
                                            @csrf
                                        </form>
                                    </div>
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
                        <button type="button" class="btn btn-primary dropdown-toggle" data-mdb-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-globe-europe"></i> {{__('settings.language.set')}}
                        </button>
                        <div class="dropdown-menu">
                            @foreach(config('app.locales') as $key => $lang)
                                <a class="dropdown-item" href="?language={{ $key }}">
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
                    / <a href="{{ route('static.privacy') }}">{{ __('menu.privacy') }}</a>
                </span>
                        <span class="footer-nav-link">
                    / <a href="{{ route('static.imprint') }}">{{ __('menu.imprint') }}</a>
                </span>
                        <span class="footer-nav-link">
                    / <a href="{{ route('blog.all') }}">{{ __('menu.blog') }}</a>
                </span>
                    </p>
                    <p class="mb-0">{!! __('menu.developed') !!}</p>
                    <p class="mb-0">&copy; {{date('Y')}} Tr&auml;welling</p>
                    <p class="mb-0 text-muted small">commit: {{ get_current_git_commit() }}</p>
                </div>
            </footer>
        </div>

        <div class="alert text-center cookiealert" role="alert">
            <b>Do you like cookies?</b> &#x1F36A; {{ __('messages.cookie-notice') }}
            <a href="{{route('static.privacy')}}">{{ __('messages.cookie-notice-learn') }}</a>

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
            var token = '{{ csrf_token() }}';
            var urlAvatarUpload = '{{route('settings.upload-image')}}';
            var urlDelete = '{{ route('status.delete') }}';
            var urlDisconnect = '{{ route('provider.destroy') }}';
            var urlDislike = '{{ route('like.destroy') }}';
            var urlEdit = '{{ route('edit') }}';
            var urlFollow = '{{ route('follow.create') }}';
            var urlFollowRequest = '{{ route('follow.request') }}';
            var urlLike = '{{ route('like.create') }}';
            var urlTrainTrip = '{{ route('trains.trip') }}';
            var urlUnfollow = '{{ route('follow.destroy') }}';
            var urlAutocomplete = '{{ url('transport/train/autocomplete') }}';
        </script>
        @yield('javascript-end')
    </body>
</html>
