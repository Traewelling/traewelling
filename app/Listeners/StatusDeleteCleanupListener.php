<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\StatusDeleteEvent;
use App\Jobs\CleanupUnusedTrip;

class StatusDeleteCleanupListener
{
    public function handle(StatusDeleteEvent $event): void
    {
        $checkin = $event->status->checkin;
        if ($checkin !== null) {
            CleanupUnusedTrip::dispatch($checkin->trip_id, $checkin->id);
        }
    }
}
