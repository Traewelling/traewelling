<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Dto\Internal\IcsExportStatus;
use App\Models\Checkin;
use App\Models\User;
use Carbon\Carbon;
use Throwable;

class CheckinRepository
{
    /**
     * Returns the earliest and latest departure timestamp for a user's checkins,
     * or null for each if the user has no checkins.
     *
     * @return array{first: Carbon|null, last: Carbon|null}
     */
    public function getDepartureBoundsForUser(User $user): array
    {
        $row = Checkin::where('user_id', $user->id)
            ->selectRaw('MIN(departure) as first, MAX(departure) as last')
            ->first();

        return [
            'first' => $row->first !== null ? Carbon::parse($row->first) : null,
            'last' => $row->last !== null ? Carbon::parse($row->last) : null,
        ];
    }

    /**
     * Fetches all checkins for a user within a given month and maps them to IcsExportStatus DTOs.
     *
     * @return IcsExportStatus[]
     */
    public function getCheckinsAsIcsStatusForMonth(
        User $user,
        Carbon $month,
        int $limit,
        bool $useEmojis,
    ): array {
        $from = $month->copy()->startOfMonth()->startOfDay();
        $until = $month->copy()->endOfMonth()->endOfDay();

        $events = [];

        Checkin::with(['status.tags', 'originStopover.station', 'destinationStopover.station', 'trip'])
            ->where('user_id', $user->id)
            ->whereBetween('departure', [$from, $until])
            ->orderByDesc('departure')
            ->limit($limit)
            ->each(function (Checkin $checkin) use (&$events, $useEmojis): void {
                try {
                    $events[] = IcsExportStatus::fromCheckin($checkin, $useEmojis);
                } catch (Throwable $throwable) {
                    report($throwable);
                }
            });

        return $events;
    }
}
