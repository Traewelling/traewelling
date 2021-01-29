<?php

return [
    'post_social'       => env('POST_SOCIAL', true),

    # Twitter
    'twitter_redirect'  => env('TWITTER_REDIRECT'),
    'twitter_id'        => env('TWITTER_ID'),
    'twitter_secret'    => env('TWITTER_SECRET'),

    # Mastodon
    'mastodon_domain'   => env('MASTODON_DOMAIN'),
    'mastodon_id'       => env('MASTODON_ID'),
    'mastodon_secret'   => env('MASTODON_SECRET'),
    'mastodon_redirect' => env('MASTODON_REDIRECT'),
    'mastodon_appname'  => env('MASTODON_APPNAME'),

    # DB_REST
    'db_rest'           => env('DB_REST'),
    'flix_rest'         => env('FLIX_REST', 'https://1.flixbus.transport.rest/'),
];
