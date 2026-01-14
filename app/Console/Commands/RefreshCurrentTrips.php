<?php

namespace App\Console\Commands;

use App\DataProviders\DataProviderBuilder;
use App\DataProviders\DataProviderInterface;
use App\DataProviders\Hydrators\MotisHydrator;
use App\DataProviders\Motis;
use App\DataProviders\Repositories\TripRepository;
use App\Enum\DataProvider;
use App\Enum\TripSource;
use App\Exceptions\DataProviderException;
use App\Models\Checkin;
use Illuminate\Console\Command;
use PDOException;

class RefreshCurrentTrips extends Command
{
    protected $signature = 'trwl:refreshTrips';

    protected $description = 'Refresh delay data from current active trips';

    private TripRepository $tripRepository;

    private MotisHydrator $motisHydrator;

    public function __construct(?TripRepository $tripRepository = null, ?MotisHydrator $motisHydrator = null)
    {
        parent::__construct();
        $this->tripRepository = $tripRepository ?? new TripRepository();
        $this->motisHydrator = $motisHydrator ?? new MotisHydrator();
    }

    private function getDataProvider(): DataProviderInterface
    {
        // Probably only HafasController is needed here, because this Command is very Hafas specific
        return (new DataProviderBuilder())->build();
    }

    public function handle(): int
    {
        if ($this->getDataProvider() instanceof Motis === false) {
            $this->error('Currently only Motis is supported for this command.');

            return 1;
        }

        $this->info('Getting trips to be refreshed...');

        $trips = $this->tripRepository->getCurrentActiveTrips(TripSource::TRANSITOUS);

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

                $rawJourney = $this->getDataProvider()->fetchRawHafasTrip($trip->trip_id, $trip->linename);
                if (!$rawJourney || !$rawJourney['legs'][0]['realTime']) {
                    $this->warn('-> Skipping, no real-time data available');

                    continue;
                }

                $stopovers = $this->motisHydrator->parseLegToUpdateStopovers(
                    $rawJourney['legs'][0],
                    $trip,
                    DataProvider::TRANSITOUS
                );

                $this->info(sprintf('Updated stopovers: %d', $stopovers->count()));

                // set duration for refreshed trips to null, so it will be recalculated
                Checkin::where('trip_id', $trip->trip_id)->update(['duration' => null]);
            } catch (PDOException $exception) {
                if ($exception->getCode() === '23000') {
                    $this->warn('-> Skipping, due to integrity constraint violation');
                } else {
                    report($exception);
                }
            } catch (DataProviderException) {
                $this->error('-> Skipping, due to DataProviderException');
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
