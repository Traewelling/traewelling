<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="author" href="/humans.txt">
        <meta name="copyright" content="Träwelling Team">
        <meta name="description" content="{{__('about.block1')}}">
        <meta name="keywords" content="Träwelling, Twitter, Deutsche, Bahn, Travel, Check-In, Zug, Bus, Tram, Mastodon">
        <meta name="audience" content="Travellers">
        <meta name="robots" content="index, nofollow">
        <meta name="DC.Rights" content="Träwelling Team">
        <meta name="DC.Description" content="{{__('about.block1')}}">
        <meta name="DC.Language" content="de">


        <!-- Fonts -->
    <link href="{{ asset('fonts/Nunito/Nunito.css') }}" rel="stylesheet">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #fff;
                font-family: 'Nunito', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                z-index: 3;
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .bottom-center {
                z-index: 3;
                position: absolute;
                bottom: 18px;
                text-align: center;
            }

            .content {
                z-index: 3;
                text-align: center;
                position: absolute;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #fff;
                padding: 0 25px;
                font-size: 13px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }

            .videoContainer {
                position: relative;
                width: 100%;
                height: 100%;
                background-attachment: scroll;
                overflow: hidden;
            }
            .videoContainer video {
                min-width: 100%;
                min-height: 100%;
                position: relative;
                z-index: 1;
            }
            .videoContainer .overlay {
                height: 100%;
                width: 100%;
                position: absolute;
                top: 0px;
                left: 0px;
                z-index: 2;
                background-image: linear-gradient(#d4353e, #a20b12);
                opacity: 0.8;
            }
        </style>

        <!-- Matomo -->
        <script type="text/javascript">
            var _paq = window._paq || [];
            /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
            _paq.push(['trackPageView']);
            _paq.push(['enableLinkTracking']);
            (function() {
                var u="//matomo.trwl.pw/";
                _paq.push(['setTrackerUrl', u+'matomo.php']);
                _paq.push(['setSiteId', '1']);
                var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
                g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
            })();
        </script>
        <!-- End Matomo Code -->
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
