@extends('layouts.minimal')

@section('code', '503')

@section('content')
    @php
        $releasesUrl = config('app.links.releases', 'https://github.com/Traewelling/traewelling/releases');
        $mastodonUrl = config('app.links.mastodon', 'https://chaos.social/@traewelling');
        $helpUrl     = config('app.links.help', 'https://help.traewelling.de/');

        $refreshMs   = (int) config('app.maintenance_refresh_ms', 30000);
    @endphp

    <div class="text-center py-4 px-3">
        <h1 class="display-1 mb-3">503</h1>

        <h2 class="mb-3">
            <i class="fa-solid fa-wrench"></i>
            {{ __('maintenance.title') }}
        </h2>

        <p class="lead text-muted mb-4">
            {{ __('maintenance.subtitle') }} {{ __('maintenance.try-later') }}<br><br>
            {{ __('maintenance.prolonged') }}
        </p>

        <div class="card mx-auto my-3" style="max-width: 720px;">
            <div class="card-body">
                <h3 class="h5 mb-3">
                    <i class="fa-solid fa-circle-info"></i>
                    {{ __('maintenance.more-info') }}
                </h3>
                <ul class="list-unstyled text-start mb-0">
                    <li class="mb-2">
                        <i class="fa-solid fa-tags me-2"></i>
                        <a class="link-underline link-underline-opacity-0" href="{{ $releasesUrl }}" target="_blank"
                           rel="noopener">
                            {{ __('maintenance.release-notes') }}
                        </a>
                        <span class="text-muted">- {{ __('maintenance.release-notes.desc') }}</span>
                    </li>
                    <li class="mb-2">
                        <i class="fa-brands fa-mastodon me-2"></i>
                        <a class="link-underline link-underline-opacity-0" href="{{ $mastodonUrl }}" target="_blank"
                           rel="me noopener">
                            {{ __('maintenance.follow') }}
                        </a>
                        <span class="text-muted">- {{ __('maintenance.follow.desc') }}</span>
                    </li>
                    <li class="mb-0">
                        <i class="fa-regular fa-circle-question me-2"></i>
                        <a class="link-underline link-underline-opacity-0" href="{{ $helpUrl }}" target="_blank"
                           rel="noopener">
                            {{ __('maintenance.help-center') }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <p class="text-muted mt-4 mb-0">
            <i class="fa-solid fa-arrows-rotate"></i>
            {{ __('maintenance.auto-refresh') }}
        </p>
    </div>

    <hr/>

    <script>
        (function () {
            var REFRESH_MS = {{ $refreshMs }};
            var timer = setInterval(function () {
                fetch(window.location.href, {method: 'HEAD', cache: 'no-store'})
                    .then(function (res) {
                        if (res.status !== 503) window.location.reload();
                    })
                    .catch(function () {
                        //do nothing
                    });
            }, REFRESH_MS);

            var btn = document.getElementById('refresh-now');
            if (btn) btn.addEventListener('click', function () {
                window.location.reload();
            });
        })();
    </script>
@endsection
