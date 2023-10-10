<?php

return [
    'post_social'              => env('POST_SOCIAL', false),

    # Mastodon
    'mastodon_domain'          => env('MASTODON_DOMAIN'),
    'mastodon_id'              => env('MASTODON_ID'),
    'mastodon_secret'          => env('MASTODON_SECRET'),
    'mastodon_redirect'        => env('MASTODON_REDIRECT'),
    'mastodon_appname'         => env('MASTODON_APPNAME'),
    'mastodon_timeout_seconds' => env("MASTODON_TIMEOUT_SECONDS", 5),

    # Brouter
    'brouter_url'              => env('BROUTER_URL', 'https://brouter.de/'),
    'brouter_timeout'          => env('BROUTER_TIMEOUT', 10),

    # DB_REST
    'db_rest'                  => env('DB_REST', 'https://v5.db.transport.rest/'),
    'db_rest_timeout'          => env('DB_REST_TIMEOUT', 10),

    # Points
    'base_points'              => [
        'time_window' => [
            # time windows before and after a journey to get points
            'good_enough' => [
                'before' => env('GOOD_ENOUGH_POINTS_MIN_BEFORE', 60),
                'after'  => env('GOOD_ENOUGH_POINTS_MIN_AFTER', 60),
            ],
            'in_time'     => [
                'before' => env('FULL_POINTS_MIN_BEFORE', 20),
                'after'  => env('FULL_POINTS_MIN_AFTER', 10),
            ],
        ],
        'train'       => [
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
    'refresh'                  => [
        'max_trips_per_minute' => env('REFRESH_TRIPS_PER_MINUTE', 1)
    ],
    'cache'                    => [
        'global-statistics-retention-seconds' => env('GLOBAL_STATISTICS_CACHE_RETENTION_SECONDS', 60 * 60),
        'leaderboard-retention-seconds'       => env('LEADERBOARD_CACHE_RETENTION_SECONDS', 5 * 60)
    ],
    'year_in_review_active'    => env('YEAR_IN_REVIEW_ACTIVE', false),
    'webhooks_active'          => env('WEBHOOKS_ACTIVE', false),
];
