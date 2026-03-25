<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mastodon' => [
        'domain' => env('MASTODON_DOMAIN'),
        'client_name' => env('MASTODON_APPNAME'), // TODO: check if this is a required value...? I don't think, we need this. ~@kris
        'client_id' => env('MASTODON_ID'),
        'client_secret' => env('MASTODON_SECRET'),
        'redirect' => env('MASTODON_REDIRECT', 'http://localhost:8000/callback/mastodon'),
        'timeout' => env('MASTODON_TIMEOUT_SECONDS', 2),
    ],

    'telegram' => [
        'admin' => [
            'token' => env('TELEGRAM_ADMIN_TOKEN'),
            'events_chat_id' => env('TELEGRAM_ADMIN_EVENTS_CHAT_ID'),
            'reports_chat_id' => env('TELEGRAM_ADMIN_REPORTS_CHAT_ID'),
        ],
    ],

    'matrix' => [
        'admin' => [
            'homeserver' => env('MATRIX_ADMIN_HOMESERVER'),
            'token' => env('MATRIX_ADMIN_TOKEN'),
            'events_room_id' => env('MATRIX_ADMIN_EVENTS_ROOM_ID'),
            'reports_room_id' => env('MATRIX_ADMIN_REPORTS_ROOM_ID'),
        ],
    ],

    // default user agent for HTTP requests to external services
    'user_agent' => env('USER_AGENT', 'traewelling (https://github.com/Traewelling/traewelling)'),

    'brouter' => [
        'url' => env('BROUTER_URL', 'https://brouter.de/brouter'),
        'timeout' => env('BROUTER_TIMEOUT_SECONDS', 30),
        'endpoint_tolerance_meters' => env('BROUTER_ENDPOINT_TOLERANCE_METERS', 200),
    ],
];
