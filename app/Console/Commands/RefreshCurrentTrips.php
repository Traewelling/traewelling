<?php

namespace App\Console\Commands;

use App\DataProviders\DataProviderBuilder;
use App\DataProviders\DataProviderInterface;
use App\DataProviders\HafasStopoverService;
use App\Enum\TripSource;
use App\Exceptions\HafasException;
use App\Models\Checkin;
use App\Models\Trip;
use Illuminate\Console\Command;
use PDOException;

class RefreshCurrentTrips extends Command
{
    protected $signature   = 'trwl:refreshTrips';
    protected $description = 'Refresh delay data from current active trips';

    private function getDataProvider(): DataProviderInterface {
        // Probably only HafasController is needed here, because this Command is very Hafas specific
        return (new DataProviderBuilder)->build();
    }

    public function handle(): int {
        $this->info('Getting trips to be refreshed...');

        // To only refresh checked in trips join train_checkins:
        $trips = Trip::join('train_checkins', 'train_checkins.trip_id', '=', 'hafas_trips.trip_id')
                     ->join('train_stopovers as origin_stopovers', 'origin_stopovers.id', '=', 'train_checkins.origin_stopover_id')
                     ->join('train_stopovers as destination_stopovers', 'destination_stopovers.id', '=', 'train_checkins.destination_stopover_id')
                     ->where(function($query) {
                         $query->where('destination_stopovers.arrival_planned', '>=', now()->subMinutes(20))
                               ->orWhere('destination_stopovers.arrival_real', '>=', now()->subMinutes(20));
                     })
                     ->where(function($query) {
                         $query->where('origin_stopovers.departure_planned', '<=', now()->addMinutes(20))
                               ->orWhere('origin_stopovers.departure_real', '<=', now()->addMinutes(20));
                     })
                     ->where(function($query) {
                         $query->where('hafas_trips.last_refreshed', '<', now()->subMinutes(5))
                               ->orWhereNull('hafas_trips.last_refreshed');
                     })
                     ->where('hafas_trips.source', TripSource::HAFAS->value)
                     ->select('hafas_trips.*')
                     ->distinct()
                     ->orderBy('hafas_trips.last_refreshed')
                     ->get();

        if ($trips->isEmpty()) {
            $this->warn('No trips to be refreshed');
            return 0;
        }

        $this->info('Found ' . $trips->count() . ' trips.');

        $loop = 1;
        foreach ($trips as $trip) {
            try {
                $this->info('Refreshing trip ' . $trip->trip_id . ' (' . $trip->linename . ')...');
                $trip->update(['last_refreshed' => now()]);

                $rawHafas      = $this->getDataProvider()->fetchRawHafasTrip($trip->trip_id, $trip->linename);
                $updatedCounts = HafasStopoverService::refreshStopovers($rawHafas);
                $this->info('Updated ' . $updatedCounts->stopovers . ' stopovers.');

                //set duration for refreshed trips to null, so it will be recalculated
                Checkin::where('trip_id', $trip->trip_id)->update(['duration' => null]);
            } catch (PDOException $exception) {
                if ($exception->getCode() === '23000') {
                    $this->warn('-> Skipping, due to integrity constraint violation');
                } else {
                    report($exception);
                }
            } catch (HafasException) {
                // Do nothing
            } catch (\Exception $exception) {
                report($exception);
            }

            if ($loop++ >= config('trwl.refresh.max_trips_per_minute')) {
                $this->warn('Max number of trips reached. Waiting for next minute...');
                return 0;
            }
        }
        return 0;
    }
}
