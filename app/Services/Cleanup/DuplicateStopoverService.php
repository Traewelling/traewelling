<?php

declare(strict_types=1);

namespace App\Services\Cleanup;

use App\Dto\Coordinate;
use App\Dto\DuplicateStopoverPair;
use App\Jobs\RefreshPolyline;
use App\Models\Stopover;
use App\Models\Trip;
use App\Services\GeoService;
use Generator;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Detects and repairs a historic data error where real-time updates added a
 * second stopover for the same physical stop to a trip: two stopovers with
 * identical planned times whose stations sit at (almost) the same location, one
 * created later than the other. The later one is the spurious duplicate.
 */
class DuplicateStopoverService
{
    /** Number of trips whose stopovers are grouped in one query. */
    private const int TRIP_CHUNK_SIZE = 2000;

    public function __construct(private readonly GeoService $geoService) {}

    public function findDuplicates(int $maxMeters, ?int $limit = null): Generator
    {
        $yielded = 0;
        foreach ($this->collidingSlots() as $slot) {
            foreach ($this->duplicatesInSlot($slot->trip_id, $slot->arrival_planned, $slot->departure_planned, $maxMeters) as $pair) {
                yield $pair;
                if ($limit !== null && ++$yielded >= $limit) {
                    return;
                }
            }
        }
    }

    private function collidingSlots(): Generator
    {
        $lastTripKey = 0;
        do {
            $trips = Trip::query()
                ->select('id', 'trip_id')
                ->where('id', '>', $lastTripKey)
                ->orderBy('id')
                ->limit(self::TRIP_CHUNK_SIZE)
                ->get();

            if ($trips->isEmpty()) {
                return;
            }
            $lastTripKey = $trips->last()->id;

            yield from Stopover::query()
                ->selectRaw('trip_id, arrival_planned, departure_planned')
                ->whereIn('trip_id', $trips->pluck('trip_id'))
                ->groupBy('trip_id', 'arrival_planned', 'departure_planned')
                ->havingRaw('COUNT(*) > 1')
                ->havingRaw('MIN(created_at) <> MAX(created_at)')
                ->get();
        } while (true);
    }

    public function checkinReferenceCount(Stopover $stopover): int
    {
        return DB::table('train_checkins')
            ->where('origin_stopover_id', $stopover->id)
            ->orWhere('destination_stopover_id', $stopover->id)
            ->count();
    }

    /**
     * Repoints any check-ins from the duplicate to the kept stopover and deletes
     * the duplicate. Returns the number of check-in references repointed.
     */
    public function fix(DuplicateStopoverPair $pair): ?int
    {
        try {
            return DB::transaction(function () use ($pair): int {
                $repointed = DB::table('train_checkins')
                    ->where('origin_stopover_id', $pair->duplicate->id)
                    ->update(['origin_stopover_id' => $pair->keeper->id]);
                $repointed += DB::table('train_checkins')
                    ->where('destination_stopover_id', $pair->duplicate->id)
                    ->update(['destination_stopover_id' => $pair->keeper->id]);

                $pair->duplicate->delete();

                return $repointed;
            });
        } catch (UniqueConstraintViolationException) {
            return null;
        }
    }

    /**
     * Rebuilds route segments (and status distances) for the given trips after
     * duplicates were removed. The existing segment assignments are cleared
     * first because RefreshPolyline only recomputes stops without a segment, so
     * the stale legs around a deleted stopover would otherwise be kept.
     *
     * @param  iterable<string>  $tripIds
     */
    public function refreshAffectedTrips(iterable $tripIds): void
    {
        Trip::whereIn('trip_id', $tripIds)
            ->get()
            ->each(function (Trip $trip): void {
                Stopover::where('trip_id', $trip->trip_id)->update(['route_segment_id' => null]);
                RefreshPolyline::dispatch($trip);
            });
    }

    /**
     * Clusters the stopovers of one time slot by proximity: the earliest-created
     * member of a cluster is the keeper, every later member within range a
     * duplicate of it.
     *
     * @return DuplicateStopoverPair[]
     */
    private function duplicatesInSlot(string $tripId, ?Carbon $arrivalPlanned, ?Carbon $departurePlanned, int $maxMeters): array
    {
        $members = Stopover::query()
            ->where('trip_id', $tripId)
            ->when($arrivalPlanned === null,
                fn ($query) => $query->whereNull('arrival_planned'),
                fn ($query) => $query->where('arrival_planned', $arrivalPlanned))
            ->when($departurePlanned === null,
                fn ($query) => $query->whereNull('departure_planned'),
                fn ($query) => $query->where('departure_planned', $departurePlanned))
            ->with('station:id,name,latitude,longitude')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        /** @var Stopover[] $keepers */
        $keepers = [];
        $pairs = [];
        foreach ($members as $member) {
            $keeper = null;
            foreach ($keepers as $candidate) {
                if ($this->sameStop($candidate, $member, $maxMeters)) {
                    $keeper = $candidate;
                    break;
                }
            }

            if ($keeper === null) {
                $keepers[] = $member;
            } else {
                $pairs[] = new DuplicateStopoverPair($member, $keeper);
            }
        }

        return $pairs;
    }

    private function sameStop(Stopover $a, Stopover $b, int $maxMeters): bool
    {
        $coordinateA = $this->coordinate($a);
        $coordinateB = $this->coordinate($b);
        if ($coordinateA === null || $coordinateB === null) {
            return false;
        }

        return $this->geoService->getDistance($coordinateA, $coordinateB) <= $maxMeters;
    }

    private function coordinate(Stopover $stopover): ?Coordinate
    {
        $station = $stopover->station;
        if ($station === null || $station->latitude === null || $station->longitude === null) {
            return null;
        }

        return new Coordinate((float) $station->latitude, (float) $station->longitude);
    }
}
