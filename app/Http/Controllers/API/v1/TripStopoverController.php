<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1;

use App\Exceptions\ManualTripValidationException;
use App\Exceptions\StopoverInUseException;
use App\Http\Requests\ShiftTripStopoversRequest;
use App\Http\Requests\StoreTripStopoverRequest;
use App\Http\Requests\UpdateTripStopoverRequest;
use App\Models\Stopover;
use App\Models\Trip;
use App\Services\Trip\TripEditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class TripStopoverController extends Controller
{
    public function __construct(private readonly TripEditService $tripEditService) {}

    #[OA\Post(
        path: '/trips/{tripUuid}/stopovers',
        operationId: 'createTripStopover',
        description: 'Adds a stopover to a trip. You can only edit manual trips (`source = user`) that you created yourself; admins can edit any trip. The position within the trip is derived from the planned times. Origin, destination, departure and arrival of the trip as well as distance, points and duration of all checkins on this trip are recalculated afterwards.',
        summary: 'Add a stopover to a trip',
        security: [['passport' => ['write-statuses']], ['token' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['stationUuid'],
                properties: [
                    new OA\Property(property: 'stationUuid', description: 'Station UUID', type: 'string', format: 'uuid', example: '00000000-0000-0000-0000-000000000000'),
                    new OA\Property(property: 'arrivalPlanned', type: 'string', format: 'date-time', example: '2025-01-01T11:00:00Z', nullable: true),
                    new OA\Property(property: 'departurePlanned', type: 'string', format: 'date-time', example: '2025-01-01T11:02:00Z', nullable: true),
                    new OA\Property(property: 'arrivalReal', type: 'string', format: 'date-time', nullable: true),
                    new OA\Property(property: 'departureReal', type: 'string', format: 'date-time', nullable: true),
                    new OA\Property(property: 'arrivalPlatformPlanned', type: 'string', example: '3', nullable: true),
                    new OA\Property(property: 'departurePlatformPlanned', type: 'string', example: '3', nullable: true),
                    new OA\Property(property: 'arrivalPlatformReal', type: 'string', nullable: true),
                    new OA\Property(property: 'departurePlatformReal', type: 'string', nullable: true),
                    new OA\Property(property: 'cancelled', type: 'boolean', example: false),
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
    public function store(StoreTripStopoverRequest $request, string $tripUuid): Response|JsonResponse
    {
        $trip = Trip::where('uuid', $tripUuid)->firstOrFail();
        $this->authorize('create', [Stopover::class, $trip]);

        try {
            $this->tripEditService->addStopover($trip, $request->validated());
        } catch (ManualTripValidationException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->noContent();
    }

    #[OA\Put(
        path: '/trips/{tripUuid}/stopovers/{stopoverUuid}',
        operationId: 'updateTripStopover',
        description: 'Updates a stopover of a trip. You can only edit manual trips (`source = user`) that you created yourself; admins can edit any trip. All fields are optional, only the given ones are changed. Origin, destination, departure and arrival of the trip as well as distance, points and duration of all checkins on this trip are recalculated afterwards.',
        summary: 'Update a stopover of a trip',
        security: [['passport' => ['write-statuses']], ['token' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'stationUuid', description: 'Station UUID', type: 'string', format: 'uuid', example: '00000000-0000-0000-0000-000000000000'),
                    new OA\Property(property: 'arrivalPlanned', type: 'string', format: 'date-time', example: '2025-01-01T11:00:00Z', nullable: true),
                    new OA\Property(property: 'departurePlanned', type: 'string', format: 'date-time', example: '2025-01-01T11:02:00Z', nullable: true),
                    new OA\Property(property: 'arrivalReal', type: 'string', format: 'date-time', nullable: true),
                    new OA\Property(property: 'departureReal', type: 'string', format: 'date-time', nullable: true),
                    new OA\Property(property: 'arrivalPlatformPlanned', type: 'string', example: '3', nullable: true),
                    new OA\Property(property: 'departurePlatformPlanned', type: 'string', example: '3', nullable: true),
                    new OA\Property(property: 'arrivalPlatformReal', type: 'string', nullable: true),
                    new OA\Property(property: 'departurePlatformReal', type: 'string', nullable: true),
                    new OA\Property(property: 'cancelled', type: 'boolean', example: false),
                ],
            ),
        ),
        tags: ['Trips'],
        parameters: [
            new OA\Parameter(name: 'tripUuid', description: 'Trip UUID', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(name: 'stopoverUuid', description: 'Stopover UUID', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: self::OA_DESC_NO_CONTENT),
            new OA\Response(response: 403, description: self::OA_DESC_FORBIDDEN),
            new OA\Response(response: 404, description: self::OA_DESC_NOT_FOUND),
            new OA\Response(response: 422, description: self::OA_DESC_UNPROCESSABLE),
        ],
    )]
    public function update(UpdateTripStopoverRequest $request, string $tripUuid, string $stopoverUuid): Response|JsonResponse
    {
        $stopover = $this->findStopover($tripUuid, $stopoverUuid);
        $this->authorize('update', $stopover);

        try {
            $this->tripEditService->updateStopover($stopover, $request->validated());
        } catch (ManualTripValidationException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->noContent();
    }

    #[OA\Post(
        path: '/trips/{tripUuid}/stopovers/shift',
        operationId: 'shiftTripStopovers',
        description: 'Moves all stopovers of a trip in time by the given amount of minutes. Positive values move the trip to a later time, negative values to an earlier one. Planned and real times are shifted alike, so the relative timing of the trip stays the same. You can only edit manual trips (`source = user`) that you created yourself; admins can edit any trip. Departure and arrival of the trip as well as duration and points of all checkins on this trip are recalculated afterwards.',
        summary: 'Shift all stopovers of a trip in time',
        security: [['passport' => ['write-statuses']], ['token' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['minutes'],
                properties: [
                    new OA\Property(property: 'minutes', description: 'Offset in minutes, may be negative', type: 'integer', example: -1440, maximum: 525600, minimum: -525600),
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
    public function shift(ShiftTripStopoversRequest $request, string $tripUuid): Response|JsonResponse
    {
        $trip = Trip::where('uuid', $tripUuid)->firstOrFail();
        $this->authorize('update', $trip);

        try {
            $this->tripEditService->shiftStopovers($trip, (int) $request->validated('minutes'));
        } catch (ManualTripValidationException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->noContent();
    }

    #[OA\Delete(
        path: '/trips/{tripUuid}/stopovers/{stopoverUuid}',
        operationId: 'deleteTripStopover',
        description: 'Removes a stopover from a trip. You can only edit manual trips (`source = user`) that you created yourself; admins can edit any trip. Stopovers referenced by a checkin as origin or destination cannot be removed, and a trip must keep at least two stopovers.',
        summary: 'Delete a stopover of a trip',
        security: [['passport' => ['write-statuses']], ['token' => []]],
        tags: ['Trips'],
        parameters: [
            new OA\Parameter(name: 'tripUuid', description: 'Trip UUID', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(name: 'stopoverUuid', description: 'Stopover UUID', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: self::OA_DESC_NO_CONTENT),
            new OA\Response(response: 403, description: self::OA_DESC_FORBIDDEN),
            new OA\Response(response: 404, description: self::OA_DESC_NOT_FOUND),
            new OA\Response(response: 409, description: 'Stopover is referenced by checkins'),
            new OA\Response(response: 422, description: self::OA_DESC_UNPROCESSABLE),
        ],
    )]
    public function destroy(string $tripUuid, string $stopoverUuid): Response|JsonResponse
    {
        $stopover = $this->findStopover($tripUuid, $stopoverUuid);
        $this->authorize('delete', $stopover);

        try {
            $this->tripEditService->deleteStopover($stopover);
        } catch (StopoverInUseException $exception) {
            return response()->json(['message' => $exception->getMessage()], 409);
        } catch (ManualTripValidationException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->noContent();
    }

    private function findStopover(string $tripUuid, string $stopoverUuid): Stopover
    {
        $trip = Trip::where('uuid', $tripUuid)->firstOrFail();

        return Stopover::where('uuid', $stopoverUuid)
            ->where('trip_id', $trip->trip_id)
            ->firstOrFail();
    }
}
