<?php

namespace App\Observers;

use App\Models\Trip;

class TripObserver
{
    /**
     * Handle the Trip "saving" event.
     * Remove duplicate journey number from linename if it exists.
     */
    public function saving(Trip $trip): void
    {
        if (empty($trip->journey_number) || empty($trip->linename)) {
            return;
        }

        $journeyNumber = $trip->journey_number;
        $linename = $trip->linename;

        $pattern = '/\s*\(' . preg_quote($journeyNumber, '/') . '\)$/';

        if (preg_match($pattern, $linename)) {
            $trip->linename = trim(preg_replace($pattern, '', $linename));
        }
    }
}
