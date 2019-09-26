<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/typeahead.bundle.min.js') }}"></script>

    <!-- Fonts -->
    <link href="{{ asset('fonts/Nunito/Nunito.css') }}" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/typeaheadjs.css') }}" rel="stylesheet">
    <style>
        body {
            background: rgb(248, 250, 252);
        }
        .product-icon {
            width: 1em;
            height: 1em;
        }

        ul.timeline {
            list-style-type: none;
            position: relative;
        }
        ul.timeline:before {
            content: ' ';
            background: #d4d9df;
            display: inline-block;
            position: absolute;
            left: 29px;
            width: 2px;
            height: 100%;
            z-index: 400;
        }
        ul.timeline:last-child {
            background: transparent !important;
        }
        ul.timeline > li {
            margin: 20px 0;
            padding-left: 20px;
        }
        ul.timeline > li:before {
            content: ' ';
            background: white;
            display: inline-block;
            position: absolute;
            border-radius: 50%;
            border: 3px solid rgb(192, 57, 43);
            left: 20px;
            width: 20px;
            height: 20px;
            z-index: 400;
        }

        ul.timeline > li:last-child {
            line-height: 1;
        }

        p.status-body:before,
        p.train-status:before {
            font-family: FontAwesome;
            display: inline-block;
            padding-right: 6px;
            vertical-align: middle;
        }

        .connection {
            background: #f5f5f5;
        }
        .text-trwl {
            color: rgb(199, 39, 48) !important;;
        }
        .bg-trwl {
            background-color: #c72730 !important;
        }

        .navbar-dark .navbar-brand {
            font-weight: bold !important;
        }

        .dropdown-menu {
            z-index: 2000;
        }
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-dark bg-trwl">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">
                        @if (Auth::check())
                        <li class="nav-item {{ request()->is('dashboard/*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        @endif
                        <li class="nav-item {{ request()->is('leaderboard') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('leaderboard') }}">Leaderboard</a>
                        </li>
                        <li class="nav-item {{ request()->is('statuses/active') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('statuses.active') }}">Unterwegs</a>
                        </li>
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Authentication Links -->
                        @guest
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                            </li>
                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle mdb-select" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ url('profile/'.Auth::user()->username) }}"><i class="fas fa-user"></i> Profile</a>
                                    <a class="dropdown-item" href="{{ route('export.landing') }}"><i class="fas fa-save"></i> {{ __('Export') }}</a>
                                    <a class="dropdown-item" href="{{ route('settings') }}"><i class="fas fa-cog"></i> {{ __('Settings') }}</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        <i class="fas fa-sign-out-alt"></i> {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
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
                <p class="text-muted mb-0">
                    <a href="{{ route('changelog') }}">{{ substr(get_current_git_commit(), 0, -35) }}</a>
                    / <a href="{{ route('imprint') }}">{{ __('Imprint') }}</a>
                    / <a href="{{ route('privacy') }}">{{ __('Privacy') }}</a>
                    / <a href="{{ route('about') }}">{{ __('About')}}</a>
                    / <a href="{{ route('globaldashboard') }}">{{ __('Global Dashboard')}}</a>
                </p>
                <p class="mb-0">{!! __('Developed with <i class="fas fa-heart fa-sm" style="color: Tomato;""></i> in Baden') !!}</p>
                <p>&copy; 2019 Tr&auml;welling</p>
            </div>
        </footer>
    </div>

    <div class="alert text-center cookiealert" role="alert">
        <b>Do you like cookies?</b> &#x1F36A; Wir nutzen Cookies für unser Login-System. <a href="{{route('privacy')}}">Mehr erfahren</a>

        <button type="button" class="btn btn-primary btn-sm acceptcookies" aria-label="Close">
            Okay
        </button>
    </div>

    <script>
        var traincomplete = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            remote: {
                url: '{{ url('transport/train/autocomplete') }}/%QUERY',
                wildcard: '%QUERY'
            }
        });

        var buscomplete = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            remote: {
                url: '{{ url('transport/bus/autocomplete') }}/%QUERY',
                wildcard: '%QUERY'
            }
        });

        $('#station-autocomplete').typeahead({
            highlight: true
        },
            {
                name: 'trains',
                display: 'name',
                source: traincomplete,
                templates: {
                    suggestion: function (data) {
                        return '<strong><strong>' + data.name + '</strong> | Flixbus</strong>';
                    }
                }
            },
            {
                name: 'busses',
                display: 'name',
                source: buscomplete,
                templates: {
                    suggestion: function (data) {
                        return '<strong><strong>' + data.name + '</strong> | Flixbus</strong>';
                    }
                }
            }).on('typeahead:select', function(ev, suggestion) {
                if(suggestion.provider === 'busses') {
                    var autocompleteAction = '{{ route('busses.stationboard') }}';
                } else {
                    var autocompleteAction = '{{ route('trains.stationboard') }}';
                }

                $('#autocomplete-form').attr('action', autocompleteAction);
        });

        var touchmoved;
        $(document).on('click touchstart', '.trainrow', function() {
            var lineName = $(this).data('linename');
            var tripID = $(this).data('tripid');
            var start = $(this).data('start');
            if(touchmoved != true) {
                window.location = '{{ route('trains.trip') }}?tripID=' + tripID + '&lineName=' + lineName + '&start=' + start;
            }
        }).on('touchmove', function(e){
            touchmoved = true;
        }).on('touchstart', function(){
            touchmoved = false;
        });

        $(document).on('click touchend', '.train-destinationrow', function() {
            var tripID = $(this).parent().parent().data('tripid');
            var start = $(this).parent().parent().data('start');
            var destination = $(this).data('ibnr');
            var stopname = $(this).data('stopname');
            var linename = $(this).parent().parent().data('linename');
            if(touchmoved != true) {
                $('#checkinModal').modal('show', function (event) {
                    var modal = $(this)
                    modal.find('.modal-title').html(linename + ' <i class="fas fa-arrow-alt-circle-right"></i> ' + stopname);
                    modal.find('#input-tripID').val(tripID);
                    modal.find('#input-destination').val(destination);
                    modal.find('#input-start').val(start);
                });

            }
        }).on('touchmove', function(e){
            touchmoved = true;
        }).on('touchstart', function(){
            touchmoved = false;
        });

        $('#checkinModal').on('show.bs.modal', function (event) {
            $(event.relatedTarget)
        });

        $('#checkinButton').click(function(e){
            e.preventDefault();
            $('#checkinForm').submit();
        });

        var token = '{{ Session::token() }}';
        var urlEdit = '{{ route('edit') }}';
        var urlDelete = '{{ route('status.delete') }}';
        var urlLike = '{{ route('like.create') }}';
        var urlDislike = '{{ route('like.destroy') }}';
        var urlDisconnect = '{{ route('provider.destroy') }}';
        var urlFollow = '{{ route('follow.create') }}';
        var urlUnfollow = '{{ route('follow.destroy') }}';
    </script>
</body>
</html>
