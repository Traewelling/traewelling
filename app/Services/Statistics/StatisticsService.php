<?php

declare(strict_types=1);

namespace App\Services\Statistics;

use App\Dto\Internal\GlobalCheckinStats;
use App\Models\User;
use App\Repositories\StatisticsRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class StatisticsService
{
    public function __construct(private readonly StatisticsRepository $repository) {}

    public function getGlobalStats(?Carbon $from = null, ?Carbon $until = null): GlobalCheckinStats
    {
        if ($from !== null && $until !== null && $from->isAfter($until)) {
            throw new InvalidArgumentException('from cannot be after until');
        }

        return $this->repository->globalStats($from, $until);
    }

    public function getTravelPurposes(User $user, Carbon $from, Carbon $until): Collection
    {
        [$from, $until] = $this->dayBounds($from, $until);

        return $this->repository->travelPurposes($user, $from, $until)
            ->map(static fn ($row) => [
                'name' => $row->reason,
                'count' => $row->count,
                'duration' => $row->duration,
            ]);
    }

    public function getTravelCategories(User $user, Carbon $from, Carbon $until, int $limit = 10): Collection
    {
        [$from, $until] = $this->dayBounds($from, $until);

        return $this->repository->travelCategories($user, $from, $until, $limit)
            ->map(static fn ($row) => [
                'name' => $row->name,
                'count' => $row->count,
                'duration' => $row->duration,
            ]);
    }

    public function getTripOperators(User $user, Carbon $from, Carbon $until, int $limit = 10): Collection
    {
        [$from, $until] = $this->dayBounds($from, $until);

        return $this->repository->tripOperators($user, $from, $until, $limit)
            ->map(static fn ($row) => [
                'name' => $row->name,
                'count' => $row->count,
                'duration' => $row->duration,
            ]);
    }

    public function getDailyTimeline(User $user, Carbon $from, Carbon $until): Collection
    {
        [$from, $until] = $this->dayBounds($from, $until);

        $dateList = collect();
        for ($date = $from->clone(); $date->isBefore($until); $date->addDay()) {
            $entry = collect();
            $entry->date = $date->clone();
            $entry->count = 0;
            $entry->duration = 0;
            $dateList->push($entry);
        }

        foreach ($this->repository->dailyTravelTime($user, $from, $until) as $row) {
            $obj = $dateList->first(fn ($item) => $item->date->isSameDay(Carbon::parse($row->date)));
            if ($obj) {
                $obj->count = (int) $row->count;
                $obj->duration = (int) $row->duration;
            }
        }

        return $dateList->sortBy('date');
    }

    public function getSummary(User $user, Carbon $from, Carbon $until): array
    {
        [$from, $until] = $this->dayBounds($from, $until);

        $aggregate = $this->repository->checkinAggregate($user, $from, $until);
        $longest = $this->repository->longestRide($user, $from, $until);
        $shortest = $this->repository->shortestRide($user, $from, $until);

        return [
            'total_checkins' => (int) ($aggregate->total_checkins ?? 0),
            'active_days' => (int) ($aggregate->active_days ?? 0),
            'total_distance_km' => round(($aggregate->total_distance_meters ?? 0) / 1000, 2),
            'mean_distance_km' => round(($aggregate->mean_distance_meters ?? 0) / 1000, 2),
            'longest_ride' => $this->toRideArray($longest),
            'shortest_ride' => $this->toRideArray($shortest),
        ];
    }

    public function getHistory(User $user): array
    {
        return [
            'yearly' => $this->repository->distanceByYear($user)
                ->map(static fn ($row) => [
                    'period' => (string) $row->period,
                    'period_type' => 'year',
                    'checkin_count' => (int) $row->checkin_count,
                    'distance_km' => round($row->total_distance_meters / 1000, 2),
                ]),
            'monthly' => $this->repository->distanceByMonth($user)
                ->map(static fn ($row) => [
                    'period' => $row->period,
                    'period_type' => 'month',
                    'checkin_count' => (int) $row->checkin_count,
                    'distance_km' => round($row->total_distance_meters / 1000, 2),
                ]),
            'weekly' => $this->repository->distanceByWeek($user)
                ->map(static fn ($row) => [
                    'period' => $row->period,
                    'period_type' => 'week',
                    'checkin_count' => (int) $row->checkin_count,
                    'distance_km' => round($row->total_distance_meters / 1000, 2),
                ]),
        ];
    }

    public function getFavorites(User $user, Carbon $from, Carbon $until): array
    {
        [$from, $until] = $this->dayBounds($from, $until);

        return [
            'stations' => $this->repository->favoriteStations($user, $from, $until)
                ->map(static fn ($row) => [
                    'station_id' => (int) $row->station_id,
                    'name' => $row->name,
                    'count' => (int) $row->visit_count,
                ]),
            'lines' => $this->repository->favoriteLines($user, $from, $until)
                ->map(static fn ($row) => [
                    'linename' => $row->linename,
                    'number' => $row->number,
                    'count' => (int) $row->count,
                    'distance_km' => round(($row->total_distance_meters ?? 0) / 1000, 2),
                ]),
            'routes' => $this->repository->favoriteRoutes($user, $from, $until)
                ->map(static fn ($row) => [
                    'origin_id' => (int) $row->origin_id,
                    'origin' => $row->origin_name,
                    'destination_id' => (int) $row->destination_id,
                    'destination' => $row->destination_name,
                    'count' => (int) $row->count,
                    'distance_km' => round(($row->total_distance_meters ?? 0) / 1000, 2),
                ]),
        ];
    }

    private function toRideArray(?object $ride): ?array
    {
        if ($ride === null) {
            return null;
        }

        return [
            'id' => $ride->id,
            'status_id' => $ride->status_id,
            'distance_km' => round($ride->distance / 1000, 2),
            'departure' => $ride->departure,
            'start' => $ride->start,
            'end' => $ride->end,
            'linename' => $ride->linename,
            'number' => $ride->number,
            'operator' => $ride->operator_name,
            'origin' => $ride->origin_name,
            'destination' => $ride->destination_name,
        ];
    }

    /** @return array{Carbon, Carbon} */
    private function dayBounds(Carbon $from, Carbon $until): array
    {
        $from = $from->clone()->startOfDay();
        $until = $until->clone()->endOfDay();

        if ($from->isAfter($until)) {
            throw new InvalidArgumentException('from cannot be after until');
        }

        return [$from, $until];
    }
}
