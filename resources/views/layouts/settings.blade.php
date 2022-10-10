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
        <link href="{{ mix('css/admin.css') }}" rel="stylesheet">
        <link rel="mask-icon" href="{{ asset('images/icons/touch-icon-vector.svg') }}">
        <link rel="shortcut favicon" href="{{ asset('images/icons/favicon.ico') }}">
        <link rel="shortcut icon" sizes="512x512" href="{{ asset('images/icons/logo512.png') }}">
        <link rel="shortcut icon" sizes="128x128" href="{{ asset('images/icons/logo128.png') }}">
        <link rel="author" href="/humans.txt">
        <link rel="manifest" href="/manifest.json"/>

        @yield('head')
    </head>
    <body>
        @include('includes.message-block')
            <main class="bg-dark">
                @yield('content')
            </main>

        <script>
            /**
             * Let's only keep the JS here that is needed, e.g. Routes or CSRF tokens and put the rest
             * in the compontents folder. I moved the touch controls that were here and are needed for
             * checkin into components/stationboard.js.
             */
            var token            = '{{ csrf_token() }}';
            var urlAvatarUpload  = '{{route('settings.upload-image')}}';
            var urlDelete        = '{{ route('status.delete') }}';
            var urlDisconnect    = '{{ route('provider.destroy') }}';
            var urlDislike       = '{{ route('like.destroy') }}';
            var urlEdit          = '{{ route('edit') }}';
            var urlFollow        = '{{ route('follow.create') }}';
            var urlFollowRequest = '{{ route('follow.request') }}';
            var urlLike          = '{{ route('like.create') }}';
            var urlTrainTrip     = '{{ route('trains.trip') }}';
            var urlUnfollow      = '{{ route('follow.destroy') }}';
            var urlAutocomplete  = '{{ url('transport/train/autocomplete') }}';
        </script>
    </body>

    @include('includes.modals.notifications-board')
    @yield('footer')
</html>
