<?php

namespace App\Console\Commands;

use App\DataProviders\DataProviderBuilder;
use App\DataProviders\DataProviderInterface;
use App\DataProviders\Repositories\TripRepository;
use App\Enum\TripSource;
use App\Exceptions\HafasException;
use App\Models\Checkin;
use Illuminate\Console\Command;
use PDOException;

class RefreshCurrentTrips extends Command
{
    protected $signature   = 'trwl:refreshTrips';
    protected $description = 'Refresh delay data from current active trips';

    private TripRepository $tripRepository;

    public function __construct(?TripRepository $tripRepository = null) {
        parent::__construct();
        $this->tripRepository = $tripRepository ?? new TripRepository();
    }

    private function getDataProvider(): DataProviderInterface {
        // Probably only HafasController is needed here, because this Command is very Hafas specific
        return (new DataProviderBuilder)->build();
    }

    public function handle(): int {
        $this->info('Getting trips to be refreshed...');

        $trips = $this->tripRepository->getCurrentActiveTrips(TripSource::TRANSITOUS)
                                      ->where('last_refreshed', '<', now()->subMinutes(5))
                                      ->orWhereNull('last_refreshed')
                                      ->orderBy('departure')
                                      ->get();

        if ($trips->isEmpty()) {
            $this->info('No trips to be refreshed');
            return 0;
        }

        $this->info('Found ' . $trips->count() . ' trips.');

        $loop = 1;
        foreach ($trips as $trip) {
            try {
                $this->info('Refreshing trip ' . $trip->trip_id . ' (' . $trip->linename . ')...');
                $trip->update(['last_refreshed' => now()]);

                $rawHafas = $this->getDataProvider()->fetchRawHafasTrip($trip->trip_id, $trip->linename);


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
