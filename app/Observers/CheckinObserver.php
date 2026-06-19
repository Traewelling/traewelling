<?php

declare(strict_types=1);

namespace App\Observers;

use App\Helpers\CacheKey;
use App\Models\Checkin;
use App\Services\Checkin\CheckinService;
use Carbon\Carbon;

class CheckinObserver
{
    public function __construct(private readonly CheckinService $checkinService) {}

    public function created(Checkin $checkin): void
    {
        $this->forgetIcsCache($checkin->user_id, $checkin->departure);
    }

    public function updated(Checkin $checkin): void
    {
        if ($checkin->isDirty(['origin', 'destination', 'departure', 'arrival', 'manual_departure', 'manual_arrival'])) {
            // if origin, destination, departure or arrival is changed, update duration
            $this->checkinService->calculateCheckinDuration($checkin->fresh());
        }

        if ($checkin->isDirty(['departure', 'manual_departure', 'origin', 'destination'])) {
            $this->forgetIcsCache($checkin->user_id, $checkin->departure);
            $original = $checkin->getOriginal('departure');
            if ($original !== null) {
                $this->forgetIcsCache($checkin->user_id, Carbon::parse($original));
            }
        }
    }

    public function deleted(Checkin $checkin): void
    {
        $this->forgetIcsCache($checkin->user_id, $checkin->departure);
    }

    private function forgetIcsCache(int $userId, ?Carbon $departure): void
    {
        if ($departure === null) {
            return;
        }
        CacheKey::forgetIcsUserMonthly($userId, $departure);
    }
}
