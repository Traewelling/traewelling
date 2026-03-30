<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\DeleteFollowRequestNotifications;
use App\Models\FollowRequest;

class FollowRequestObserver
{
    public function deleted(FollowRequest $followRequest): void
    {
        DeleteFollowRequestNotifications::dispatch($followRequest->id, $followRequest->follow_id);
    }
}
