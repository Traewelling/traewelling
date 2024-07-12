<?php

namespace App\Observers;

use App\Enum\Report\ReportableSubject;
use App\Enum\Report\ReportReason;
use App\Models\Trip;
use App\Services\ReportService;
use Illuminate\Support\Facades\Log;

class TripObserver
{
    public function created(Trip $trip): void {
        // check if trip is out of allowed types and create an admin report if so
        $triggerWords = ['auto', 'fuss', 'fuß', 'fahrrad', 'foot', 'car', 'bike'];
        foreach ($triggerWords as $triggerWord) {
            if (str_contains(strtolower($trip->linename), $triggerWord)) {
                Log::info("Automatically reported trip {$trip->id}: The trip is inappropriate because it contains the word " . $triggerWord . '".');
                ReportService::createReport(
                    subjectType: ReportableSubject::TRIP,
                    subjectId:   $trip->id,
                    reason:      ReportReason::INAPPROPRIATE,
                    description: 'Automatically reported trip: The trip is inappropriate because it contains the word "' . $triggerWord . '".',
                );
            }
        }
    }

}
