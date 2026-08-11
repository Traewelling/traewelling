<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1;

use App\Enum\HafasTravelType;
use App\Enum\TripSource;
use App\Exceptions\ManualTripValidationException;
use App\Exceptions\TripInUseException;
use App\Http\Controllers\Backend\Transport\ManualTripCreator;
use App\Http\Requests\ManualTripCreationRequest;
use App\Http\Requests\UpdateTripRequest;
use App\Http\Resources\StatusResource;
use App\Http\Resources\TripResource;
use App\Models\Checkin;
use App\Models\Operator;
use App\Models\Station;
use App\Models\Trip;
use App\Services\Trip\RoutePreviewService;
use App\Services\Trip\TripCopyService;
use App\Services\Trip\TripEditService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

class TripController extends Controller
{
    #[OA\Get(
        path: '/trips/{id}/statuses',
        operationId: 'getTripStatuses',
        description: 'Returns all statuses visible to the (un)authenticated user for a given trip',
        summary: '[Auth optional] Get statuses for a trip',
        security: [['passport' => ['read-statuses']], ['token' => []]],
        tags: ['Trips'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Internal trip ID (available as checkin.trip in the status response)',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 4711,
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
                            items: new OA\Items(ref: '#/components/schemas/StatusResource'),
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 404, description: 'Trip not found'),
        ],
    )]
    public function statuses(int $id): AnonymousResourceCollection|JsonResponse
    {
        $trip = Trip::find($id);

        if ($trip === null) {
            return response()->json(['message' => 'Trip not found'], 404);
        }

        $statuses = Checkin::with([
            'status.user',
            'status.checkin.originStopover.station',
            'status.checkin.destinationStopover.station',
        ])
            ->where('trip_id', $trip->trip_id)
            ->get()
            ->map(fn (Checkin $checkin) => $checkin->status)
            ->filter(fn ($status) => $status !== null && Gate::allows('view', $status))
            ->values();

        return StatusResource::collection($statuses);
    }

    #[OA\Post(
        path: '/trips/route-preview',
        operationId: 'routePreviewTrip',
        description: 'Routes between the given stations using the appropriate BRouter profile for the category. Falls back to straight-line segments if BRouter cannot route a segment (e.g. no railway near that station). Returns a GeoJSON LineString feature.',
        summary: 'Preview the routing for a manual trip before creating it.',
        security: [['passport' => ['write-statuses']], ['token' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['category', 'stationIds'],
                properties: [
                    new OA\Property(property: 'category', ref: HafasTravelType::class),
                    new OA\Property(
                        property: 'stationIds',
                        description: 'Ordered list of station IDs (origin first, destination last).',
                        type: 'array',
                        items: new OA\Items(type: 'integer', example: 8000105),
                        minItems: 2,
                    ),
                ],
            ),
        ),
        tags: ['Trips'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'GeoJSON Feature with a LineString geometry. The `properties.routed` flag indicates whether BRouter was used for at least one segment (false means straight-line fallback).',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [
                        new OA\Property(property: 'data', description: 'GeoJSON Feature (LineString)', type: 'object'),
                    ],
                ),
            ),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function routePreview(Request $request, RoutePreviewService $service): JsonResponse
    {
        $validated = $request->validate([
            'category' => ['required', 'string'],
            'stationIds' => ['required', 'array', 'min:2'],
            'stationIds.*' => ['required', 'integer'],
        ]);

        $category = HafasTravelType::tryFrom($validated['category']);
        if ($category === null) {
            return response()->json(['message' => 'Unknown category.'], 422);
        }

        try {
            $feature = $service->build($validated['stationIds'], $category);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['data' => $feature]);
    }

    #[OA\Post(
        path: '/trips',
        operationId: 'createTrip',
        description: 'Creates a trip from the given data, to check into journeys no data provider knows about. The trip is created with `source = user` and belongs to you, so you can change it afterwards through the trip and stopover endpoints.',
        summary: 'Create a trip',
        security: [['passport' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['category', 'lineName', 'originId', 'originDeparturePlanned', 'destinationId', 'destinationArrivalPlanned'],
                properties: [
                    new OA\Property(property: 'category', type: 'string', example: 'regional'),
                    new OA\Property(property: 'lineName', type: 'string', example: 'RE 1'),
                    new OA\Property(property: 'journeyNumber', type: 'integer', example: 12345, nullable: true),
                    new OA\Property(
                        property: 'operatorId',
                        description: 'Operator UUID (preferred) or numeric legacy ID. Use the `uuid` field from the operators endpoint.',
                        type: 'string',
                        example: '00000000-0000-0000-0000-000000000000',
                        nullable: true,
                    ),
                    new OA\Property(property: 'originId', type: 'integer', example: 8000105),
                    new OA\Property(property: 'originDeparturePlanned', type: 'string', format: 'date-time', example: '2025-01-01T10:00:00Z'),
                    new OA\Property(property: 'destinationId', type: 'integer', example: 8000261),
                    new OA\Property(property: 'destinationArrivalPlanned', type: 'string', format: 'date-time', example: '2025-01-01T12:00:00Z'),
                    new OA\Property(
                        property: 'stopovers',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'stationId', type: 'integer', example: 8000240),
                                new OA\Property(property: 'arrival', type: 'string', format: 'date-time', example: '2025-01-01T11:00:00Z', nullable: true),
                                new OA\Property(property: 'departure', type: 'string', format: 'date-time', example: '2025-01-01T11:02:00Z', nullable: true),
                            ],
                        ),
                        nullable: true,
                    ),
                ],
            ),
        ),
        tags: ['Trips'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Trip created successfully',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/TripResource'),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Validation error'),
            new OA\Response(response: 403, description: 'Unauthorized'),
        ],
    )]
    public function createTrip(ManualTripCreationRequest $request): TripResource|JsonResponse
    {
        $validated = $request->validated();

        try {
            $trip = DB::transaction(function () use ($validated) {
                $creator = new ManualTripCreator();
                $creator->setCategory(HafasTravelType::from($validated['category']))
                    ->setLine($validated['lineName'], $validated['journeyNumber'] ?? null)
                    ->setOrigin(
                        Station::findOrFail($validated['originId']),
                        Carbon::parse($validated['originDeparturePlanned']),
                        isset($validated['originDepartureReal']) ? Carbon::parse($validated['originDepartureReal']) : null
                    )
                    ->setDestination(
                        Station::findOrFail($validated['destinationId']),
                        Carbon::parse($validated['destinationArrivalPlanned']),
                        isset($validated['destinationArrivalReal']) ? Carbon::parse($validated['destinationArrivalReal']) : null
                    );

                if (isset($validated['operatorId'])) {
                    $operatorId = $validated['operatorId'];
                    $operator = Str::isUuid($operatorId) // TODO: remove this catch after legacy id is removed (after 2026-09)
                        ? Operator::where('id', $operatorId)->firstOrFail()
                        : Operator::where('legacy_id', $operatorId)->firstOrFail();
                    $creator->setOperator($operator);
                }

                foreach ($validated['stopovers'] ?? [] as $stopover) {
                    $creator->addStopover(
                        Station::findOrFail($stopover['stationId']),
                        isset($stopover['departure']) ? Carbon::parse($stopover['departure']) : null,
                        isset($stopover['arrival']) ? Carbon::parse($stopover['arrival']) : null,
                        isset($stopover['departureReal']) ? Carbon::parse($stopover['departureReal']) : null,
                        isset($stopover['arrivalReal']) ? Carbon::parse($stopover['arrivalReal']) : null
                    );
                }

                $trip = $creator->createFullTrip();
                $durationInHours = $trip->departure->diffInHours($trip->arrival);
                if ($durationInHours > config('trwl.max_journey_hours')) {
                    throw new ManualTripValidationException(sprintf('Trip duration exceeds maximum allowed duration of %d hours', config('trwl.max_journey_hours')));
                }

                return $trip;
            });
        } catch (ManualTripValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }

        $trip->load('stopovers.station');

        return new TripResource($trip);
    }

    #[OA\Get(
        path: '/trips',
        operationId: 'getOwnTrips',
        description: 'Returns a list of the manual trips the authenticated user created.',
        summary: 'List your own trips',
        security: [['passport' => ['read-statuses']], ['token' => []]],
        tags: ['Trips'],
        parameters: [
            new OA\Parameter(name: 'cursor', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: self::OA_DESC_SUCCESS,
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: TripResource::class)),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: self::OA_DESC_UNAUTHENTICATED),
        ],
    )]
    public function index(): AnonymousResourceCollection
    {
        $trips = Trip::with(['originStation', 'destinationStation', 'operator', 'stopovers.station'])
            ->withCount('checkins')
            ->where('user_id', auth()->id())
            ->where('source', TripSource::USER)
            ->orderByDesc('departure')
            ->orderByDesc('id')
            ->cursorPaginate(25);

        return TripResource::collection($trips);
    }

    #[OA\Post(
        path: '/trips/{tripUuid}/copy',
        operationId: 'copyTrip',
        description: 'Copies a trip including all its stopovers into a new trip with `source = user` that belongs to you, so you can correct data a provider got wrong. Manual trips of other users cannot be copied. Your own checkin on the original trip is moved to the copy, which drops its points to 0 because manual trips do not score. Checkins of other users stay on the original trip.',
        summary: 'Copy a trip',
        security: [['passport' => ['write-statuses']], ['token' => []]],
        tags: ['Trips'],
        parameters: [
            new OA\Parameter(name: 'tripUuid', description: 'Trip UUID', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Trip copied successfully',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [new OA\Property(property: 'data', ref: TripResource::class)],
                ),
            ),
            new OA\Response(response: 403, description: self::OA_DESC_FORBIDDEN),
            new OA\Response(response: 404, description: self::OA_DESC_NOT_FOUND),
        ],
    )]
    public function copy(string $tripUuid, TripCopyService $tripCopyService): JsonResponse
    {
        $trip = Trip::with('stopovers')->where('uuid', $tripUuid)->firstOrFail();
        $this->authorize('copy', $trip);

        $copy = $tripCopyService->copy($trip, auth()->user());
        $copy->load(['originStation', 'destinationStation', 'operator', 'stopovers.station']);

        return new TripResource($copy)->response()->setStatusCode(201);
    }

    #[OA\Get(
        path: '/trips/{tripUuid}',
        operationId: 'getTrip',
        description: 'Returns a trip including all stopovers. You can only access manual trips (`source = user`) that you created yourself; admins can access any trip.',
        summary: 'Get a trip',
        security: [['passport' => ['write-statuses']], ['token' => []]],
        tags: ['Trips'],
        parameters: [
            new OA\Parameter(name: 'tripUuid', description: 'Trip UUID', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: self::OA_DESC_SUCCESS,
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [new OA\Property(property: 'data', ref: TripResource::class)],
                ),
            ),
            new OA\Response(response: 403, description: self::OA_DESC_FORBIDDEN),
            new OA\Response(response: 404, description: self::OA_DESC_NOT_FOUND),
        ],
    )]
    public function show(string $tripUuid): TripResource
    {
        $trip = Trip::where('uuid', $tripUuid)->firstOrFail();
        $this->authorize('view', $trip);

        $trip->load(['originStation', 'destinationStation', 'operator', 'stopovers.station']);
        $trip->loadCount('checkins');

        return new TripResource($trip);
    }

    #[OA\Put(
        path: '/trips/{tripUuid}',
        operationId: 'updateTrip',
        description: 'Updates the metadata of a trip. You can only edit manual trips (`source = user`) that you created yourself; admins can edit any trip. All fields are optional, only the given ones are changed. Changing the category recalculates distance and points of all checkins on this trip.',
        summary: 'Update a trip',
        security: [['passport' => ['write-statuses']], ['token' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'category', ref: HafasTravelType::class),
                    new OA\Property(property: 'lineName', type: 'string', example: 'RE 1'),
                    new OA\Property(property: 'journeyNumber', type: 'integer', example: 12345, nullable: true),
                    new OA\Property(
                        property: 'operatorUuid',
                        description: 'Operator UUID. Null removes the operator.',
                        type: 'string',
                        format: 'uuid',
                        example: '00000000-0000-0000-0000-000000000000',
                        nullable: true,
                    ),
                ],
            ),
        ),
        tags: ['Trips'],
        parameters: [
            new OA\Parameter(name: 'tripUuid', description: 'Trip UUID', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: self::OA_DESC_NO_CONTENT),
            new OA\Response(response: 403, description: self::OA_DESC_FORBIDDEN),
            new OA\Response(response: 404, description: self::OA_DESC_NOT_FOUND),
            new OA\Response(response: 422, description: self::OA_DESC_UNPROCESSABLE),
        ],
    )]
    public function update(UpdateTripRequest $request, string $tripUuid, TripEditService $tripEditService): Response|JsonResponse
    {
        $trip = Trip::where('uuid', $tripUuid)->firstOrFail();
        $this->authorize('update', $trip);

        try {
            $tripEditService->updateTrip($trip, $request->validated());
        } catch (ManualTripValidationException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->noContent();
    }

    #[OA\Delete(
        path: '/trips/{tripUuid}',
        operationId: 'deleteTrip',
        description: 'Deletes a trip including all its stopovers. You can only delete manual trips (`source = user`) that you created yourself; admins can delete any trip. A trip can only be deleted while no checkin references it, which includes checkins of other users you cannot see. Check `checkinCount` beforehand to know whether deleting is possible.',
        summary: 'Delete a trip',
        security: [['passport' => ['write-statuses']], ['token' => []]],
        tags: ['Trips'],
        parameters: [
            new OA\Parameter(name: 'tripUuid', description: 'Trip UUID', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: self::OA_DESC_NO_CONTENT),
            new OA\Response(response: 403, description: self::OA_DESC_FORBIDDEN),
            new OA\Response(response: 404, description: self::OA_DESC_NOT_FOUND),
            new OA\Response(response: 409, description: 'Trip is referenced by checkins'),
        ],
    )]
    public function destroy(string $tripUuid, TripEditService $tripEditService): Response|JsonResponse
    {
        $trip = Trip::where('uuid', $tripUuid)->firstOrFail();
        $this->authorize('delete', $trip);

        try {
            $tripEditService->deleteTrip($trip);
        } catch (TripInUseException $exception) {
            return response()->json(['message' => $exception->getMessage()], 409);
        }

        return response()->noContent();
    }
}
