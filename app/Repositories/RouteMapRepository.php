<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Dto\RouteMap\RouteMapFilterDto;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RouteMapRepository
{
    private static function primarySortKey(string $alias): string
    {
        return "COALESCE($alias.arrival_planned, $alias.departure_planned)";
    }

    private static function secondarySortKey(string $alias): string
    {
        return "COALESCE($alias.departure_planned, $alias.arrival_planned)";
    }

    private static function isAtOrAfter(string $alias, string $boundary): string
    {
        return sprintf(
            '(%s > %s OR (%s = %s AND %s >= %s))',
            self::primarySortKey($alias), self::primarySortKey($boundary),
            self::primarySortKey($alias), self::primarySortKey($boundary),
            self::secondarySortKey($alias), self::secondarySortKey($boundary),
        );
    }

    private static function isAtOrBefore(string $alias, string $boundary): string
    {
        return sprintf(
            '(%s < %s OR (%s = %s AND %s <= %s))',
            self::primarySortKey($alias), self::primarySortKey($boundary),
            self::primarySortKey($alias), self::primarySortKey($boundary),
            self::secondarySortKey($alias), self::secondarySortKey($boundary),
        );
    }

    private function legs(User $user, RouteMapFilterDto $filter): Builder
    {
        $query = DB::table('train_checkins as c')
            ->join('hafas_trips as t', 't.trip_id', '=', 'c.trip_id')
            ->join('train_stopovers as o', 'o.id', '=', 'c.origin_stopover_id')
            ->join('train_stopovers as d', 'd.id', '=', 'c.destination_stopover_id')
            ->join('train_stopovers as s', static function (JoinClause $join): void {
                $join->on('s.trip_id', '=', 'c.trip_id')
                    ->whereRaw(self::isAtOrAfter('s', 'o'))
                    ->whereRaw(self::isAtOrBefore('s', 'd'));
            })
            ->where('c.user_id', '=', $user->id)
            ->select([
                's.route_segment_id',
                's.train_station_id as from_station_id',
                DB::raw(sprintf(
                    'LEAD(s.train_station_id) OVER (PARTITION BY c.id ORDER BY %s, %s) as to_station_id',
                    self::primarySortKey('s'),
                    self::secondarySortKey('s'),
                )),
                't.category',
            ]);

        if ($filter->from !== null) {
            $query->where('c.departure', '>=', $filter->from);
        }

        if ($filter->until !== null) {
            $query->where('c.departure', '<=', $filter->until);
        }

        if ($filter->travelTypes !== []) {
            $query->whereIn('t.category', array_map(static fn ($type) => $type->value, $filter->travelTypes));
        }

        if ($filter->travelPurposes !== []) {
            $query->join('statuses as st', 'st.id', '=', 'c.status_id')
                ->whereIn('st.business', array_map(static fn ($purpose) => $purpose->value, $filter->travelPurposes));
        }

        return $query;
    }

    /**
     * Distinct stretches the user has travelled.
     *
     * @return Collection<int, object{route_segment_id: string|null, from_station_id: int, to_station_id: int, categories: string}>
     */
    public function getTravelledStretches(User $user, RouteMapFilterDto $filter): Collection
    {
        $query = DB::query()
            ->fromSub($this->legs($user, $filter), 'legs')
            // The destination stopover has no following stop within the check-in and therefore no leg.
            ->whereNotNull('legs.to_station_id')
            ->groupBy('legs.route_segment_id', 'legs.from_station_id', 'legs.to_station_id')
            ->orderBy('legs.route_segment_id')
            ->orderBy('legs.from_station_id')
            ->orderBy('legs.to_station_id')
            ->select([
                'legs.route_segment_id',
                'legs.from_station_id',
                'legs.to_station_id',
                DB::raw('GROUP_CONCAT(DISTINCT legs.category) as categories'),
            ]);

        if (!$filter->includeApproximated) {
            $query->whereNotNull('legs.route_segment_id');
        }

        return $query->get();
    }
}
