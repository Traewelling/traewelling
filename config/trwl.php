<?php

return [
    'post_social'           => env('POST_SOCIAL', false),

    # Twitter
    'twitter_redirect'      => env('TWITTER_REDIRECT'),
    'twitter_id'            => env('TWITTER_ID'),
    'twitter_secret'        => env('TWITTER_SECRET'),
    'twitter_oauth1_id'     => env('TWITTER_OAUTH1_ID'),
    'twitter_oauth1_secret' => env('TWITTER_OAUTH1_SECRET'),

    # Mastodon
    'mastodon_domain'       => env('MASTODON_DOMAIN'),
    'mastodon_id'           => env('MASTODON_ID'),
    'mastodon_secret'       => env('MASTODON_SECRET'),
    'mastodon_redirect'     => env('MASTODON_REDIRECT'),
    'mastodon_appname'      => env('MASTODON_APPNAME'),

    # DB_REST
    'db_rest'               => env('DB_REST', 'https://v5.db.transport.rest/'),
    'db_rest_timeout'       => env('DB_REST_TIMEOUT', 10),
    'base_points'           => [
        'train' => [
            'tram'            => env('BASE_POINTS_TRAIN_TRAM', 2),
            'bus'             => env('BASE_POINTS_TRAIN_BUS', 2),
            'subway'          => env('BASE_POINTS_TRAIN_SUBWAY', 2),
            'suburban'        => env('BASE_POINTS_TRAIN_SUBURBAN', 3),
            'ferry'           => env('BASE_POINTS_TRAIN_FERRY', 3),
            'regional'        => env('BASE_POINTS_TRAIN_REGIONAL', 5),
            'regionalExp'     => env('BASE_POINTS_TRAIN_REGIONALEXP', 6),
            'express'         => env('BASE_POINTS_TRAIN_EXPRESS', 10),
            'national'        => env('BASE_POINTS_TRAIN_NATIONAL', 10),
            'nationalExpress' => env('BASE_POINTS_TRAIN_NATIONALEXPRESS', 10),
        ]
    ],
    'refresh'               => [
        'max_trips_per_minute' => env('REFRESH_TRIPS_PER_MINUTE', 1)
    ],
    'cache'                 => [
        'global-statistics-retention-seconds' => env('GLOBAL_STATISTICS_CACHE_RETENTION_SECONDS', 60 * 60),
        'leaderboard-retention-seconds'       => env('LEADERBOARD_CACHE_RETENTION_SECONDS', 5 * 60)
    ],
    'year_in_review_active' => env('YEAR_IN_REVIEW_ACTIVE', false),
    'webhooks_active'       => env('WEBHOOKS_ACTIVE', false),
];
