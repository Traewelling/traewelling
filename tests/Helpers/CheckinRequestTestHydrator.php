<?php

declare(strict_types=1);

namespace Tests\Helpers;

use App\Dto\Internal\CheckInRequestDto;
use App\Models\Checkin;
use App\Models\Stopover;
use App\Models\Trip;
use Illuminate\Contracts\Auth\Authenticatable;

class CheckinRequestTestHydrator
{
    private CheckInRequestDto $dto;
    private string            $searchKey;
    private Authenticatable   $user;

    public function __construct(Authenticatable $user) {
        $this->dto  = new CheckInRequestDto();
        $this->user = $user;
    }

    public function hydrateFromCheckin(Checkin $checkin): CheckInRequestDto {
        $this->dto
            ->setUser($this->user)
            ->setTrip($checkin->trip)
            ->setOrigin($checkin->originStopover->station)
            ->setDeparture($checkin->originStopover->departure_planned)
            ->setDestination($checkin->destinationStopover->station)
            ->setArrival($checkin->destinationStopover->arrival_planned);

        return $this->dto;
    }

    public function hydrateFromTrip(Trip $trip): CheckInRequestDto {
        $this->dto
            ->setUser($this->user)
            ->setTrip($trip)
            ->setOrigin($trip->originStation)
            ->setDeparture($trip->departure)
            ->setDestination($trip->destinationStation)
            ->setArrival($trip->arrival);

        return $this->dto;
    }

    public function hydrateFromStopovers(
        Trip      $trip,
        ?Stopover $originStopover,
        ?Stopover $destinationStopover
    ): CheckInRequestDto {
        $this->dto->setUser($this->user)
                  ->setTrip($trip);

        if ($originStopover !== null) {
            $this->dto->setOrigin($originStopover->trainStation)
                      ->setDeparture($originStopover->departure_planned);
        }

        if ($destinationStopover !== null) {
            $this->dto->setDestination($destinationStopover->trainStation)
                      ->setArrival($destinationStopover->arrival_planned);
        }

        return $this->dto;
    }
}
