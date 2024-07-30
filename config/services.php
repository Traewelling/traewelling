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
        'domain'        => env('MASTODON_DOMAIN'),
        'client_id'     => env('MASTODON_ID'),
        'client_secret' => env('MASTODON_SECRET'),
        'redirect'      => env('MASTODON_REDIRECT'),
    ],

    'telegram' => [
        'admin' => [
            'active'  => env('TELEGRAM_ADMIN_ACTIVE', false),
            'chat_id' => env('TELEGRAM_ADMIN_CHAT_ID'),
            'token'   => env('TELEGRAM_ADMIN_TOKEN'),
        ]
    ]
];
