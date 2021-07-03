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

    'basis_points' => [
        'train' => [
            'tram'            => env('BASIS_POINTS_TRAIN_TRAM', 2),
            'bus'             => env('BASIS_POINTS_TRAIN_BUS', 2),
            'subway'          => env('BASIS_POINTS_TRAIN_SUBWAY', 2),
            'suburban'        => env('BASIS_POINTS_TRAIN_SUBURBAN', 3),
            'ferry'           => env('BASIS_POINTS_TRAIN_FERRY', 3),
            'regional'        => env('BASIS_POINTS_TRAIN_REGIONAL', 5),
            'regionalExp'     => env('BASIS_POINTS_TRAIN_REGIONALEXP', 6),
            'express'         => env('BASIS_POINTS_TRAIN_EXPRESS', 10),
            'national'        => env('BASIS_POINTS_TRAIN_NATIONAL', 10),
            'nationalExpress' => env('BASIS_POINTS_TRAIN_NATIONALEXPRESS', 10),
        ]
    ]
];
