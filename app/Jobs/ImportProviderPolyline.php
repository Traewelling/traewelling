<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enum\Queue;
use App\Models\Trip;
use App\Services\Trip\ProviderPolylineService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class ImportProviderPolyline implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, IsMonitored, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public int $timeout = 300;

    public function __construct(private readonly Trip $trip)
    {
        $this->onQueue(Queue::LOW->value);
    }

    public function uniqueId(): string
    {
        return (string) $this->trip->id;
    }

    public function handle(ProviderPolylineService $providerPolylineService): void
    {
        if (app()->environment('testing')) {
            Log::info('ImportProviderPolyline Job skipped: Testing environment', ['trip_id' => $this->trip->id]);

            return;
        }

        Log::debug('ImportProviderPolyline Job started', [
            'trip_id' => $this->trip->id,
            'provider_trip_id' => $this->trip->trip_id,
            'attempt' => $this->attempts(),
        ]);

        $result = $providerPolylineService->importForTrip($this->trip);

        Log::info('ImportProviderPolyline: Completed', [
            'trip_id' => $this->trip->id,
            ...$result->toArray(),
        ]);
    }
}
