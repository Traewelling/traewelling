<?php

namespace App\Http\Controllers\API\v1;

use App\Enum\CacheKey;
use App\Http\Controllers\HafasController;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class HealthMonitorController
{

    public function healthReady(): JsonResponse {
        $checks = [
            "database_reachable" => $this->isDatabaseReachable()
        ];

        return response()
            ->json($checks)
            ->setStatusCode(array_product($checks) ? 200 : 500);
    }

    public function healthFitness(): JsonResponse {
        $checks = [
            "database_reachable" => $this->isDatabaseReachable(),
            "queue_running"      => $this->isQueueRunning(),
            "db_rest_reachable"  => $this->isDbRestReachable(),
            "scheduler_running"  => $this->isSchedulerRunning(),
        ];

        return response()
            ->json($checks)
            ->setStatusCode(array_product($checks) ? 200 : 500);
    }

    private function isDatabaseReachable(): bool {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (QueryException $e) {
            Log::info("Health Monitor ran into Query exception. Is the database not available?");
            return false;
        }
    }

    private function isQueueRunning(): bool {
        $size = Queue::size();
        return $size < 10;
    }

    private function isDbRestReachable(): bool {
        $response = HafasController::getHttpClient()
                                   ->get("/stations/8000105");
        return $response->successful();
    }

    private function isSchedulerRunning(): bool {
        $lastScheduler = Cache::get(CacheKey::SchedulerCanary, 0);
        return time() - $lastScheduler < 60;
    }
}
