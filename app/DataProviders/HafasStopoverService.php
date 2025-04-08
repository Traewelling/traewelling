<?php

namespace App\DataProviders;

use App\Exceptions\HafasException;
use App\Models\Stopover;
use Carbon\Carbon;
use stdClass;

class HafasStopoverService
{
    private DataProviderInterface $dataProvider;

    /**
     * @template T of DataProviderInterface
     * @param class-string<T>          $dataProvider
     * @param DataProviderBuilder|null $dataProviderFactory
     */
    public function __construct(string $dataProvider, ?DataProviderBuilder $dataProviderFactory = null) {
        $dataProviderFactory ??= new DataProviderBuilder();
        $this->dataProvider  = $dataProviderFactory->build($dataProvider);
    }

    public static function refreshStopovers(stdClass $rawHafas): stdClass {
        $stopoversUpdated = 0;
        $payloadArrival   = [];
        $payloadDeparture = [];
        $payloadCancelled = [];
        foreach ($rawHafas->stopovers ?? [] as $stopover) {
            if (!isset($stopover->arrivalDelay) && !isset($stopover->departureDelay) && !isset($stopover->cancelled)) {
                continue; // No realtime data present for this stopover, keep existing data
            }

            $stop             = Repositories\StationRepository::parseHafasStopObject($stopover->stop);
            $arrivalPlanned   = Carbon::parse($stopover->plannedArrival)->tz(config('app.timezone'));
            $departurePlanned = Carbon::parse($stopover->plannedDeparture)->tz(config('app.timezone'));

            $basePayload = [
                'trip_id'           => $rawHafas->id,
                'train_station_id'  => $stop->id,
                'arrival_planned'   => isset($stopover->plannedArrival) ? $arrivalPlanned : $departurePlanned,
                'departure_planned' => isset($stopover->plannedDeparture) ? $departurePlanned : $arrivalPlanned,
            ];

            if (isset($stopover->arrivalDelay) && isset($stopover->arrival)) {
                $arrivalReal      = Carbon::parse($stopover->arrival)->tz(config('app.timezone'));
                $payloadArrival[] = array_merge($basePayload, ['arrival_real' => $arrivalReal]);
            }

            if (isset($stopover->departureDelay) && isset($stopover->departure)) {
                $departureReal      = Carbon::parse($stopover->departure)->tz(config('app.timezone'));
                $payloadDeparture[] = array_merge($basePayload, ['departure_real' => $departureReal]);
            }

            // In case of cancellation, arrivalDelay/departureDelay will be null while the cancelled attribute will be present and true
            // If cancelled is false / missing while other RT data is present (see initial if expression), it will be upserted to false
            // This behavior is required for potential withdrawn cancellations
            $payloadCancelled[] = array_merge($basePayload, ['cancelled' => $stopover->cancelled ?? false]);

            $stopoversUpdated++;
        }

        $key = ['trip_id', 'train_station_id', 'departure_planned', 'arrival_planned'];

        return (object) [
            "stopovers" => $stopoversUpdated,
            "rows"      => [
                "arrival"   => Stopover::upsert($payloadArrival, $key, ['arrival_real']),
                "departure" => Stopover::upsert($payloadDeparture, $key, ['departure_real']),
                "cancelled" => Stopover::upsert($payloadCancelled, $key, ['cancelled'])
            ]
        ];
    }

    /**
     * This function is used to refresh the departure of a trip, if the planned_departure is in the past and no
     * real-time data is given. The HAFAS stationboard gives us this real-time data even for trips in the past, so give
     * it a chance.
     *
     * This function should be called in an async job, if not needed instantly.
     *
     * @param Stopover $stopover
     *
     * @return void
     * @throws HafasException
     */
    public function refreshStopover(Stopover $stopover): void {
        if($stopover->departure_planned === null) {
            return;
        }
        $departure = $this->dataProvider->getDepartures(
            station: $stopover->station,
            when:    $stopover->departure_planned,
        )->filter(function(stdClass $trip) use ($stopover) {
            return $trip->tripId === $stopover->trip_id;
        })->first();

        if ($departure === null || $departure->when === null || $departure->plannedWhen === $departure->when) {
            return; //do nothing, if the trip isn't found.
        }

        $stopover->update([
                              'departure_real' => Carbon::parse($departure->when),
                          ]);
    }
}
