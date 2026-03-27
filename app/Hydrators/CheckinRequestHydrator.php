<?php

declare(strict_types=1);

namespace App\Hydrators;

use App\Dto\Internal\CheckInRequestDto;
use App\Enum\Business;
use App\Enum\StationIdentifierType;
use App\Enum\StatusVisibility;
use App\Exceptions\DataProviderException;
use App\Http\Requests\CheckinRequest;
use App\Models\Station;
use App\Repositories\EventRepository;
use App\Repositories\StationRepository;
use App\Repositories\TripRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use JsonException;

class CheckinRequestHydrator
{
    private CheckInRequestDto $dto;

    private CheckinRequest $request;

    private Authenticatable $user;

    private StationRepository $stationRepository;

    private EventRepository $eventRepository;

    private TripRepository $tripRepository;

    public function __construct(
        CheckinRequest $validated,
        ?Authenticatable $user = null,
        ?CheckInRequestDto $dto = null,
        ?StationRepository $stationRepository = null,
        ?EventRepository $eventRepository = null,
        ?TripRepository $tripRepository = null
    ) {
        $this->request = $validated;
        $this->dto = $dto ?? new CheckInRequestDto();
        $this->user = $user ?? Auth::user();
        $this->stationRepository = $stationRepository ?? new StationRepository();
        $this->eventRepository = $eventRepository ?? new EventRepository();
        $this->tripRepository = $tripRepository ?? new TripRepository();
    }

    /**
     * @throws DataProviderException
     * @throws JsonException
     */
    public function hydrateFromApi(): CheckInRequestDto
    {
        $this->parseApiFields();

        return $this->dto;
    }

    /**
     * @throws DataProviderException
     * @throws JsonException
     */
    private function parseApiFields(): void
    {
        $departure = Carbon::parse($this->request->departure);
        $arrival = Carbon::parse($this->request->arrival);
        $travelReason = Business::tryFrom($this->request->business ?? Business::PRIVATE->value);
        $visibility = StatusVisibility::tryFrom($this->request->visibility ?? StatusVisibility::PUBLIC->value);
        $event = isset($this->request->eventId) ? $this->eventRepository->getById($this->request->eventId) : null;
        $trip = $this->tripRepository->getByIdentifier($this->request->tripId, $this->request->lineName);

        $this->dto->setUser($this->user)
            ->setTrip($trip)
            ->setOrigin($this->getOriginStation())
            ->setDestination($this->getDestinationStation())
            ->setArrival($arrival)
            ->setDeparture($departure)
            ->setTravelReason($travelReason)
            ->setStatusVisibility($visibility)
            ->setBody($this->request->body ?? null)
            ->setEvent($event)
            ->setForceFlag(!empty($this->request->force))
            ->setPostOnMastodonFlag(!empty($this->request->toot))
            ->setChainFlag(!empty($this->request->chainPost))
            ->setUserIds($this->request->with ?? []);
    }

    private function getOriginStation(): ?Station
    {
        if ($this->request->ibnr && !empty($this->request->start)) {
            return $this->stationRepository->getByIdentifier((string) $this->request->start, StationIdentifierType::DE_DB_IBNR);
        }
        if ($this->request->start && !$this->request->ibnr) {
            return $this->stationRepository->getById((int) $this->request->start);
        }
        if (!empty($this->request->startIdentifier) && !empty($this->request->startIdentifierType)) {
            return $this->stationRepository->getByIdentifier($this->request->startIdentifier, StationIdentifierType::from($this->request->startIdentifierType));
        }

        return null;
    }

    private function getDestinationStation(): ?Station
    {
        if ($this->request->ibnr && !empty($this->request->destination)) {
            return $this->stationRepository->getByIdentifier((string) $this->request->destination, StationIdentifierType::DE_DB_IBNR);
        }
        if ($this->request->destination && !$this->request->ibnr) {
            return $this->stationRepository->getById((int) $this->request->destination);
        }
        if (!empty($this->request->destinationIdentifier) && !empty($this->request->destinationIdentifierType)) {
            return $this->stationRepository->getByIdentifier($this->request->destinationIdentifier, StationIdentifierType::from($this->request->destinationIdentifierType));
        }

        return null;
    }
}
