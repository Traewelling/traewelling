<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserCheckedIn;
use App\Jobs\ImportProviderPolyline;

class StatusCreateImportProviderPolylineListener
{
    public function handle(UserCheckedIn $event): void
    {
        ImportProviderPolyline::dispatch($event->status->checkin->trip);
    }
}
