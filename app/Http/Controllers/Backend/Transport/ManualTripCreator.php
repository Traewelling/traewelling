<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend\Transport;

use App\Enum\HafasTravelType;
use App\Enum\TripSource;
use App\Exceptions\ManualTripValidationException;
use App\Http\Controllers\Controller;
use App\Models\Operator;
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
    private ?Operator       $operator  = null;
    private Station         $origin;
    private Carbon          $originDeparturePlanned;
    private ?Carbon         $originDepartureReal;
    private Station         $destination;
    private Carbon          $destinationArrivalPlanned;
    private ?Carbon         $destinationArrivalReal;
    private array           $stopovers = [];

    public function createFullTrip(): Trip {
        $this->createTrip();
        $this->createOriginStopover();
        $this->createDestinationStopover();
        $this->processStopovers();
        return $this->trip;
    }

    private function createTrip(): void {
        \Log::debug('Create manual trip', [
            'user_id'        => auth()->user()?->id ?? null,
            'category'       => $this->category,
            'lineName'       => $this->lineName,
            'journeyNumber'  => $this->journeyNumber,
            'operator_id'    => $this->operator->id ?? null,
            'origin_id'      => $this->origin->id,
            'destination_id' => $this->destination->id,
            'departure'      => $this->originDeparturePlanned,
            'arrival'        => $this->destinationArrivalPlanned,
        ]);
        $this->trip = Trip::create([
                                       'trip_id'        => $this->generateUniqueTripId(),
                                       'category'       => $this->category,
                                       'number'         => $this->lineName,
                                       'linename'       => $this->lineName,
                                       'journey_number' => $this->journeyNumber,
                                       'operator_id'    => $this->operator->id ?? null,
                                       'origin_id'      => $this->origin->id,
                                       'destination_id' => $this->destination->id,
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
        \Log::debug('Create origin stopover for manual trip', [
            'user_id'           => auth()->user()?->id ?? null,
            'trip_id'           => $this->trip->trip_id,
            'train_station_id'  => $this->origin->id,
            'arrival_planned'   => $this->originDeparturePlanned,
            'departure_planned' => $this->originDeparturePlanned,
            'arrival_real'      => $this->originDepartureReal,
            'departure_real'    => $this->originDepartureReal,
        ]);
        Stopover::create([
                             'trip_id'           => $this->trip->trip_id,
                             'train_station_id'  => $this->origin->id,
                             'arrival_planned'   => $this->originDeparturePlanned,
                             'departure_planned' => $this->originDeparturePlanned,
                             'arrival_real'      => $this->originDepartureReal,
                             'departure_real'    => $this->originDepartureReal,
                         ]);
    }

    private function createDestinationStopover(): void {
        if ($this->trip === null) {
            throw new InvalidArgumentException('Cannot create stopover without trip');
        }
        \Log::debug('Create destination stopover for manual trip', [
            'user_id'           => auth()->user()?->id ?? null,
            'trip_id'           => $this->trip->trip_id,
            'train_station_id'  => $this->origin->id,
            'arrival_planned'   => $this->originDeparturePlanned,
            'departure_planned' => $this->originDeparturePlanned,
            'arrival_real'      => $this->originDepartureReal,
            'departure_real'    => $this->originDepartureReal,
        ]);
        Stopover::create([
                             'trip_id'           => $this->trip->trip_id,
                             'train_station_id'  => $this->destination->id,
                             'arrival_planned'   => $this->destinationArrivalPlanned,
                             'departure_planned' => $this->destinationArrivalPlanned,
                             'arrival_real'      => $this->destinationArrivalReal,
                             'departure_real'    => $this->destinationArrivalReal,
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

    public function setOperator(?Operator $operator): ManualTripCreator {
        $this->operator = $operator;
        return $this;
    }

    public function setOrigin(Station $origin, Carbon $plannedDeparture, ?Carbon $realDeparture = null): ManualTripCreator {
        $this->origin                 = $origin;
        $this->originDeparturePlanned = $plannedDeparture;
        $this->originDepartureReal    = $realDeparture;
        return $this;
    }

    public function setDestination(Station $destination, Carbon $plannedArrival, ?Carbon $realArrival = null): ManualTripCreator {
        $this->destination               = $destination;
        $this->destinationArrivalPlanned = $plannedArrival;
        $this->destinationArrivalReal    = $realArrival;
        return $this;
    }

    /**
     * @throws ManualTripValidationException
     */
    public function addStopover(
        Station $station,
        ?Carbon $plannedDeparture,
        ?Carbon $plannedArrival,
        ?Carbon $realDeparture,
        ?Carbon $realArrival
    ): ManualTripCreator {
        if ($plannedDeparture === null && $plannedArrival === null) {
            throw new ManualTripValidationException('Either arrival or departure must be set');
        }
        if ($plannedDeparture !== null && $plannedArrival !== null && $plannedDeparture->isBefore($plannedArrival)) {
            throw new ManualTripValidationException('Departure must be after arrival');
        }
        if ($realDeparture !== null && $realArrival !== null && $realDeparture->isBefore($realArrival)) {
            throw new ManualTripValidationException('Real departure must be after real arrival');
        }

        $this->stopovers[] = [
            'station'        => $station,
            'departure'      => $plannedDeparture ?? $plannedArrival,
            'arrival'        => $plannedArrival ?? $plannedDeparture,
            'departure_real' => $realDeparture,
            'arrival_real'   => $realArrival
        ];
        return $this;
    }

    private function processStopovers(): void {
        if ($this->trip === null) {
            throw new InvalidArgumentException('Cannot add stopover without trip');
        }
        foreach ($this->stopovers as $stopover) {
            \Log::debug('Create stopover for manual trip', [
                'user_id'           => auth()->user()?->id ?? null,
                'trip_id'           => $this->trip->trip_id,
                'train_station_id'  => $stopover['station']->id,
                'arrival_planned'   => $stopover['arrival'],
                'departure_planned' => $stopover['departure'],
                'arrival_real'      => $stopover['arrival_real'],
                'departure_real'    => $stopover['departure_real'],
            ]);
            Stopover::create([
                                 'trip_id'           => $this->trip->trip_id,
                                 'train_station_id'  => $stopover['station']->id,
                                 'arrival_planned'   => $stopover['arrival'],
                                 'departure_planned' => $stopover['departure'],
                                 'arrival_real'      => $stopover['arrival_real'],
                                 'departure_real'    => $stopover['departure_real'],
                             ]);
        }
    }
}
