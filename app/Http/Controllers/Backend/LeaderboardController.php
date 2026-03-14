<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Helpers\CacheKey;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;
use stdClass;
use UnexpectedValueException;

class LeaderboardController extends Controller
{
    private const string CACHE_RETENTION_CONFIG_KEY = 'trwl.cache.leaderboard-retention-seconds';

    private int $ttl;

    public function __construct()
    {
        $this->ttl = config(self::CACHE_RETENTION_CONFIG_KEY, 5 * 60);
    }

    public function getCachedGlobalLeaderboard(): Collection
    {
        return Cache::remember(
            CacheKey::LEADERBOARD_GLOBAL_POINTS,
            $this->ttl,
            fn () => $this->getLeaderboard()
        )->filter(fn (stdClass $row) => Gate::allows('view', $row->user));
    }

    public function getCachedFriendsLeaderboard(): ?Collection
    {
        return auth()->check()
            ? Cache::remember(
                CacheKey::getFriendsLeaderboardKey(auth()->id()),
                $this->ttl,
                fn () => $this->getLeaderboard(onlyFollowings: true))
            : null;
    }

    public function getCachedDistanceLeaderboard(): Collection
    {
        return Cache::remember(
            CacheKey::LEADERBOARD_GLOBAL_DISTANCE,
            $this->ttl,
            fn () => $this->getLeaderboard(orderBy: 'distance')
        )->filter(fn (stdClass $row) => Gate::allows('view', $row->user));
    }

    private function getLeaderboard(
        string $orderBy = 'points',
        ?Carbon $since = null,
        ?Carbon $until = null,
        int $limit = 20,
        bool $onlyFollowings = false
    ): Collection {
        if (auth()->user()?->points_enabled === false) {
            return collect();
        }

        if ($since == null) {
            $since = now()->subWeek();
        }
        if ($until == null) {
            $until = now();
        }
        if ($since->isAfter($until)) {
            throw new InvalidArgumentException('since cannot be after until');
        }
        if (!in_array($orderBy, ['points', 'distance', 'duration', 'speed'])) {
            throw new InvalidArgumentException(
                'orderBy must be one of the following strings: points, distance, duration, speed'
            );
        }

        $sumDistance = 'SUM(train_checkins.distance)';
        $followIds = auth()->check() ? auth()->user()->follows->pluck('id') : collect();

        $query = DB::table('train_checkins')
            ->join('users', 'train_checkins.user_id', '=', 'users.id')
            ->where('train_checkins.departure', '>=', $since->utc()->format('Y-m-d H:i:s'))
            ->where('train_checkins.departure', '<=', $until->utc()->format('Y-m-d H:i:s'))
            ->where(fn (Builder $q) => $this->applyPrivacyFilter($q, $followIds))
            ->groupBy('train_checkins.user_id')
            ->select([
                'train_checkins.user_id',
                DB::raw('SUM(train_checkins.points) AS points'),
                DB::raw($sumDistance . ' AS distance'),
                DB::raw(self::getDurationSelector() . ' AS duration'),
                DB::raw($sumDistance . ' / (' . self::getDurationSelector() . ' / 60) AS speed'),
            ])
            ->orderByDesc($orderBy)
            ->limit($limit);

        if ($onlyFollowings && auth()->check()) {
            $query->where(function (Builder $q) use ($followIds): void {
                $q->whereIn('train_checkins.user_id', $followIds)
                    ->orWhere('train_checkins.user_id', auth()->id());
            });
        }

        $data = $query->get();

        // Fetch user models in ONE query and map it to the collection
        $userCache = User::with(['blockedByUsers', 'blockedUsers'])
            ->whereIn('id', $data->pluck('user_id'))
            ->get();

        return $data->map(function ($row) use ($userCache) {
            $row->user = $userCache->where('id', $row->user_id)->first();

            return $row;
        });
    }

    public static function getMonthlyLeaderboard(Carbon $date): Collection
    {
        if (auth()->user()?->points_enabled === false) {
            return collect();
        }

        $followIds = auth()->check() ? auth()->user()->follows->pluck('id') : collect();

        $data = DB::table('train_checkins')
            ->join('users', 'train_checkins.user_id', '=', 'users.id')
            ->where('train_checkins.departure', '>=', $date->clone()->firstOfMonth()->utc()->format('Y-m-d H:i:s'))
            ->where('train_checkins.departure', '<=', $date->clone()->lastOfMonth()->endOfDay()->utc()->format('Y-m-d H:i:s'))
            ->where(fn (Builder $q) => self::applyPrivacyFilter($q, $followIds))
            ->select([
                'train_checkins.user_id',
                DB::raw('SUM(train_checkins.points) AS points'),
                DB::raw('SUM(train_checkins.distance) AS distance'),
                DB::raw(self::getDurationSelector() . ' AS duration'),
                DB::raw('SUM(train_checkins.distance) / (' . self::getDurationSelector() . ' / 60) AS speed'),
            ])
            ->groupBy('train_checkins.user_id')
            ->orderByDesc('points')
            ->limit(100)
            ->get();

        // Fetch user models in ONE query and map it to the collection
        $userCache = User::with(['blockedByUsers', 'blockedUsers'])
            ->whereIn('id', $data->pluck('user_id'))
            ->get();

        return $data->map(function ($row) use ($userCache) {
            $row->user = $userCache->where('id', $row->user_id)->first();

            return $row;
        });
    }

    private static function applyPrivacyFilter(Builder $query, Collection $followIds): void
    {
        $query->where('users.private_profile', 0);
        if (auth()->check()) {
            $query->orWhereIn('users.id', $followIds)
                ->orWhere('users.id', auth()->id());
        }
    }

    private static function getDurationSelector(): string
    {
        $driver = config('database.default');

        if ($driver === 'mysql' || $driver === 'mariadb') {
            return 'SUM(TIMESTAMPDIFF(MINUTE, train_checkins.departure, train_checkins.arrival))';
        }

        if ($driver === 'sqlite') {
            // Sorry for this disgusting code. But we test with SQLite.
            // There are different functions than with MySQL/MariaDB.
            return 'SUM((JULIANDAY(train_checkins.arrival) - JULIANDAY(train_checkins.departure)) * 1440)';
        }

        throw new UnexpectedValueException('Driver not supported');
    }
}
