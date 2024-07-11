<?php

declare(strict_types=1);

namespace App\Hydrators;

use App\Dto\Internal\CheckInRequestDto;
use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Exceptions\HafasException;
use App\Models\Event;
use App\Repositories\CheckinHydratorRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use JsonException;

class CheckinRequestHydrator
{
    private CheckInRequestDto         $dto;
    private array                     $validated;
    private string                    $searchKey;
    private Authenticatable           $user;
    private CheckinHydratorRepository $repository;

    public function __construct(
        array                      $validated,
        ?Authenticatable           $user = null,
        ?CheckInRequestDto         $dto = null,
        ?CheckinHydratorRepository $repository = null
    ) {
        $this->validated  = $validated;
        $this->dto        = $dto ?? new CheckInRequestDto();
        $this->user       = $user ?? Auth::user();
        $this->repository = $repository ?? new CheckinHydratorRepository();
    }

    /**
     * @throws HafasException
     */
    public function hydrateFromApi(): CheckInRequestDto {
        $this->parseApiFields();

        return $this->dto;
    }

    /**
     * @throws HafasException
     * @throws JsonException
     */
    public function hydrateFromAdmin(): CheckInRequestDto {
        $this->parseAdminFields();

        return $this->dto;
    }

    /**
     * @throws HafasException
     * @throws JsonException
     */
    private function parseAdminFields(): void {
        $this->parseDefaultFields();
        $destinationStopover = $this->repository->findOrFailStopover($this->validated['destinationStopover']);

        $this->dto->setDestination($destinationStopover->station)
                  ->setArrival($destinationStopover->arrival_planned);
    }

    /**
     * @throws HafasException
     * @throws JsonException
     */
    private function parseApiFields(): void {
        $this->parseDefaultFields();

        $arrival            = Carbon::parse($this->validated['arrival']);
        $destinationStation = $this->repository->getOneStation($this->searchKey, $this->validated['destination']);

        $this->dto->setArrival($arrival)
                  ->setDestination($destinationStation);
    }

    /**
     * @throws HafasException
     * @throws JsonException
     */
    private function parseDefaultFields(): void {
        $this->searchKey = empty($this->validated['ibnr']) ? 'id' : 'ibnr';
        $originStation   = $this->repository->getOneStation($this->searchKey, $this->validated['start']);
        $departure       = Carbon::parse($this->validated['departure']);
        $travelReason    = Business::tryFrom($this->validated['business'] ?? Business::PRIVATE->value);
        $visibility      = StatusVisibility::tryFrom($this->validated['visibility'] ?? StatusVisibility::PUBLIC->value);
        $event           = isset($this->validated['eventId']) ? $this->repository->findEvent($this->validated['eventId']) : null;
        $trip            = $this->repository->getHafasTrip($this->validated['tripId'], $this->validated['lineName']);

        $this->dto->setUser($this->user)
                  ->setTrip($trip)
                  ->setOrigin($originStation)
                  ->setDeparture($departure)
                  ->setTravelReason($travelReason)
                  ->setStatusVisibility($visibility)
                  ->setBody($this->validated['body'] ?? null)
                  ->setEvent($event)
                  ->setForceFlag(!empty($this->validated['force']))
                  ->setPostOnMastodonFlag(!empty($this->validated['toot']))
                  ->setChainFlag(!empty($this->validated['chainPost']));
    }
}
