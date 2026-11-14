@php
    $releasesUrl = config('app.links.releases', 'https://github.com/Traewelling/traewelling/releases');
    $mastodonUrl = config('app.links.mastodon', 'https://social.traewelling.org/@traewelling');
    $helpUrl = config('app.links.help', 'https://help.traewelling.de/');
    $refreshMs = (int) config('app.maintenance_refresh_ms', 30000);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ __('maintenance.title') }} | Träwelling</title>
    <link href="{{ asset('fonts/Nunito/Nunito.css') }}" rel="stylesheet">
    <link rel="shortcut icon" href="{{ asset('images/icons/touch-icon-vector.svg') }}">
    <style>
        :root {
            color-scheme: light dark;
            --primary: #c72730;
            --background: #f8fafc;
            --card: #ffffff;
            --text: #1f2937;
            --muted: #6b7280;
            --link: #3b71ca;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --primary: #811a0e;
                --background: oklch(0.21 0.009 264.318);
                --card: oklch(0.25 0.009 264.318);
                --text: #e5e7eb;
                --muted: #9ca3af;
                --link: #7aa2e3;
            }
        }

        body {
            margin: 0;
            font-family: 'Nunito', sans-serif;
            line-height: 1.5;
            background: var(--background);
            color: var(--text);
        }

        header {
            padding: 0.75rem 1rem;
            background: var(--primary);
            color: #ffffff;
            font-size: 1.25rem;
            font-weight: bold;
        }

        main {
            max-width: 720px;
            margin: 0 auto;
            padding: 2rem 1rem;
            text-align: center;
        }

        h1 {
            margin: 0 0 1rem;
            font-size: 5rem;
            font-weight: 300;
            line-height: 1;
        }

        h2 {
            font-size: 1.5rem;
        }

        h3 {
            margin-top: 0;
            font-size: 1.125rem;
        }

        .muted {
            color: var(--muted);
        }

        .card {
            margin: 1.5rem 0;
            padding: 1.25rem;
            border-radius: 0.5rem;
            background: var(--card);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            text-align: left;
        }

        ul {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        li + li {
            margin-top: 0.75rem;
        }

        li span {
            display: block;
            font-size: 0.875rem;
        }

        a {
            color: var(--link);
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<header>Träwelling</header>
<main>
    <h1>503</h1>
    <h2>{{ __('maintenance.title') }}</h2>
    <p class="muted">
        {{ __('maintenance.subtitle') }} {{ __('maintenance.try-later') }}<br><br>
        {{ __('maintenance.prolonged') }}
    </p>

    <div class="card">
        <h3>{{ __('maintenance.more-info') }}</h3>
        <ul>
            <li>
                <a href="{{ $releasesUrl }}" target="_blank" rel="noopener">{{ __('maintenance.release-notes') }}</a>
                <span class="muted">{{ __('maintenance.release-notes.desc') }}</span>
            </li>
            <li>
                <a href="{{ $mastodonUrl }}" target="_blank" rel="me noopener">{{ __('maintenance.follow') }}</a>
                <span class="muted">{{ __('maintenance.follow.desc') }}</span>
            </li>
            <li>
                <a href="{{ $helpUrl }}" target="_blank" rel="noopener">{{ __('maintenance.help-center') }}</a>
            </li>
        </ul>
    </div>

    <p class="muted">{{ __('maintenance.auto-refresh') }}</p>
</main>
<script>
    setInterval(function () {
        fetch(window.location.href, {method: 'HEAD', cache: 'no-store'})
            .then(function (response) {
                if (response.status !== 503) {
                    window.location.reload();
                }
            })
            .catch(function () {});
    }, {{ $refreshMs }});
</script>
</body>
</html>
