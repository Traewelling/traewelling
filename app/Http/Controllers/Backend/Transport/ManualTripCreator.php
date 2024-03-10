<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend\Transport;

use App\Enum\HafasTravelType;
use App\Enum\TripSource;
use App\Http\Controllers\Controller;
use App\Models\HafasOperator;
use App\Models\Station;
use App\Models\Stopover;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ManualTripCreator extends Controller
{

    private ?Trip           $trip;
    private HafasTravelType $category;
    private string          $lineName;
    private ?int            $journeyNumber;
    private ?HafasOperator  $operator;
    private Station         $origin;
    private Carbon          $originDeparturePlanned;
    private Station         $destination;
    private Carbon          $destinationArrivalPlanned;
    private array           $stopovers = [];

    public function createFullTrip(): Trip {
        $this->createTrip();
        $this->createOriginStopover();
        $this->createDestinationStopover();
        $this->processStopovers();
        return $this->trip;
    }

    private function createTrip(): void {
        $this->trip = Trip::create([
                                       'trip_id'        => $this->generateUniqueTripId(),
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
                                       'user_id'        => auth()->user()?->id ?? null,
                                   ]);
    }

    private function createOriginStopover(): void {
        if ($this->trip === null) {
            throw new InvalidArgumentException('Cannot create stopover without trip');
        }
        Stopover::create([
                             'trip_id'           => $this->trip->trip_id,
                             'train_station_id'  => $this->origin->id,
                             'arrival_planned'   => $this->originDeparturePlanned,
                             'departure_planned' => $this->originDeparturePlanned,
                         ]);
    }

    private function createDestinationStopover(): void {
        if ($this->trip === null) {
            throw new InvalidArgumentException('Cannot create stopover without trip');
        }
        Stopover::create([
                             'trip_id'           => $this->trip->trip_id,
                             'train_station_id'  => $this->destination->id,
                             'arrival_planned'   => $this->destinationArrivalPlanned,
                             'departure_planned' => $this->destinationArrivalPlanned,
                         ]);
    }

    private function generateUniqueTripId(): string {
        $tripId = Str::uuid();
        while (Trip::where('trip_id', $tripId)->exists()) {
            $tripId = Str::uuid();
        }
        return $tripId->toString();
    }

    public function setCategory(HafasTravelType $category): ManualTripCreator {
        $this->category = $category;
        return $this;
    }

    public function setLine(string $lineName, ?int $journeyNumber): ManualTripCreator {
        $this->lineName      = $lineName;
        $this->journeyNumber = $journeyNumber;
        return $this;
    }

    public function setOperator(?HafasOperator $operator): ManualTripCreator {
        $this->operator = $operator;
        return $this;
    }

    public function setOrigin(Station $origin, Carbon $plannedDeparture): ManualTripCreator {
        $this->origin                 = $origin;
        $this->originDeparturePlanned = $plannedDeparture;
        return $this;
    }

    public function setDestination(Station $destination, Carbon $plannedArrival): ManualTripCreator {
        $this->destination               = $destination;
        $this->destinationArrivalPlanned = $plannedArrival;
        return $this;
    }

    public function addStopover(
        Station $station,
        ?Carbon $plannedDeparture,
        ?Carbon $plannedArrival
    ): ManualTripCreator {
        if ($plannedDeparture === null && $plannedArrival === null) {
            throw new InvalidArgumentException('Either arrival or departure must be set');
        }
        if ($plannedDeparture !== null && $plannedArrival !== null && $plannedDeparture->isAfter($plannedArrival)) {
            throw new InvalidArgumentException('Departure must be before arrival');
        }

        $this->stopovers[] = [
            'station'   => $station,
            'departure' => $plannedDeparture ?? $plannedArrival,
            'arrival'   => $plannedArrival ?? $plannedDeparture,
        ];
        return $this;
    }

    private function processStopovers(): void {
        if ($this->trip === null) {
            throw new InvalidArgumentException('Cannot add stopover without trip');
        }
        foreach ($this->stopovers as $stopover) {
            Stopover::create([
                                 'trip_id'           => $this->trip->trip_id,
                                 'train_station_id'  => $stopover['station']->id,
                                 'arrival_planned'   => $stopover['arrival'],
                                 'departure_planned' => $stopover['departure'],
                             ]);
        }
    }
}
