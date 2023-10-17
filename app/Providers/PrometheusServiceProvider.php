<?php

namespace App\Providers;

use App\Models\HafasTrip;
use App\Models\PolyLine;
use App\Models\Status;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use romanzipp\QueueMonitor\Enums\MonitorStatus;
use Spatie\Prometheus\Facades\Prometheus;

class PrometheusServiceProvider extends ServiceProvider
{
    public function register() {
        /*
         * Here you can register all the exporters that you
         * want to export to prometheus
         */
        Prometheus::addGauge('Users count')
                  ->helpText("How many users are registered on the website?")
                  ->value(function() {
                      return User::count();
                  });

        Prometheus::addGauge('Status count')
                  ->helpText("How many statuses are posted on the website?")
                  ->value(function() {
                      return Status::count();
                  });

        Prometheus::addGauge('Hafas Trips count')
                  ->helpText("How many hafas trips are posted grouped by operator and mode of transport?")
                  ->labels(["operator", "category"])
                  ->value(function() {
                      return HafasTrip::groupBy("operator_id", "category")
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
                  ->label("job_name")
                  ->value(function() {
                      if (config("queue.default") === "database") {
                          return $this->getJobsByDisplayName("jobs");
                      }

                      return [Queue::size(), ["all"]];
                  });

        Prometheus::addGauge("failed_jobs_count")
                  ->helpText("How many jobs have failed?")
                  ->label("job_name")
                  ->value(function() {
                      return $this->getJobsByDisplayName("failed_jobs");
                  });

        Prometheus::addGauge("completed_jobs_count")
                  ->helpText("How many jobs are done? Old items from queue monitor table are deleted after 7 days.")
                  ->labels(["job_name", "status"])
                  ->value(function() {
                      return DB::table("queue_monitor")
                               ->groupBy("name", "status")
                               ->selectRaw("count(*) AS total, name, status")
                               ->get()
                               ->map(fn($item) => [$item->total, [$item->name, MonitorStatus::toNamedArray()[$item->status]]])
                               ->toArray();
                  });

        Prometheus::addGauge("profile_image_count")
                  ->helpText("How many profile images are stored?")
                  ->value(function() {
                      $iter = new \FilesystemIterator(public_path("uploads/avatars"));
                      return iterator_count($iter);
                  });

        Prometheus::addGauge("is_maintenance_mode_active")
                  ->helpText("Is the Laravel Maintenance Mode active right now?")
                  ->value($this->app->maintenanceMode()->active());

        Prometheus::addGauge("oauth_tokens")
                  ->helpText("How many users do the clients have?")
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
    }


    public static function getJobsByDisplayName($table_name): array {
        $counts = DB::table($table_name)
                    ->get("payload")
                    ->map(fn($row) => json_decode($row->payload))
                    ->countBy(fn($payload) => $payload->displayName)
                    ->toArray();

        return array_map(
            fn($jobname, $count) => [$count, [$jobname]],
            array_keys($counts),
            array_values($counts)
        );
    }
}
