<?php

namespace App\Providers;

use App\Dto\Internal\GlobalCheckinStats;
use App\Helpers\CacheKey;
use App\Helpers\HCK;
use App\Http\Controllers\Backend\StatisticController as StatisticBackend;
use App\Models\PolyLine;
use App\Models\Station;
use App\Models\StationIdentifier;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\Factory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use romanzipp\QueueMonitor\Enums\MonitorStatus;
use Spatie\Prometheus\Facades\Prometheus;

const PROM_JOB_SCRAPER_SEPARATOR = '-PROM-JOB-SCRAPER-SEPARATOR-';

/** Cache TTL for slow Prometheus metrics (10 minutes) */
const PROM_CACHE_TTL = 600;

/** Cache TTL for frequently-changing metrics like en-route counts (2 minutes) */
const PROM_CACHE_TTL_SHORT = 120;

class PrometheusServiceProvider extends ServiceProvider
{
    /** HTTP status codes tracked by the api_response_codes metric. */
    private const array API_STATUS_CODES = [
        200, 201, 202, 204,
        301, 302, 304,
        400, 401, 403, 404, 405, 406, 408, 409, 410, 422, 429,
        500, 501, 503,
    ];

    /**
     * The queue manager instance.
     *
     * @var Factory
     */
    protected $queueFactoryManager;

    public function boot(Factory $queueFactoryManager): void
    {
        $this->queueFactoryManager = $queueFactoryManager;
    }

    public function register(): void
    {
        $this->registerGlobalStats();
        $this->metaDataStats();
        $this->queueMetrics();
        $this->hafasMetrics();
        $this->oAuthMetrics();
        $this->apiMetrics();

        Prometheus::addGauge('absent_webhooks_deleted')
            ->helpText('How many webhooks were responded with Gone and were thus deleted from our side?')
            ->value(fn () => Cache::get(CacheKey::WEBHOOK_ABSENT, 0));

        Prometheus::addGauge('is_maintenance_mode_active')
            ->helpText('Is the Laravel Maintenance Mode active right now?')
            ->value($this->app->maintenanceMode()->active());
    }

    public static function getJobsByDisplayName(string $tableName): array
    {
        // Use SQL JSON extraction instead of fetching all payload columns to PHP
        // and decoding them one by one.
        $jsonExtract = DB::getDriverName() === 'sqlite'
            ? "json_extract(payload, '$.displayName')"
            : "JSON_UNQUOTE(JSON_EXTRACT(payload, '$.displayName'))";

        return DB::table($tableName)
            ->selectRaw("count(*) AS total, queue, {$jsonExtract} AS display_name")
            ->groupBy('queue', 'display_name')
            ->get()
            ->map(fn ($row) => [$row->total, [$row->display_name, $row->queue]])
            ->toArray();
    }

    private function getHafasByType(array $getFailures): array
    {
        $values = [];
        foreach ($getFailures as $key => $name) {
            $values[$name] = Cache::get($key, 0);
        }

        return array_map(fn ($value, $key) => [$value, [$key]], $values, array_keys($values));
    }

    private function getGlobalStats(): GlobalCheckinStats
    {
        $from = Carbon::now()->subWeeks(4);
        $until = Carbon::now();

        /** @var GlobalCheckinStats $globalStats */
        return Cache::remember(
            key: CacheKey::getGlobalStatsKey($from, $until),
            ttl: config('trwl.cache.global-statistics-retention-seconds'), // 1 hour
            callback: static fn () => StatisticBackend::getGlobalCheckInStats($from, $until)
        );
    }

    private function registerGlobalStats(): void
    {
        Prometheus::addGauge('Active Users count')
            ->helpText('How many users have checked in in the last 4 weeks?')
            ->value(function () {
                return $this->getGlobalStats()->userCount;
            });

        Prometheus::addGauge('Global Distance')
            ->helpText('How many meters have been travelled in the last 4 weeks?')
            ->value(function () {
                return $this->getGlobalStats()->distance;
            });

        Prometheus::addGauge('Global Duration')
            ->helpText('How many minutes have been travelled in the last 4 weeks?')
            ->value(function () {
                return $this->getGlobalStats()->duration;
            });
    }

    public function metaDataStats(): void
    {
        Prometheus::addGauge('Stations count')
            ->helpText('How many stations exist in the database?')
            ->value(function () {
                return Cache::remember('prom_station_count', PROM_CACHE_TTL, fn () => Station::count());
            });

        Prometheus::addGauge('Station identifiers count')
            ->helpText('How many station identifiers exist in the database?')
            ->value(function () {
                return Cache::remember('prom_station_identifier_count', PROM_CACHE_TTL, fn () => StationIdentifier::count());
            });

        Prometheus::addGauge('Users count')
            ->helpText('How many users are registered on the website?')
            ->label('state')
            ->value(function () {
                return [
                    [Cache::get(CacheKey::USER_CREATED, 0), ['created']],
                    [Cache::get(CacheKey::USER_DELETED, 0), ['deleted']],
                ];
            });

        Prometheus::addGauge('Status count')
            ->helpText('How many statuses are posted on the website?')
            ->label('state')
            ->value(function () {
                return [
                    [Cache::get(CacheKey::STATUS_CREATED, 0), ['created']],
                    [Cache::get(CacheKey::STATUS_DELETED, 0), ['deleted']],
                ];
            });

        Prometheus::addGauge('Hafas Trips count')
            ->helpText('How many hafas trips are posted grouped by operator and mode of transport?')
            ->labels(['operator', 'category'])
            ->value(function () {
                return Cache::remember('prom_trips_by_operator_category', PROM_CACHE_TTL, function () {
                    return Trip::leftJoin('operators', 'hafas_trips.operator_id', '=', 'operators.id')
                        ->groupBy('hafas_trips.operator_id', 'hafas_trips.category')
                        ->selectRaw('count(*) AS total, MAX(operators.name) AS operator_name, hafas_trips.category')
                        ->get()
                        ->map(fn ($item) => [$item->total, [$item->operator_name, $item->category]])
                        ->toArray();
                });
            });

        Prometheus::addGauge('Trip Source count')
            ->helpText('How many hafas trips are posted grouped by source?')
            ->label('source')
            ->value(function () {
                return Cache::remember('prom_trips_by_source', PROM_CACHE_TTL, function () {
                    return Trip::groupBy('source')
                        ->selectRaw('count(*) AS total, source')
                        ->get()
                        ->map(fn ($item) => [$item->total, [$item->source?->value]])
                        ->toArray();
                });
            });

        Prometheus::addGauge('Polylines count')
            ->helpText('How many polylines are saved grouped by source?')
            ->labels(['source'])
            ->value(function () {
                return Cache::remember('prom_polylines_by_source', PROM_CACHE_TTL, function () {
                    return PolyLine::groupBy('source')
                        ->selectRaw('count(*) AS total, source')
                        ->get()
                        ->map(fn ($item) => [$item->total, [$item->source]])
                        ->toArray();
                });
            });

        Prometheus::addGauge('profile_image_count')
            ->helpText('How many profile images are stored?')
            ->value(function () {
                return Cache::remember('prom_profile_image_count', PROM_CACHE_TTL, function () {
                    $iter = new \FilesystemIterator(public_path('uploads/avatars'));

                    return iterator_count($iter);
                });
            });

        Prometheus::addGauge('active_statuses_count')
            ->helpText('How many trips are en route?')
            ->value(function () {
                return Cache::remember('prom_active_statuses_count', PROM_CACHE_TTL_SHORT, function () {
                    return Trip::where('departure', '<', now())
                        ->where('arrival', '>', now())
                        ->count();
                });
            });
    }

    public function queueMetrics(): void
    {
        Prometheus::addGauge('queue_size')
            ->helpText('How many items are currently in the job queue?')
            ->labels(['queue'])
            ->value(fn () => collect(['realtime', 'important', 'normal', 'low', 'background'])->map(function ($queue) {
                $size = $this->queueFactoryManager->connection(config('queue.default'))->size($queue);

                return [$size, [$queue]];
            })->toArray()
            );

        Prometheus::addGauge('failed_jobs_count')
            ->helpText('How many jobs have failed?')
            ->labels(['job_name', 'queue'])
            ->value(function () {
                return Cache::remember('prom_failed_jobs_count', PROM_CACHE_TTL_SHORT, function () {
                    return $this->getJobsByDisplayName('failed_jobs');
                });
            });

        Prometheus::addGauge('completed_jobs_count')
            ->helpText('How many jobs are done? Old items from queue monitor table are deleted after 7 days.')
            ->labels(['job_name', 'status', 'queue'])
            ->value(function () {
                return Cache::remember('prom_completed_jobs_count', PROM_CACHE_TTL_SHORT, function () {
                    return DB::table('queue_monitor')
                        ->groupBy('name', 'status', 'queue')
                        ->selectRaw('count(*) AS total, name, status, queue')
                        ->get()
                        ->map(fn ($item) => [$item->total, [$item->name, MonitorStatus::toNamedArray()[$item->status], $item->queue]])
                        ->toArray();
                });
            });
    }

    public function hafasMetrics(): void
    {
        Prometheus::addGauge('failed_hafas_requests_count')
            ->helpText('How many hafas requests have failed?')
            ->labels(['request_name'])
            ->value(function () {
                return $this->getHafasByType(HCK::getFailures());
            });

        Prometheus::addGauge('not_ok_hafas_requests_count')
            ->helpText('How many hafas requests are not ok?')
            ->labels(['request_name'])
            ->value(function () {
                return $this->getHafasByType(HCK::getNotOks());
            });

        Prometheus::addGauge('succeeded_hafas_requests_count')
            ->helpText('How many hafas requests have succeeded?')
            ->labels(['request_name'])
            ->value(function () {
                return $this->getHafasByType(HCK::getSuccesses());
            });

        Prometheus::addGauge('hafas_cache_hits')
            ->helpText('How many hafas requests have been served from cache?')
            ->labels(['request_name'])
            ->value(function () {
                $values = [];
                foreach (HCK::getSuccesses() as $key => $name) {
                    $key = CacheKey::getHafasCacheHitKey($key);
                    $values[$name] = Cache::get($key, 0);
                }

                return array_map(fn ($value, $key) => [$value, [$key]], $values, array_keys($values));
            });

        Prometheus::addGauge('hafas_cache_sets')
            ->helpText('How many hafas requests have been stored in cache?')
            ->labels(['request_name'])
            ->value(function () {
                $values = [];
                foreach (HCK::getSuccesses() as $key => $name) {
                    $key = CacheKey::getHafasCacheSetKey($key);
                    $values[$name] = Cache::get($key, 0);
                }

                return array_map(fn ($value, $key) => [$value, [$key]], $values, array_keys($values));
            });
    }

    private function apiMetrics(): void
    {
        Prometheus::addGauge('api_response_time_avg_ms')
            ->helpText('Average API response time in milliseconds for the previous completed minute')
            ->value(function () {
                $bucket = (int) floor(time() / 60) - 1;
                $sum = Cache::get(CacheKey::getApiResponseTimeSumKey($bucket), 0);
                $count = Cache::get(CacheKey::getApiResponseTimeCountKey($bucket), 0);

                return $count > 0 ? round($sum / $count, 2) : 0;
            });

        Prometheus::addGauge('api_response_hits')
            ->helpText('Number of API requests in the previous completed minute')
            ->value(function () {
                $bucket = (int) floor(time() / 60) - 1;

                return Cache::get(CacheKey::getApiResponseTimeCountKey($bucket), 0);
            });

        Prometheus::addGauge('api_response_codes')
            ->helpText('Number of API responses per HTTP status code in the previous completed minute')
            ->label('status_code')
            ->value(function () {
                $bucket = (int) floor(time() / 60) - 1;
                $result = [];

                foreach (self::API_STATUS_CODES as $code) {
                    $count = Cache::get(CacheKey::getApiResponseCodeKey($bucket, $code), 0);
                    if ($count > 0) {
                        $result[] = [$count, [(string) $code]];
                    }
                }

                return $result;
            });
    }

    public function oAuthMetrics(): void
    {
        Prometheus::addGauge('oauth_total_tokens')
            ->helpText('How many total (revoked and accredited) access tokens do the clients have?')
            ->labels(['app_name'])
            ->value(function () {
                return Cache::remember('prom_oauth_total_tokens', PROM_CACHE_TTL, function () {
                    return DB::table('oauth_access_tokens')
                        ->join('oauth_clients', 'oauth_access_tokens.client_id', '=', 'oauth_clients.id')
                        ->groupBy('oauth_clients.name')
                        ->selectRaw('count(*) AS total, oauth_clients.name AS name')
                        ->orderBy('total', 'desc')
                        ->limit(20)
                        ->get()
                        ->map(fn ($item) => [$item->total, [$item->name]])
                        ->toArray();
                });
            });

        Prometheus::addGauge('oauth_users')
            ->helpText('How many access tokens do the clients have?')
            ->labels(['app_name'])
            ->value(function () {
                return Cache::remember('prom_oauth_users', PROM_CACHE_TTL, function () {
                    return DB::table('oauth_access_tokens')
                        ->join('oauth_clients', 'oauth_access_tokens.client_id', '=', 'oauth_clients.id')
                        ->groupBy('oauth_clients.name')
                        ->selectRaw('count(distinct oauth_access_tokens.user_id) AS total, oauth_clients.name AS name')
                        ->where('oauth_access_tokens.revoked', '=', 0)
                        ->whereNull('oauth_access_tokens.expires_at')
                        ->orderBy('total', 'desc')
                        ->limit(20)
                        ->get()
                        ->map(fn ($item) => [$item->total, [$item->name]])
                        ->toArray();
                });
            });

        Prometheus::addGauge('oauth_revoked_tokens')
            ->helpText('How many revoked access tokens do the clients have?')
            ->labels(['app_name'])
            ->value(function () {
                return Cache::remember('prom_oauth_revoked_tokens', PROM_CACHE_TTL, function () {
                    return DB::table('oauth_access_tokens')
                        ->join('oauth_clients', 'oauth_access_tokens.client_id', '=', 'oauth_clients.id')
                        ->groupBy('oauth_clients.name')
                        ->selectRaw('count(distinct oauth_access_tokens.user_id) AS total, oauth_clients.name AS name')
                        ->where('oauth_access_tokens.revoked', '!=', 0)
                        ->whereNotNull('oauth_access_tokens.expires_at', 'or')
                        ->orderBy('total', 'desc')
                        ->limit(20)
                        ->get()
                        ->map(fn ($item) => [$item->total, [$item->name]])
                        ->toArray();
                });
            });
    }
}
