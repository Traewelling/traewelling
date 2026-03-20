<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1;

use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Events\StatusUpdateEvent;
use App\Http\Controllers\Backend\Support\LocationController;
use App\Http\Controllers\Backend\Transport\PointsCalculationController;
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Http\Resources\AdminStatusResource;
use App\Models\Station;
use App\Models\Status;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rules\Enum;
use OpenApi\Attributes as OA;

class AdminStatusController extends Controller
{
    #[OA\Get(
        path: '/admin/statuses',
        operationId: 'getAdminStatuses',
        summary: 'List statuses for admin moderation. Admin only.',
        security: [['passport' => []], ['token' => []]],
        tags: ['Admin'],
        parameters: [
            new OA\Parameter(name: 'userQuery', description: 'Filter by user name or username', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'cursor', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of statuses.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/AdminStatusResource')),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated.'),
            new OA\Response(response: 403, description: 'Forbidden.'),
        ],
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('adminViewAny', Status::class);

        $validated = $request->validate([
            'userQuery' => ['nullable', 'string', 'max:255'],
        ]);

        $query = Status::with([
            'checkin.originStopover.station',
            'checkin.destinationStopover.station',
            'user',
        ])->orderBy('created_at', 'desc');

        if (!empty($validated['userQuery'])) {
            $query->whereIn(
                'user_id',
                User::where('name', 'like', '%' . $validated['userQuery'] . '%')
                    ->orWhere('username', 'like', '%' . $validated['userQuery'] . '%')
                    ->pluck('id')
            );
        }

        return AdminStatusResource::collection($query->cursorPaginate(15));
    }

    #[OA\Get(
        path: '/admin/statuses/{id}',
        operationId: 'getAdminStatus',
        summary: 'Get a single status with all admin details. Admin only.',
        security: [['passport' => []], ['token' => []]],
        tags: ['Admin'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Status details.', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/AdminStatusResource')])),
            new OA\Response(response: 403, description: 'Forbidden.'),
            new OA\Response(response: 404, description: 'Not found.'),
        ],
    )]
    public function show(int $id): AdminStatusResource
    {
        $this->authorize('adminViewAny', Status::class);

        $status = Status::with([
            'checkin.originStopover.station',
            'checkin.destinationStopover.station',
            'checkin.trip.stopovers.station',
            'user',
            'createdByUser',
            'tags',
            'client',
            'event',
        ])->findOrFail($id);

        return new AdminStatusResource($status);
    }

    #[OA\Put(
        path: '/admin/statuses/{id}',
        operationId: 'updateAdminStatus',
        summary: 'Update a status including moderation fields. Admin only.',
        security: [['passport' => []], ['token' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['origin', 'destination', 'visibility'],
                properties: [
                    new OA\Property(property: 'origin', description: 'Origin station ID', type: 'integer'),
                    new OA\Property(property: 'destination', description: 'Destination station ID', type: 'integer'),
                    new OA\Property(property: 'body', type: 'string', nullable: true, maxLength: 280),
                    new OA\Property(property: 'visibility', type: 'integer'),
                    new OA\Property(property: 'business', type: 'integer', nullable: true),
                    new OA\Property(property: 'event_id', type: 'integer', nullable: true),
                    new OA\Property(property: 'points', type: 'integer', nullable: true),
                    new OA\Property(property: 'moderation_notes', type: 'string', nullable: true, maxLength: 255),
                    new OA\Property(property: 'lock_visibility', type: 'boolean', nullable: true),
                    new OA\Property(property: 'hide_body', type: 'boolean', nullable: true),
                ],
            ),
        ),
        tags: ['Admin'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Updated status.', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/AdminStatusResource')])),
            new OA\Response(response: 403, description: 'Forbidden.'),
            new OA\Response(response: 404, description: 'Not found.'),
            new OA\Response(response: 422, description: 'Validation error.'),
        ],
    )]
    public function update(int $id, Request $request): AdminStatusResource
    {
        $status = Status::with('checkin.trip.stopovers')->findOrFail($id);
        $this->authorize('adminUpdate', $status);

        $validated = $request->validate([
            'origin' => ['required', 'integer', 'exists:train_stations,id'],
            'destination' => ['required', 'integer', 'exists:train_stations,id'],
            'body' => ['nullable', 'string', 'max:280'],
            'visibility' => ['required', new Enum(StatusVisibility::class)],
            'business' => ['nullable', new Enum(Business::class)],
            'event_id' => ['nullable', 'integer', 'exists:events,id'],
            'points' => ['nullable', 'integer', 'gte:0'],
            'moderation_notes' => ['nullable', 'string', 'max:255'],
            'lock_visibility' => ['nullable', 'boolean'],
            'hide_body' => ['nullable', 'boolean'],
        ]);

        $originStation = Station::findOrFail($validated['origin']);
        $destinationStation = Station::findOrFail($validated['destination']);

        $newOrigin = $status->checkin->trip->stopovers->where('train_station_id', $originStation->id)->first();
        $newDestination = $status->checkin->trip->stopovers->where('train_station_id', $destinationStation->id)->first();

        $newDeparture = $newOrigin->departure_planned ?? $newOrigin->arrival_planned;
        $newArrival = $newDestination->arrival_planned ?? $newDestination->departure_planned;

        $distanceInMeters = (new LocationController(
            trip: $status->checkin->trip,
            origin: $newOrigin,
            destination: $newDestination,
        ))->calculateDistance();

        $pointCalculation = PointsCalculationController::calculatePoints(
            distanceInMeter: $distanceInMeters,
            hafasTravelType: $status->checkin->trip->category,
            departure: $newDeparture,
            arrival: $newArrival,
            tripSource: $status->checkin->trip->source,
            timestampOfView: $newDeparture,
        );

        $status->checkin->update([
            'origin_stopover_id' => $newOrigin->id,
            'destination_stopover_id' => $newDestination->id,
            'departure' => $newDeparture,
            'arrival' => $newArrival,
            'distance' => $distanceInMeters,
            'points' => $validated['points'] ?? $pointCalculation->points,
            'duration' => TrainCheckinController::calculateCheckinDuration($status->checkin, false),
        ]);

        StatusUpdateEvent::dispatch($status->refresh());

        $payload = [
            'visibility' => $validated['visibility'],
            'event_id' => $validated['event_id'] ?? null,
            'moderation_notes' => $validated['moderation_notes'] ?? null,
        ];

        if (array_key_exists('body', $validated)) {
            $payload['body'] = $validated['body'];
        }
        if (array_key_exists('business', $validated) && $validated['business'] !== null) {
            $payload['business'] = $validated['business'];
        }
        if (array_key_exists('lock_visibility', $validated)) {
            $payload['lock_visibility'] = $validated['lock_visibility'] ?? false;
        }
        if (array_key_exists('hide_body', $validated)) {
            $payload['hide_body'] = $validated['hide_body'] ?? false;
        }

        $status->update($payload);

        return new AdminStatusResource($status->fresh([
            'checkin.originStopover.station',
            'checkin.destinationStopover.station',
            'checkin.trip.stopovers.station',
            'user',
            'createdByUser',
            'tags',
            'client',
            'event',
        ]));
    }
}
