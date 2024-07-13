<?php

namespace App\Observers;

use App\Enum\Report\ReportableSubject;
use App\Enum\Report\ReportReason;
use App\Models\Trip;
use App\Repositories\ReportRepository;
use App\Services\ReportService;
use Illuminate\Support\Facades\Log;

class TripObserver
{
    public function created(Trip $trip): void {
        // check if trip is out of allowed types and create an admin report if so
        (new ReportService())->checkAndReport(
            $trip->linename,
            ReportableSubject::TRIP,
            $trip->id
        );
    }
}
