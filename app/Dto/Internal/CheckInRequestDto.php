<?php

declare(strict_types=1);

namespace App\Dto\Internal;

use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Models\Event;
use App\Models\Station;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;

class CheckInRequestDto
{
    public Authenticatable  $user;
    public Trip             $trip;
    public Station          $origin;
    public Carbon           $departure;
    public Station          $destination;
    public Carbon           $arrival;
    public Business         $travelReason;
    public StatusVisibility $statusVisibility;
    public ?string          $body;
    public ?Event           $event;
    public bool             $forceFlag;
    public bool             $postOnMastodonFlag;
    public bool             $chainFlag;

    public function __construct() {
        $this->travelReason       = Business::PRIVATE;
        $this->statusVisibility   = StatusVisibility::PUBLIC;
        $this->body               = null;
        $this->event              = null;
        $this->forceFlag          = false;
        $this->postOnMastodonFlag = false;
        $this->chainFlag          = false;
    }

    public function setUser(Authenticatable $user): CheckInRequestDto {
        $this->user = $user;
        return $this;
    }

    public function setTrip(Trip $trip): CheckInRequestDto {
        $this->trip = $trip;
        return $this;
    }

    public function setOrigin(Station $origin): CheckInRequestDto {
        $this->origin = $origin;
        return $this;
    }

    public function setDeparture(Carbon $departure): CheckInRequestDto {
        $this->departure = $departure;
        return $this;
    }

    public function setDestination(Station $destination): CheckInRequestDto {
        $this->destination = $destination;
        return $this;
    }

    public function setArrival(Carbon $arrival): CheckInRequestDto {
        $this->arrival = $arrival;
        return $this;
    }

    public function setTravelReason(Business $travelReason): CheckInRequestDto {
        $this->travelReason = $travelReason;
        return $this;
    }

    public function setStatusVisibility(StatusVisibility $statusVisibility): CheckInRequestDto {
        $this->statusVisibility = $statusVisibility;
        return $this;
    }

    public function setBody(?string $body): CheckInRequestDto {
        $this->body = $body;
        return $this;
    }

    public function setEvent(?Event $event): CheckInRequestDto {
        $this->event = $event;
        return $this;
    }

    public function setForceFlag(bool $forceFlag): CheckInRequestDto {
        $this->forceFlag = $forceFlag;
        return $this;
    }

    public function setPostOnMastodonFlag(bool $postOnMastodonFlag): CheckInRequestDto {
        $this->postOnMastodonFlag = $postOnMastodonFlag;
        return $this;
    }

    public function setChainFlag(bool $chainFlag): CheckInRequestDto {
        $this->chainFlag = $chainFlag;
        return $this;
    }
}
