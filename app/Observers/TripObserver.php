<?php

namespace App\Observers;

use App\Enum\Report\ReportableSubject;
use App\Enum\TripSource;
use App\Models\Trip;
use App\Services\ReportService;

class TripObserver
{
    /**
     * Handle the Trip "saving" event.
     * Remove duplicate journey number from linename if it exists.
     */
    public function saving(Trip $trip): void {
        if (empty($trip->journey_number) || empty($trip->linename)) {
            return;
        }

        $journeyNumber = $trip->journey_number;
        $linename      = $trip->linename;

        $pattern = '/\s*\(' . preg_quote($journeyNumber, '/') . '\)$/';

        if (preg_match($pattern, $linename)) {
            $trip->linename = trim(preg_replace($pattern, '', $linename));
        }
    }

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
