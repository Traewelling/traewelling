<?php

declare(strict_types=1);

namespace App\Services\Checkin;

use App\DataProviders\DataProviderInterface;
use App\Dto\IdentifierMoveResult;
use App\Dto\StationUsageMoveResultDto;
use App\Enum\StationIdentifierType;
use App\Models\Station;
use App\Models\StationIdentifier;
use App\Models\User;
use App\Repositories\StationRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StationService
{
    public function __construct(
        private StationRepository $stationRepository,
        private DataProviderInterface $dataProvider
    ) {}

    public function getLatestArrivalsForUser(User $user, int $limit): Collection
    {
        return $this->stationRepository->getLatestArrivalsForUser(user: $user, maxCount: $limit);
    }

    public function search(string $search): Collection
    {
        if (!is_numeric($search) && strlen($search) <= 5 && ctype_upper($search)) {
            $stations = $this->stationRepository->getStationsByFuzzyRilIdentifier($search);
            if ($stations->isNotEmpty()) {
                return $stations;
            }
        }

        if (preg_match('/^Q\d+$/', $search)) {
            return $this->stationRepository->getStationsByWikidataId($search);
        }

        $stations = $this->dataProvider->getStations($search);
        if ($stations->count() < 10) {
            $remaining = 10 - $stations->count();
            $dbStations = $this->stationRepository->getStationByName($search);
            // remove duplicates
            $dbStations = $dbStations->filter(function (Station $station) use ($stations) {
                return !$stations->contains('id', $station->id);
            });
            $stations = $stations->merge($dbStations->take($remaining));
        }

        return $stations;
    }

    public function moveIdentifier(StationIdentifier $identifier, Station $targetStation, User $actor): IdentifierMoveResult
    {
        $sourceStation = $identifier->station;

        $result = DB::transaction(function () use ($identifier, $targetStation): IdentifierMoveResult {
            $updatedRouteSegments = $this->stationRepository->moveIdentifierToStation($identifier, $targetStation);
            $stopoverResult = $this->stationRepository->moveStopoversOfIdentifier($identifier, $targetStation);
            $updatedTrips = $this->stationRepository->updateTripTerminalStations($stopoverResult['tripIds'], $identifier);

            return new IdentifierMoveResult(
                movedStopovers: $stopoverResult['moved'],
                skippedStopovers: $stopoverResult['skipped'],
                updatedTrips: $updatedTrips,
                updatedRouteSegments: $updatedRouteSegments,
            );
        });

        activity()->causedBy($actor)
            ->performedOn($identifier)
            ->log(
                "Moved identifier {$identifier->identifier} ({$identifier->type->value}) from station {$sourceStation->name} ({$sourceStation->id}) to {$targetStation->name} ({$targetStation->id}): "
                . "{$result->movedStopovers} stopovers moved ({$result->skippedStopovers} skipped), {$result->updatedTrips} trips and {$result->updatedRouteSegments} route segments updated"
            );

        return $result;
    }

    public function moveStationReferences(Station $source, Station $target, array $types, User $actor): StationUsageMoveResultDto
    {
        $result = DB::transaction(fn (): StationUsageMoveResultDto => new StationUsageMoveResultDto(
            stopovers: in_array('stopovers', $types, true) ? $this->stationRepository->moveStopoversToStation($source, $target) : 0,
            trips: in_array('trips', $types, true) ? $this->stationRepository->moveTripTerminalsToStation($source, $target) : 0,
            events: in_array('events', $types, true) ? $this->stationRepository->moveEventsToStation($source, $target) : 0,
            eventSuggestions: in_array('eventSuggestions', $types, true) ? $this->stationRepository->moveEventSuggestionsToStation($source, $target) : 0,
            routeSegments: in_array('routeSegments', $types, true) ? $this->stationRepository->moveRouteSegmentsToStation($source, $target, onlyWithoutIdentifier: true) : 0,
            homeUsers: in_array('homeUsers', $types, true) ? $this->stationRepository->moveHomeUsersToStation($source, $target) : 0,
        ));

        activity()->causedBy($actor)
            ->performedOn($source)
            ->log(
                "Moved references from station {$source->name} ({$source->id}) to {$target->name} ({$target->id}): "
                . "{$result->stopovers} stopovers, {$result->trips} trip terminals, {$result->events} events, "
                . "{$result->eventSuggestions} event suggestions, {$result->routeSegments} route segment sides, {$result->homeUsers} home stations"
            );

        return $result;
    }

    public function createIdentifier(Station $station, StationIdentifierType $type, string $value, User $actor): void
    {
        $identifier = $this->stationRepository->createIdentifier($station, $type, $value);

        activity()->causedBy($actor)
            ->performedOn($identifier)
            ->log("Added identifier {$value} ({$type->value}) to station {$station->name} ({$station->id})");
    }

    public function updateIdentifierValues(StationIdentifier $identifier, StationIdentifierType $type, string $value, User $actor): void
    {
        $old = "{$identifier->identifier} ({$identifier->type->value})";

        $this->stationRepository->updateIdentifierValues($identifier, $type, $value);

        activity()->causedBy($actor)
            ->performedOn($identifier)
            ->log("Updated identifier on station ({$identifier->station_id}): {$old} -> {$value} ({$type->value})");
    }
}
