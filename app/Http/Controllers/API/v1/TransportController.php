<?php

namespace App\Http\Controllers\API\v1;

use App\Dto\Coordinate;
use App\Dto\Internal\Departure;
use App\Dto\LicenseDto;
use App\Dto\Transport\Station as StationDto;
use App\Enum\StationIdentifierType;
use App\Enum\TravelType;
use App\Exceptions\Checkin\AlreadyCheckedInException;
use App\Exceptions\CheckInCollisionException;
use App\Exceptions\CheckinException;
use App\Exceptions\DataProviderException;
use App\Exceptions\NotAllowedToCheckinOtherUserException;
use App\Exceptions\StationNotOnTripException;
use App\Http\Requests\CheckinRequest;
use App\Http\Resources\CheckinSuccessResource;
use App\Http\Resources\DepartureResource;
use App\Http\Resources\StationResource;
use App\Http\Resources\StatusResource;
use App\Http\Resources\TripResource;
use App\Hydrators\CheckinRequestHydrator;
use App\Models\Station;
use App\Models\Status;
use App\Repositories\TripRepository;
use App\Services\Checkin\CheckinService;
use App\Services\Checkin\StationService;
use App\Services\GeoService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rules\Enum;
use OpenApi\Attributes as OA;

class TransportController extends Controller
{
    public function __construct(
        private CheckinService $checkinService,
        private StationService $stationService
    ) {
        parent::__construct();
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
                schema: new OA\Schema(ref: TravelType::class),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    required: ['data', 'meta'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: DepartureResource::class),
                        ),
                        new OA\Property(
                            property: 'meta',
                            required: ['station', 'times', 'removedLicenses', 'removedCount'],
                            properties: [
                                new OA\Property(
                                    property: 'station',
                                    ref: StationDto::class,
                                ),
                                new OA\Property(
                                    property: 'times',
                                    required: ['now', 'prev', 'next'],
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
                                            new OA\Schema(ref: LicenseDto::class),
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
        $station->loadMissing('stationIdentifiers');

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
        } catch (DataProviderException $exception) {
            return $this->sendError($exception->getMessage(), 502);
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
                    required: ['data'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: TripResource::class,
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
            $trip = app(TripRepository::class)
                ->getByIdentifier(
                    tripID: $validated['hafasTripId'],
                    lineName: $validated['lineName']
                )
                ->loadMissing(['stopovers', 'originStation', 'destinationStation', 'continuationTrip.destinationStation']);

            return $this->sendResponse(data: new TripResource($trip));
        } catch (DataProviderException $exception) {
            report($exception);

            return $this->sendError(__('messages.exception.motis.502', [], 'en'), 503);
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
                    required: ['data'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: Station::class),
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
            content: new OA\JsonContent(ref: CheckinRequest::class),
        ),
        tags: ['Checkin'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'successful operation',
                content: new OA\JsonContent(ref: CheckinSuccessResource::class),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(
                response: 403,
                description: 'Forbidden — one or more users in `with` cannot be checked in',
                content: new OA\JsonContent(
                    required: ['message', 'meta'],
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'You are not allowed to check in the following users: 1'),
                        new OA\Property(
                            property: 'meta',
                            required: ['invalidUsers'],
                            properties: [
                                new OA\Property(property: 'invalidUsers', type: 'array', items: new OA\Items(type: 'integer', example: 1)),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
            new OA\Response(
                response: 409,
                description: 'Checkin collision',
                content: new OA\JsonContent(
                    required: ['message', 'data'],
                    properties: [
                        new OA\Property(
                            property: 'message',
                            description: 'Deprecated: use data.conflicts instead',
                            required: ['status_id', 'lineName'],
                            properties: [
                                new OA\Property(property: 'status_id', type: 'integer', nullable: true, deprecated: true),
                                new OA\Property(property: 'lineName', type: 'string', nullable: true, deprecated: true),
                            ],
                            type: 'object',
                            deprecated: true,
                        ),
                        new OA\Property(
                            property: 'data',
                            required: ['conflicts'],
                            properties: [
                                new OA\Property(
                                    property: 'conflicts',
                                    type: 'array',
                                    items: new OA\Items(ref: StatusResource::class)
                                ),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
        ],
    )]
    public function create(CheckinRequest $request): JsonResponse
    {
        $this->authorize('create', Status::class);

        try {
            $dto = new CheckinRequestHydrator($request)->hydrateFromApi();
            $checkinResponse = $this->checkinService->checkin($dto);

            return $this->sendResponse(new CheckinSuccessResource($checkinResponse), 201);
        } catch (NotAllowedToCheckinOtherUserException $exception) {
            return $this->sendError(
                error: 'You are not allowed to check in the following users: ' . implode(',', $exception->users),
                code: 403,
                additional: [
                    'invalidUsers' => $exception->users,
                ]
            );
        } catch (CheckInCollisionException $exception) {
            $statuses = Status::with([
                'event', 'likes', 'user', 'createdByUser',
                'checkin.originStopover.station', 'checkin.destinationStopover.station',
                'checkin.trip.operator', 'checkin.trip.motisSourceLicense',
                'checkin.statusTags', 'tags', 'mentions.mentioned', 'ticket', 'client',
            ])->whereIn('id', $exception->checkins->pluck('status_id'))->get();

            $firstCheckin = $exception->checkins->first();

            return response()->json([
                'message' => [
                    'status_id' => $firstCheckin?->status_id,  // TODO: remove after 2026-10
                    'lineName' => $firstCheckin?->trip?->linename, // TODO: remove after 2026-10
                ],
                'data' => [
                    'conflicts' => StatusResource::collection($statuses),
                ],
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
                    required: ['data'],
                    properties: [new OA\Property(property: 'data', ref: Station::class)],
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
                    required: ['data'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: StationResource::class),
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
            $trainAutocompleteResponse = $this->stationService->search($query);

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
                    required: ['data'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: StationResource::class),
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ],
    )]
    public function getTrainStationHistory(): AnonymousResourceCollection
    {
        return StationResource::collection(
            $this->stationService->getLatestArrivalsForUser(\auth()->user(), 10)
        );
    }
}
