<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

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
    </body>
</html>
