<?php

namespace App\Providers;

use App\Enum\CacheKey;
use App\Enum\MonitoringCounter;
use App\Models\Trip;
use App\Models\PolyLine;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use romanzipp\QueueMonitor\Enums\MonitorStatus;
use Spatie\Prometheus\Facades\Prometheus;

const PROM_JOB_SCRAPER_SEPARATOR = "-PROM-JOB-SCRAPER-SEPARATOR-";
class PrometheusServiceProvider extends ServiceProvider
{
    public function register() {
        /*
         * Here you can register all the exporters that you
         * want to export to prometheus
         */
        Prometheus::addGauge('Users count')
                  ->helpText("How many users are registered on the website?")
            ->label("state")
            ->value(function() {
                return [
                    [Cache::get(CacheKey::getMonitoringCounterKey(MonitoringCounter::UserCreated)), ["created"]],
                    [Cache::get(CacheKey::getMonitoringCounterKey(MonitoringCounter::UserDeleted)), ["deleted"]]
                ];
            });


        Prometheus::addGauge('Status count')
                  ->helpText("How many statuses are posted on the website?")
            ->label("state")
            ->value(function() {
                return [
                    [Cache::get(CacheKey::getMonitoringCounterKey(MonitoringCounter::StatusCreated)), ["created"]],
                    [Cache::get(CacheKey::getMonitoringCounterKey(MonitoringCounter::StatusDeleted)), ["deleted"]]
                ];
            });

        Prometheus::addGauge('Hafas Trips count')
                  ->helpText("How many hafas trips are posted grouped by operator and mode of transport?")
                  ->labels(["operator", "category"])
                  ->value(function() {
                      return Trip::groupBy("operator_id", "category")
                                 ->selectRaw("count(*) AS total, operator_id, category")
                                 ->with("operator")
                                 ->get()
                                 ->map(fn($item) => [$item->total, [$item->operator?->name, $item->category]])
                                 ->toArray();
                  });

        Prometheus::addGauge('Polylines count')
                  ->helpText("How many polylines are saved grouped by source?")
                  ->labels(["source"])
                  ->value(function() {
                      return PolyLine::groupBy("source")
                                     ->selectRaw("count(*) AS total, source")
                                     ->get()
                                     ->map(fn($item) => [$item->total, [$item->source]])
                                     ->toArray();
                  });

        Prometheus::addGauge("queue_size")
                  ->helpText("How many items are currently in the job queue?")
                  ->labels(["job_name", "queue"])
                  ->value(function() {
                      if (config("queue.default") === "database") {
                          return $this->getJobsByDisplayName("jobs");
                      }

                      return [Queue::size(), ["all", "all"]];
                  });

        Prometheus::addGauge("failed_jobs_count")
                  ->helpText("How many jobs have failed?")
                  ->labels(["job_name", "queue"])
                  ->value(function() {
                      return $this->getJobsByDisplayName("failed_jobs");
                  });

        Prometheus::addGauge("completed_jobs_count")
                  ->helpText("How many jobs are done? Old items from queue monitor table are deleted after 7 days.")
                  ->labels(["job_name", "status", "queue"])
                  ->value(function() {
                      return DB::table("queue_monitor")
                               ->groupBy("name", "status", "queue")
                               ->selectRaw("count(*) AS total, name, status, queue")
                               ->get()
                               ->map(fn($item) => [$item->total, [$item->name, MonitorStatus::toNamedArray()[$item->status], $item->queue]])
                               ->toArray();
                  });

        Prometheus::addGauge('absent_webhooks_deleted')
                  ->helpText("How many webhooks were responded with Gone and were thus deleted from our side?")
                  ->value(fn() => Cache::get(CacheKey::getMonitoringCounterKey(MonitoringCounter::WebhookAbsent)));

        Prometheus::addGauge("profile_image_count")
                  ->helpText("How many profile images are stored?")
                  ->value(function() {
                      $iter = new \FilesystemIterator(public_path("uploads/avatars"));
                      return iterator_count($iter);
                  });

        Prometheus::addGauge("is_maintenance_mode_active")
                  ->helpText("Is the Laravel Maintenance Mode active right now?")
                  ->value($this->app->maintenanceMode()->active());

        Prometheus::addGauge("oauth_total_tokens")
                  ->helpText("How many total (revoked and accredited) access tokens do the clients have?")
                  ->labels(["app_name"])
                  ->value(function() {
                      return DB::table("oauth_access_tokens")
                               ->join("oauth_clients", "oauth_access_tokens.client_id", "=", "oauth_clients.id")
                               ->groupBy("oauth_clients.name")
                               ->selectRaw("count(*) AS total, oauth_clients.name AS name")
                               ->orderBy("total", "desc")
                               ->limit(20)
                               ->get()
                               ->map(fn($item) => [$item->total, [$item->name]])
                               ->toArray();
                  });
        Prometheus::addGauge("oauth_users")
                  ->helpText("How many access tokens do the clients have?")
                  ->labels(["app_name"])
                  ->value(function() {
                      return DB::table("oauth_access_tokens")
                               ->join("oauth_clients", "oauth_access_tokens.client_id", "=", "oauth_clients.id")
                               ->groupBy("oauth_clients.name")
                               ->selectRaw("count(distinct oauth_access_tokens.user_id) AS total, oauth_clients.name AS name")
                               ->where("oauth_access_tokens.revoked", "=", 0)
                               ->whereNull("oauth_access_tokens.expires_at")
                               ->orderBy("total", "desc")
                               ->limit(20)
                               ->get()
                               ->map(fn($item) => [$item->total, [$item->name]])
                               ->toArray();
                  });
        Prometheus::addGauge("oauth_revoked_tokens")
                  ->helpText("How many revoked access tokens do the clients have?")
                  ->labels(["app_name"])
                  ->value(function() {
                      return DB::table("oauth_access_tokens")
                               ->join("oauth_clients", "oauth_access_tokens.client_id", "=", "oauth_clients.id")
                               ->groupBy("oauth_clients.name")
                               ->selectRaw("count(distinct oauth_access_tokens.user_id) AS total, oauth_clients.name AS name")
                               ->where("oauth_access_tokens.revoked", "!=", 0)
                               ->whereNotNull("oauth_access_tokens.expires_at", "or")
                               ->orderBy("total", "desc")
                               ->limit(20)
                               ->get()
                               ->map(fn($item) => [$item->total, [$item->name]])
                               ->toArray();
                  });
    }


    public static function getJobsByDisplayName($table_name): array {
        $counts = DB::table($table_name)
                    ->get(["queue", "payload"])
                    ->map(fn($row) => [
                        'queue'       => $row->queue,
                        'displayName' => json_decode($row->payload)->displayName])
                    ->countBy(fn($job) => $job['displayName'] . PROM_JOB_SCRAPER_SEPARATOR . $job['queue'])
                    ->toArray();

        return array_map(
            fn($job_properties, $count) => [$count, explode(PROM_JOB_SCRAPER_SEPARATOR, $job_properties)],
            array_keys($counts),
            array_values($counts)
        );
    }

    public static function getJobsByQueue($table_name): array {
        $counts = DB::table($table_name)
                    ->get("queue")
                    ->countBy(fn($job) => $job->queue)
                    ->toArray();

        return array_map(
            fn($jobname, $count) => [$count, [$jobname]],
            array_keys($counts),
            array_values($counts)
        );
    }
}
