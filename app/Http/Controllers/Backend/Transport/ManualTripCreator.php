<?php

namespace App\Http\Controllers\Backend\Transport;

use App\Enum\HafasTravelType;
use App\Enum\TripSource;
use App\Http\Controllers\Backend\Transport\ManualTripCreator as TripBackend;
use App\Http\Controllers\Controller;
use App\Models\HafasOperator;
use App\Models\HafasTrip;
use App\Models\TrainStation;
use App\Models\TrainStopover;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ManualTripCreator extends Controller
{

    private ?HafasTrip $trip;
    //
    public HafasTravelType $category;
    public string          $lineName;
    public ?int            $journeyNumber;
    public ?HafasOperator  $operator;
    public TrainStation    $origin;
    public Carbon          $originDeparturePlanned;
    public TrainStation    $destination;
    public Carbon          $destinationArrivalPlanned;

    public function createTrip(): HafasTrip {
        $this->trip = HafasTrip::create([
                                            'trip_id'        => TripBackend::generateUniqueTripId(),
                                            'category'       => $this->category,
                                            'number'         => $this->lineName,
                                            'linename'       => $this->lineName,
                                            'journey_number' => $this->journeyNumber,
                                            'operator_id'    => $this->operator->id ?? null,
                                            'origin'         => $this->origin->ibnr,
                                            'destination'    => $this->destination->ibnr,
                                            'departure'      => $this->originDeparturePlanned,
                                            'arrival'        => $this->destinationArrivalPlanned,
                                            'source'         => TripSource::USER,
                                        ]);
        return $this->trip;
    }

    public function createOriginStopover(): TrainStopover {
        if ($this->trip === null) {
            throw new \InvalidArgumentException('Cannot create stopover without trip');
        }
        return TrainStopover::create([
                                         'trip_id'           => $this->trip->trip_id,
                                         'train_station_id'  => $this->origin->id,
                                         'arrival_planned'   => $this->originDeparturePlanned,
                                         'departure_planned' => $this->originDeparturePlanned,
                                     ]);
    }

    public function createDestinationStopover(): TrainStopover {
        if ($this->trip === null) {
            throw new \InvalidArgumentException('Cannot create stopover without trip');
        }
        return TrainStopover::create([
                                         'trip_id'           => $this->trip->trip_id,
                                         'train_station_id'  => $this->destination->id,
                                         'arrival_planned'   => $this->destinationArrivalPlanned,
                                         'departure_planned' => $this->destinationArrivalPlanned,
                                     ]);
    }

    public static function generateUniqueTripId(): string {
        $tripId = Str::uuid();
        while (HafasTrip::where('trip_id', $tripId)->exists()) {
            return self::generateUniqueTripId();
        }
        return $tripId;
    }
}
