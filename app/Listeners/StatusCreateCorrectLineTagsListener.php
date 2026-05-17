<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserCheckedIn;
use App\Jobs\CorrectLineTagsFromStationboard;

class StatusCreateCorrectLineTagsListener
{
    public function handle(UserCheckedIn $event): void
    {
        CorrectLineTagsFromStationboard::dispatch($event->status);
    }
}
