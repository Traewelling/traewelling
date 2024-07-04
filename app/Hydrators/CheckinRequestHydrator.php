<?php

namespace App\Hydrators;

use App\Dto\Internal\CheckInRequestDto;
use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Exceptions\HafasException;
use App\Http\Controllers\HafasController;
use App\Models\Event;
use App\Models\Station;
use App\Models\Stopover;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

class CheckinRequestHydrator
{
    private CheckInRequestDto $dto;
    private array             $validated;
    private string            $searchKey;
    private Authenticatable   $user;

    public function __construct(array $validated, ?Authenticatable $user = null, ?CheckInRequestDto $dto = null) {
        $this->validated = $validated;
        $this->dto       = $dto ?? new CheckInRequestDto();
        $this->user      = $user ?? Auth::user();
    }

    public function hydrateFromApi(): CheckInRequestDto {
        $this->parseApiFields();

        return $this->dto;
    }

    public function hydrateFromAdmin(): CheckInRequestDto {
        $this->parseAdminFields();

        return $this->dto;
    }

    private function parseAdminFields(): void {
        $this->parseDefaultFields();
        $destinationStopover = Stopover::findOrFail($this->validated['destinationStopover']);

        $this->dto->setDestination($destinationStopover->station)
            ->setArrival($destinationStopover->arrival_planned);
    }

    private function parseApiFields(): void {
        $this->parseDefaultFields();

        $arrival            = Carbon::parse($this->validated['arrival']);
        $destinationStation = Station::where($this->searchKey, $this->validated['destination'])->first();

        $this->dto->setArrival($arrival)
                  ->setDestination($destinationStation);
    }

    /**
     * @throws HafasException
     */
    private function parseDefaultFields(): void {
        $this->searchKey = empty($this->validated['ibnr']) ? 'id' : 'ibnr';
        $originStation   = Station::where($this->searchKey, $this->validated['start'])->first();
        $departure       = Carbon::parse($this->validated['departure']);
        $travelReason    = Business::tryFrom($this->validated['business'] ?? Business::PRIVATE->value);
        $visibility      = StatusVisibility::tryFrom($this->validated['visibility'] ?? StatusVisibility::PUBLIC->value);
        $event           = isset($this->validated['eventId']) ? Event::find($this->validated['eventId']) : null;

        $this->dto->setUser($this->user)
                  ->setTrip(HafasController::getHafasTrip($this->validated['tripId'], $this->validated['lineName']))
                  ->setOrigin($originStation)
                  ->setDeparture($departure)
                  ->setTravelReason($travelReason)
                  ->setStatusVisibility($visibility)
                  ->setBody($this->validated['body'] ?? null)
                  ->setEvent($event)
                  ->setForce(!empty($this->validated['force']))
                  ->setPostOnMastodon(!empty($this->validated['toot']))
                  ->setShouldChain(!empty($this->validated['chainPost']));
    }
}
