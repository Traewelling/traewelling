<?php

namespace App\Http\Controllers\API\v1;

use App\Dto\Coordinate;
use App\Dto\Internal\CheckInRequestDto;
use App\Dto\Internal\CheckinSuccessDto;
use App\Dto\Internal\Departure;
use App\Dto\Transport\Station as StationDto;
use App\Enum\Business;
use App\Enum\StationIdentifierType;
use App\Enum\StatusVisibility;
use App\Enum\TravelType;
use App\Exceptions\Checkin\AlreadyCheckedInException;
use App\Exceptions\CheckInCollisionException;
use App\Exceptions\CheckinException;
use App\Exceptions\DataProviderException;
use App\Exceptions\StationNotOnTripException;
use App\Http\Controllers\Backend\Transport\StationController;
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Http\Resources\CheckinSuccessResource;
use App\Http\Resources\DepartureResource;
use App\Http\Resources\StationResource;
use App\Http\Resources\TripResource;
use App\Hydrators\CheckinRequestHydrator;
use App\Models\Station;
use App\Models\Status;
use App\Models\User;
use App\Notifications\YouHaveBeenCheckedIn;
use App\Repositories\CheckinHydratorRepository;
use App\Repositories\StationRepository;
use App\Services\GeoService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use OpenApi\Attributes as OA;
use Throwable;

#[OA\Schema(
    schema: 'CheckinRequestBody',
    title: 'CheckinRequestBody',
    description: 'Fields for creating a transit checkin',
    properties: [
        new OA\Property(property: 'body', type: 'string', example: 'Meine erste Fahrt nach Knuffingen!', nullable: true, maxLength: 280),
        new OA\Property(property: 'business', ref: '#/components/schemas/Business'),
        new OA\Property(property: 'visibility', ref: '#/components/schemas/StatusVisibility'),
        new OA\Property(property: 'eventId', description: 'Id of an event the status should be connected to', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'toot', description: 'Should this status be posted to mastodon?', type: 'boolean', example: false, nullable: true),
        new OA\Property(property: 'chainPost', description: 'Should this status be posted to mastodon as a chained post?', type: 'boolean', example: false, nullable: true),
        new OA\Property(property: 'ibnr', description: 'If true, `start` and `destination` can be supplied as IBNR. Otherwise Träwelling-ID. Default: false.', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'tripId', description: 'The tripId for the trip to check into', type: 'string', example: 'b37ff515-22e1-463c-94de-3ad7964b5cb8', nullable: true),
        new OA\Property(property: 'lineName', description: 'The line name for the trip to check into', type: 'string', example: 'S 4', nullable: true),
        new OA\Property(property: 'start', description: 'Station-ID of the starting point (see `ibnr`)', type: 'integer', example: 8000191),
        new OA\Property(property: 'destination', description: 'Station-ID of the destination (see `ibnr`)', type: 'integer', example: 8000192),
        new OA\Property(property: 'departure', description: 'Timestamp of the departure', type: 'string', format: 'date-time', example: '2022-12-19T20:41:00+01:00'),
        new OA\Property(property: 'arrival', description: 'Timestamp of the arrival', type: 'string', format: 'date-time', example: '2022-12-19T20:42:00+01:00'),
        new OA\Property(property: 'force', description: 'If true, the checkin is created even on collision. No points awarded.', type: 'boolean', example: false, nullable: true),
        new OA\Property(property: 'with', description: 'Also check in these user IDs (max. 10). Requires mutual follow.', type: 'array', items: new OA\Items(type: 'integer', example: 1), nullable: true),
    ],
)]
class TransportController extends Controller
{
    private StationRepository $stationRepository;

    public function __construct(StationRepository $stationRepository)
    {
        parent::__construct();
        $this->stationRepository = $stationRepository;
    }

    /**
     * @todo: This endpoint needs to be restructured to use own Resources! Currently we just throw the raw db-rest response.
     */
    #[OA\Get(
        path: '/station/{id}/departures',
        operationId: 'getDepartures',
        description: 'Get departures from a station.',
        summary: 'Get departures from a station',
        security: [['passport' => ['create-statuses']], ['token' => []]],
        tags: ['Checkin'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Träwelling-ID of the station (you can look this up with [trainStationAutocomplete](#/Checkin/trainStationAutocomplete))',
                in: 'path',
                required: true,
            ),
            new OA\Parameter(
                name: 'when',
                description: 'When to get the departures (default: now). If you omit the timezone, the datetime is interpreted as localtime. This is especially helpful when träwelling abroad.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    format: 'date-time',
                    example: '2020-01-01T12:00:00.000Z',
                ),
            ),
            new OA\Parameter(
                name: 'travelType',
                description: 'Means of transport (default: all)',
                in: 'query',
                required: false,
                schema: new OA\Schema(ref: '#/components/schemas/TravelType'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/DepartureResource'),
                        ),
                        new OA\Property(
                            property: 'meta',
                            properties: [
                                new OA\Property(
                                    property: 'station',
                                    ref: '#/components/schemas/Station',
                                ),
                                new OA\Property(
                                    property: 'times',
                                    properties: [
                                        new OA\Property(
                                            property: 'now',
                                            type: 'string',
                                            format: 'date-time',
                                            example: '2020-01-01T12:00:00.000Z',
                                        ),
                                        new OA\Property(
                                            property: 'prev',
                                            type: 'string',
                                            format: 'date-time',
                                            example: '2020-01-01T11:45:00.000Z',
                                        ),
                                        new OA\Property(
                                            property: 'next',
                                            type: 'string',
                                            format: 'date-time',
                                            example: '2020-01-01T12:15:00.000Z',
                                        ),
                                    ],
                                    type: 'object',
                                ),
                                new OA\Property(
                                    property: 'removedLicenses',
                                    description: 'List of licenses that were filtered out',
                                    type: 'array',
                                    items: new OA\Items(
                                        oneOf: [
                                            new OA\Schema(
                                                type: 'string',
                                                example: 'FR: fr_horaires-sncf.gtfs',
                                            ),
                                            new OA\Schema(ref: '#/components/schemas/LicenseDto'),
                                        ],
                                    ),
                                ),
                                new OA\Property(
                                    property: 'removedCount',
                                    description: 'Number of removed entries due to license filtering',
                                    type: 'integer',
                                    example: 2,
                                ),
                            ],
                            type: 'object',
                        ),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'Station not found'),
            new OA\Response(response: 422, description: 'Invalid input'),
            new OA\Response(response: 502, description: 'Error with our data provider'),
        ],
    )]
    public function getDepartures(Request $request, int $stationId): JsonResponse
    {
        $validated = $request->validate([
            'when' => ['nullable', 'date'],
            'travelType' => ['nullable', new Enum(TravelType::class)],
        ]);

        $timestamp = isset($validated['when']) ? Carbon::parse($validated['when']) : now();
        $station = Station::findOrFail($stationId);

        try {
            $filtered = $this->dataProvider->getFilteredDepartures(
                station: $station,
                when: $timestamp,
                type: TravelType::tryFrom($validated['travelType'] ?? null),
                localtime: isset($validated['when']) && !preg_match('(\+|Z)', $validated['when'])
            );

            $departures = $filtered->departures->sortBy(function (Departure $departure) {
                return ($departure->realDeparture ?? $departure->plannedDeparture)->toIso8601String();
            });

            $times = $departures->map(fn (Departure $d) => $d->realDeparture ?? $d->plannedDeparture)->filter()->sort();
            $prev = $timestamp->clone()->subMinutes(15);
            $next = $times->isNotEmpty() ? $times->last()->clone()->addMinute() : $timestamp->clone()->addMinutes(15);

            return $this->sendResponse(
                data: DepartureResource::collection($departures->values()),
                additional: [
                    'meta' => [
                        'station' => StationDto::fromModel($station),
                        'times' => [
                            'now' => $timestamp,
                            'prev' => $prev,
                            'next' => $next,
                        ],
                        'removedLicenses' => $filtered->removedEntries,
                        'removedCount' => $filtered->removedCount,
                    ],
                ]
            );
        } catch (DataProviderException) {
            return $this->sendError(__('messages.exception.generalHafas', [], 'en'), 502);
        } catch (ModelNotFoundException) {
            return $this->sendError(__('controller.transport.no-station-found', [], 'en'));
        } catch (Exception $exception) {
            report($exception);

            return $this->sendError('An unknown error occurred.', 500, null, $exception);
        }
    }

    #[OA\Get(
        path: '/trains/trip',
        operationId: 'getTrainTrip',
        summary: 'Get the stopovers and trip information for a given train',
        security: [['passport' => ['create-statuses']], ['token' => []]],
        tags: ['Checkin'],
        parameters: [
            new OA\Parameter(
                name: 'hafasTripId',
                description: 'HAFAS trip ID (fetched from departures)',
                in: 'query',
                required: true,
                example: '1|323306|1|80|17072022',
            ),
            new OA\Parameter(
                name: 'lineName',
                description: 'line name for that train',
                in: 'query',
                required: true,
                example: 'S 4',
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/TripResource'),
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'No station found'),
            new OA\Response(
                response: 503,
                description: 'There has been an error with our data provider',
            ),
        ],
    )]
    public function getTrip(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'hafasTripId' => ['required', 'string'],
            'lineName' => ['required', 'string'],
        ]);

        try {
            $trip = app(CheckinHydratorRepository::class)
                ->getHafasTrip(
                    tripID: $validated['hafasTripId'],
                    lineName: $validated['lineName']
                )
                ->loadMissing(['stopovers', 'originStation', 'destinationStation']);

            return $this->sendResponse(data: new TripResource($trip));
        } catch (DataProviderException $exception) {
            report($exception);

            return $this->sendError(__('messages.exception.hafas.502', [], 'en'), 503);
        }
    }

    #[OA\Get(
        path: '/trains/station/nearby',
        operationId: 'trainStationsNearby',
        description: 'Returns the nearest station to the given coordinates',
        summary: 'Location based search for stations',
        security: [['passport' => ['create-statuses']], ['token' => []]],
        tags: ['Checkin'],
        parameters: [
            new OA\Parameter(
                name: 'latitude',
                description: 'latitude',
                in: 'query',
                required: true,
                example: 48.991,
            ),
            new OA\Parameter(
                name: 'longitude',
                description: 'longitude',
                in: 'query',
                required: true,
                example: 8.4005,
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Station'),
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'No station found'),
            new OA\Response(
                response: 503,
                description: 'There has been an error with our data provider',
            ),
        ],
    )]
    public function getNextStationByCoordinates(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'min:-90', 'max:90'],
            'longitude' => ['required', 'numeric', 'min:-180', 'max:180'],
        ]);

        try {
            $nearestStation = $this->dataProvider->getNearbyStations(
                latitude: $validated['latitude'],
                longitude: $validated['longitude'],
                results: 1
            )->first();
        } catch (DataProviderException) {
            $bbox = (new GeoService())->getBoundingBox(new Coordinate($validated['latitude'], $validated['longitude']), 100, 6);

            $nearestStation = Station::whereBetween('latitude', [$bbox->lowerRight->latitude, $bbox->upperLeft->latitude])
                ->whereBetween('longitude', [$bbox->lowerRight->longitude, $bbox->upperLeft->longitude])
                ->whereHas('stationIdentifiers', fn ($q) => $q->where('type', StationIdentifierType::DE_DB_IBNR->value))
                ->orderBy('id', 'asc')
                ->first();
        }

        if ($nearestStation === null) {
            return $this->sendError(__('controller.transport.no-station-found', [], 'en'));
        }

        return $this->sendResponse(new StationResource($nearestStation));
    }

    #[OA\Post(
        path: '/trains/checkin',
        operationId: 'createCheckin',
        summary: 'Check in to a trip.',
        security: [['passport' => ['create-statuses']], ['token' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/CheckinRequestBody'),
        ),
        tags: ['Checkin'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'successful operation',
                content: new OA\JsonContent(ref: '#/components/schemas/CheckinSuccessResource'),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(
                response: 403,
                description: 'Forbidden — one or more users in `with` cannot be checked in',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'You are not allowed to check in the following users: 1'),
                        new OA\Property(
                            property: 'meta',
                            properties: [
                                new OA\Property(property: 'invalidUsers', type: 'array', items: new OA\Items(type: 'integer', example: 1)),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 409, description: 'Checkin collision'),
        ],
    )]
    public function create(Request $request): JsonResponse
    {
        $this->authorize('create', Status::class);

        $withUsers = null;
        $validated = $request->validate([
            'body' => ['nullable', 'max:280'],
            'business' => ['nullable', new Enum(Business::class)],
            'visibility' => ['nullable', new Enum(StatusVisibility::class)],
            'eventId' => ['nullable', 'integer', 'exists:events,id'],
            'toot' => ['nullable', 'boolean'],
            'chainPost' => ['nullable', 'boolean'],
            'ibnr' => ['nullable', 'boolean'],
            'tripId' => ['required'],
            'lineName' => ['required'],
            'start' => ['required', 'numeric'],
            'destination' => ['required', 'numeric'],
            'departure' => ['required', 'date'],
            'arrival' => ['required', 'date'],
            'force' => ['nullable', 'boolean'],
            'with' => ['nullable', 'array', 'max:10'],
        ]);
        if (isset($validated['with'])) {
            $withUsers = User::whereIn('id', $validated['with'])->get();
            $forbiddenUsers = collect();
            foreach ($withUsers as $user) {
                if (!Auth::user()?->can('checkin', $user)) {
                    $forbiddenUsers->push($user);
                }
            }
            if ($forbiddenUsers->isNotEmpty()) {
                $forbiddenUserIds = $forbiddenUsers->pluck('id')->toArray();

                return response()->json(
                    data: [
                        'message' => 'You are not allowed to check in the following users: ' . implode(',', $forbiddenUserIds),
                        'meta' => [
                            'invalidUsers' => $forbiddenUserIds,
                        ],
                    ],
                    status: 403
                );
            }
        }

        try {
            $dto = (new CheckinRequestHydrator($validated))->hydrateFromApi();
            $checkinResponse = TrainCheckinController::checkin($dto);

            // if isset, check in the other users with their default values
            $this->checkinOtherUsers($withUsers, $dto, $checkinResponse);

            return $this->sendResponse(new CheckinSuccessResource($checkinResponse), 201);
        } catch (CheckInCollisionException $exception) {
            return $this->sendError([
                'status_id' => $exception->checkin->status_id,
                'lineName' => $exception->checkin->trip->linename,
            ], 409);

        } catch (StationNotOnTripException) {
            return $this->sendError('Given stations are not on the trip/have wrong departure/arrival.', 400);
        } catch (DataProviderException|CheckinException $exception) {
            return $this->sendError($exception->getMessage(), 400);
        } catch (AlreadyCheckedInException) {
            return $this->sendError(__('messages.exception.already-checkedin', [], 'en'), 400);
        } catch (Exception $exception) {
            report($exception);

            return $this->sendError('An unknown error occurred.', 500, null, $exception);
        }
    }

    #[OA\Put(
        path: '/station/{id}/home',
        operationId: 'setHomeStation',
        summary: 'Set a station as home station',
        security: [['passport' => ['create-statuses']], ['token' => []]],
        tags: ['Checkin'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Träwelling-ID of the station',
                in: 'path',
                required: true,
                example: 1234,
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Station')],
                    type: 'object',
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'Station not found'),
            new OA\Response(response: 500, description: 'Unknown error'),
        ],
    )]
    public function setHome(int $stationId): JsonResponse
    {
        try {
            $station = Station::findOrFail($stationId);

            auth()->user()?->update([
                'home_id' => $station->id,
            ]);

            return $this->sendResponse(
                data: new StationResource($station),
            );
        } catch (ModelNotFoundException) {
            return $this->sendError('The station could not be found');
        } catch (Exception $exception) {
            report($exception);

            return $this->sendError('Unknown error', 500);
        }
    }

    #[OA\Get(
        path: '/trains/station/autocomplete/{query}',
        operationId: 'trainStationAutocomplete',
        description: 'This request returns an array of max. 10 station objects matching the query. **CAUTION:** All slashes (as well as encoded to %2F) in {query} need to be replaced, preferrably by a space (%20)',
        summary: 'Autocomplete for stations',
        security: [['passport' => ['create-statuses']], ['token' => []]],
        tags: ['Checkin'],
        parameters: [
            new OA\Parameter(
                name: 'query',
                description: 'station query',
                in: 'path',
                example: 'Karls',
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/StationResource'),
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(
                response: 503,
                description: 'There has been an error with our data provider',
            ),
        ],
    )]
    public function getTrainStationAutocomplete(string $query): JsonResponse
    {
        try {
            $trainAutocompleteResponse = (new StationController())->search($query);

            return $this->sendResponse(StationResource::collection($trainAutocompleteResponse));
        } catch (DataProviderException $e) {
            // check if app is in debug mode
            return $this->sendError(
                'There has been an error with our data provider',
                503,
                null,
                $e
            );
        }
    }

    #[OA\Get(
        path: '/trains/station/history',
        operationId: 'trainStationHistory',
        description: 'This request returns an array of max. 10 most recent station objects that the user has arrived at.',
        summary: 'History for stations',
        security: [['passport' => ['create-statuses']], ['token' => []]],
        tags: ['Checkin'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Station'),
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ],
    )]
    public function getTrainStationHistory(): AnonymousResourceCollection
    {
        $latestArrivals = $this->stationRepository->getLatestArrivalsForUser(\auth()->user(), 10);

        return StationResource::collection($latestArrivals);
    }

    public function checkinOtherUsers(?Collection $withUsers, CheckInRequestDto $dto, CheckinSuccessDto $checkinResponse): void
    {
        $by = $dto->user;
        foreach ($withUsers ?? [] as $user) {
            $dto->setUser($user);
            $dto->setBody(null);
            $dto->setStatusVisibility($user->default_status_visibility);
            $dto->setPostOnMastodonFlag(false);
            try {
                $checkin = TrainCheckinController::checkin($dto, $by);
            } catch (Throwable) {
                continue;
            }
            $user->notify(new YouHaveBeenCheckedIn($checkin->status, auth()->user()));
            $checkinResponse->alsoOnThisConnection->push($checkin->status);
        }
    }
}
