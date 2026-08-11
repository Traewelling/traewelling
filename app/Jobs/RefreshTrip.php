<?php

declare(strict_types=1);

namespace App\Jobs;

use App\DataProviders\DataProviderBuilder;
use App\DataProviders\DataProviderInterface;
use App\DataProviders\Hydrators\MotisHydrator;
use App\DataProviders\Motis;
use App\Enum\DataProvider;
use App\Enum\Queue;
use App\Exceptions\DataProviderException;
use App\Models\Checkin;
use App\Models\Stopover;
use App\Models\Trip;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use PDOException;

class RefreshTrip implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(private readonly Trip $trip)
    {
        $this->onQueue(Queue::IMPORTANT->value);
    }

    public function uniqueId(): string
    {
        return $this->trip->trip_id;
    }

    private function getDataProvider(): DataProviderInterface
    {
        return new DataProviderBuilder()->build();
    }

    public function handle(MotisHydrator $motisHydrator): void
    {
        $dataProvider = $this->getDataProvider();

        if (!$dataProvider instanceof Motis) {
            Log::warning('RefreshTrip Job skipped: only Motis is supported', ['trip_id' => $this->trip->trip_id]);

            return;
        }

        try {
            $this->trip->update(['last_refreshed' => now()]);

            $rawJourney = $dataProvider->fetchRawHafasTrip($this->trip->trip_id, $this->trip->linename);
            if (!$rawJourney) {
                Log::debug('RefreshTrip Job skipped: no journey data available', ['trip_id' => $this->trip->trip_id]);

                return;
            }

            $leg = $rawJourney['legs'][0] ?? null;

            if ($leg === null || !($leg['realTime'] ?? false)) {
                Log::debug('RefreshTrip Job skipped: no real-time data available', ['trip_id' => $this->trip->trip_id]);

                return;
            }

            $stopovers = $motisHydrator->parseLegToUpdateStopovers(
                $leg,
                $this->trip,
                DataProvider::TRANSITOUS
            );

            $this->resetRouteSegmentsIfNewStopoversWereCreated($stopovers);

            Log::debug('RefreshTrip Job completed', [
                'trip_id' => $this->trip->trip_id,
                'updated_stopovers' => $stopovers->count(),
            ]);

            Checkin::where('trip_id', $this->trip->trip_id)->update(['duration' => null]);
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                Log::warning('RefreshTrip Job skipped: integrity constraint violation', ['trip_id' => $this->trip->trip_id]);

                return;
            }
            throw $exception;
        } catch (DataProviderException $exception) {
            Log::error('RefreshTrip Job failed: DataProviderException', [
                'trip_id' => $this->trip->trip_id,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function resetRouteSegmentsIfNewStopoversWereCreated(Collection $stopovers): void
    {
        $newStopovers = $stopovers->filter(fn (Stopover $stopover) => $stopover->wasRecentlyCreated);
        if ($newStopovers->isEmpty() || !$this->trip->category->onRails()) {
            return;
        }

        Stopover::where('trip_id', $this->trip->trip_id)->update(['route_segment_id' => null]);
        RefreshPolyline::dispatch($this->trip);

        Log::info('RefreshTrip: new stopovers were created, route segments reset and polyline refresh dispatched', [
            'trip_id' => $this->trip->trip_id,
            'new_stopovers' => $newStopovers->count(),
        ]);
    }
}
