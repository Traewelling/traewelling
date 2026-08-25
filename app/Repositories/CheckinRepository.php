<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Dto\Internal\IcsExportStatus;
use App\Models\Checkin;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Throwable;

class CheckinRepository
{
    private const ORIGIN_STOPOVER_ALIAS = 'active_origin_stopover';

    private const DESTINATION_STOPOVER_ALIAS = 'active_destination_stopover';

    /**
     * How far behind the planned arrival a checkin may still be running.
     */
    private const REALTIME_ARRIVAL_BUFFER_HOURS = 6;

    public function whereCurrentlyActive(Builder $query): Builder
    {
        $maxJourneyHours = (int) config('trwl.max_journey_hours');
        $maxDelayHours = (int) config('trwl.max_delay_hours');

        $departure = self::effectiveDepartureExpression();
        $arrival = self::effectiveArrivalExpression();

        return $query
            ->leftJoin(
                'train_stopovers as ' . self::ORIGIN_STOPOVER_ALIAS,
                'train_checkins.origin_stopover_id',
                '=',
                self::ORIGIN_STOPOVER_ALIAS . '.id'
            )
            ->leftJoin(
                'train_stopovers as ' . self::DESTINATION_STOPOVER_ALIAS,
                'train_checkins.destination_stopover_id',
                '=',
                self::DESTINATION_STOPOVER_ALIAS . '.id'
            )
            ->whereBetween('train_checkins.departure', [
                now()->subHours($maxJourneyHours + $maxDelayHours),
                now()->addHours($maxDelayHours),
            ])
            ->where('train_checkins.arrival', '>', now()->subHours(self::REALTIME_ARRIVAL_BUFFER_HOURS))
            ->where($departure, '<=', now())
            ->where($departure, '>', now()->subHours($maxJourneyHours))
            ->where($arrival, '>', now());
    }

    /**
     * The departure Träwelling displays for a checkin: manual > realtime > planned.
     * Requires the joins added by {@see self::whereCurrentlyActive()}.
     */
    private static function effectiveDepartureExpression(): Expression
    {
        return DB::raw(sprintf(
            'COALESCE(train_checkins.manual_departure, %1$s.departure_real, %1$s.departure_planned, train_checkins.departure)',
            self::ORIGIN_STOPOVER_ALIAS
        ));
    }

    /**
     * The arrival Träwelling displays for a checkin: manual > realtime > planned.
     * Requires the joins added by {@see self::whereCurrentlyActive()}.
     */
    private static function effectiveArrivalExpression(): Expression
    {
        return DB::raw(sprintf(
            'COALESCE(train_checkins.manual_arrival, %1$s.arrival_real, %1$s.arrival_planned, train_checkins.arrival)',
            self::DESTINATION_STOPOVER_ALIAS
        ));
    }

    /**
     * Ids of all statuses whose checkin is running right now, regardless of visibility.
     * Queried on the checkins alone so the (departure, arrival, status_id) index can carry the whole filter.
     *
     * @return int[]
     */
    public function getActiveStatusIds(): array
    {
        $query = Checkin::query()->select('train_checkins.status_id');

        return $this->whereCurrentlyActive($query)->pluck('status_id')->toArray();
    }

    /**
     * The status of the journey the user is currently on, or null if they are not travelling.
     * If more than one checkin is running, the one that started last is returned.
     */
    public function getActiveStatusForUser(User $user): ?Status
    {
        $query = Status::query()
            ->join('train_checkins', 'statuses.id', '=', 'train_checkins.status_id')
            ->where('train_checkins.user_id', $user->id)
            ->with([
                'event',
                'likes',
                'mentions.mentioned',
                'client',
                'user',
                'createdByUser',
                'tags',
                'checkin.statusTags',
                'checkin.originStopover.station',
                'checkin.destinationStopover.station',
                'checkin.trip.stopovers.station',
                'checkin.trip.operator',
                'checkin.trip.motisSourceLicense',
            ])
            ->select('statuses.*')
            ->orderByDesc('train_checkins.departure');

        return $this->whereCurrentlyActive($query)->first();
    }

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
