<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1;

use App\Enum\HafasTravelType;
use App\Exceptions\ManualTripValidationException;
use App\Http\Controllers\Backend\Transport\ManualTripCreator;
use App\Http\Requests\ManualTripCreationRequest;
use App\Http\Resources\StatusResource;
use App\Http\Resources\TripResource;
use App\Models\Checkin;
use App\Models\Operator;
use App\Models\Station;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
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

        $statuses = Checkin::with(['status.user'])
            ->where('trip_id', $trip->trip_id)
            ->get()
            ->map(fn (Checkin $checkin) => $checkin->status)
            ->filter(fn ($status) => $status !== null && Gate::allows('view', $status))
            ->values();

        return StatusResource::collection($statuses);
    }

    #[OA\Post(
        path: '/trips',
        operationId: 'createTrip',
        summary: 'Create a manual trip.',
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

        return new TripResource($trip);
    }
}
