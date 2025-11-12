<?php

return [
    'post_social'               => env('POST_SOCIAL', false),

    # ReRouting
    'distance_deviation' => [
        'cooldown_error_percent' => (float) env('DISTANCE_DEVIATION_COOLDOWN_ERROR_PERCENT', 10),
        'cooldown_seconds' => (int) env('DISTANCE_DEVIATION_COOLDOWN_SECONDS', 60),
        'threshold_percent' => (float) env('DISTANCE_DEVIATION_THRESHOLD_PERCENT', 20),
        'threshold_percent_medium' => (float) env('DISTANCE_DEVIATION_THRESHOLD_PERCENT_MEDIUM', 25),
        'threshold_percent_short' => (float) env('DISTANCE_DEVIATION_THRESHOLD_PERCENT_SHORT', 30),
        'threshold_percent_shortest' => (float) env('DISTANCE_DEVIATION_THRESHOLD_PERCENT_SHORTEST', 40),
        'shortest_distance' => (int) env('DISTANCE_DEVIATION_SHORTEST_DISTANCE', 400),
        'short_distance' => (int) env('DISTANCE_DEVIATION_SHORT_DISTANCE', 2000),
        'medium_distance' => (int) env('DISTANCE_DEVIATION_MEDIUM_DISTANCE', 15000),
    ],

    # Polyline
    'polyline_storage_path'     => env('POLYLINE_STORAGE_PATH', 'polylines'),
    'polyline_storage_driver'   => env('POLYLINE_STORAGE_DRIVER', 'local'),
    'polyline_clear_after_copy' => env('POLYLINE_CLEAR_AFTER_COPY', false),

    # DB_REST
    'db_rest'                   => env('DB_REST', 'https://v5.db.transport.rest/'),
    'db_rest_timeout'           => env('DB_REST_TIMEOUT', 3),

    'data_provider' => env('DATA_PROVIDER', 'transitous'),

    'motis'             => [
        'radius'          => (int) env('MOTIS_RADIUS', 200),
        'nearby_radius'   => (int) env('MOTIS_NEARBY_RADIUS', 200),
        'results'         => (int) env('MOTIS_RESULTS', 50),
        'filter_licenses' => (bool) env('MOTIS_FILTER_LICENSES', false),
    ],

    # Points
    'base_points'       => [
        'time_window' => [
            # time windows before and after a journey to get points
            'good_enough' => [
                'before' => (int) env('GOOD_ENOUGH_POINTS_MIN_BEFORE', 60),
                'after'  => (int) env('GOOD_ENOUGH_POINTS_MIN_AFTER', 60),
            ],
            'in_time'     => [
                'before' => (int) env('FULL_POINTS_MIN_BEFORE', 20),
                'after'  => (int) env('FULL_POINTS_MIN_AFTER', 10),
            ],
        ],
        'train'       => [
            'tram'            => env('BASE_POINTS_TRAIN_TRAM', 2),
            'bus'             => env('BASE_POINTS_TRAIN_BUS', 2),
            'subway'          => env('BASE_POINTS_TRAIN_SUBWAY', 2),
            'suburban'        => env('BASE_POINTS_TRAIN_SUBURBAN', 3),
            'ferry'           => env('BASE_POINTS_TRAIN_FERRY', 3),
            'regional'        => env('BASE_POINTS_TRAIN_REGIONAL', 6),
            'regionalExp'     => env('BASE_POINTS_TRAIN_REGIONALEXP', 8),
            'national'        => env('BASE_POINTS_TRAIN_NATIONAL', 8),
            'nationalExpress' => env('BASE_POINTS_TRAIN_NATIONALEXPRESS', 10),
        ]
    ],
    'refresh'           => [
        'max_trips_per_minute' => env('REFRESH_TRIPS_PER_MINUTE', 1)
    ],
    'cache'             => [
        'global-statistics-retention-seconds' => env('GLOBAL_STATISTICS_CACHE_RETENTION_SECONDS', 60 * 60),
        'leaderboard-retention-seconds'       => env('LEADERBOARD_CACHE_RETENTION_SECONDS', 5 * 60),
        'data_provider'                       => env('DATA_PROVIDER_CACHE', false),
    ],
    'year_in_review'    => [
        'alert'     => env('YEAR_IN_REVIEW_ALERT', false),
        'backend'   => env('YEAR_IN_REVIEW_BACKEND', false),
        'scheduler' => env('YEAR_IN_REVIEW_SCHEDULER', false),
    ],
    'webhooks_active'   => env('WEBHOOKS_ACTIVE', false),
    'webfinger_active'  => env('WEBFINGER_ACTIVE', true),
    'max_journey_hours' => (int) env('MAX_JOURNEY_HOURS', 48),
    'max_delay_hours'   => (int) env('MAX_DELAY_HOURS', 24),

    # A/B Testing
    'ab_testing'        => [
        'gdpr_export' => env('AB_TESTING_GDPR_EXPORT', false),
    ],

    'gdpr_export' => [
        'days'    => env('GDPR_EXPORT_DAYS', 14),
        'timeout' => env('GDPR_EXPORT_TIMEOUT', 60 * 60 * 24),
        'tries'   => env('GDPR_EXPORT_TRIES', 3),
    ],
];
