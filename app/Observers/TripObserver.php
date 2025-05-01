<?php

namespace App\Observers;

use App\Enum\Report\ReportableSubject;
use App\Enum\TripSource;
use App\Models\Trip;
use App\Services\ReportService;

class TripObserver
{
    public function created(Trip $trip): void {
        // check if trip is out of allowed types and create an admin report if so
        if ($trip->source === TripSource::USER) {
            (new ReportService())->checkAndReport(
                $trip->linename,
                ReportableSubject::TRIP,
                $trip->id
            );
        }
    }
}
