<?php

declare(strict_types=1);

return [
    'status_like' => [
        'max_attempts' => env('STATUS_LIKE_MAX_ATTEMPTS', 20),
        'decay_minutes' => env('STATUS_LIKE_DECAY_MINUTES', 10),
    ],
];
