<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1;

use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Events\StatusUpdateEvent;
use App\Http\Controllers\Backend\Support\LocationController;
use App\Http\Controllers\Backend\Transport\PointsCalculationController;
use App\Http\Resources\AdminStatusResource;
use App\Models\Status;
use App\Models\User;
use App\Services\Checkin\CheckinService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
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
                    required: ['data'],
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: AdminStatusResource::class)),
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
            'checkin.trip.stopovers.station',
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
            new OA\Response(
                response: 200,
                description: 'Status details.',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [new OA\Property(property: 'data', ref: AdminStatusResource::class)]
                )
            ),
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
                    new OA\Property(property: 'origin', description: 'Origin stopover ID (train_stopovers.id) of this trip', type: 'integer'),
                    new OA\Property(property: 'destination', description: 'Destination stopover ID (train_stopovers.id) of this trip', type: 'integer'),
                    new OA\Property(property: 'body', type: 'string', nullable: true, maxLength: 280),
                    new OA\Property(property: 'visibility', type: 'integer'),
                    new OA\Property(property: 'business', type: 'integer', nullable: true),
                    new OA\Property(property: 'eventId', type: 'integer', nullable: true),
                    new OA\Property(property: 'points', type: 'integer', nullable: true),
                    new OA\Property(property: 'moderationNotes', type: 'string', nullable: true, maxLength: 255),
                    new OA\Property(property: 'lockVisibility', type: 'boolean', nullable: true),
                    new OA\Property(property: 'hideBody', type: 'boolean', nullable: true),
                ],
            ),
        ),
        tags: ['Admin'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Updated status.',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [new OA\Property(property: 'data', ref: AdminStatusResource::class)]
                )
            ),
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
            'origin' => ['required', 'integer'],
            'destination' => ['required', 'integer'],
            'body' => ['nullable', 'string', 'max:280'],
            'visibility' => ['required', new Enum(StatusVisibility::class)],
            'business' => ['nullable', new Enum(Business::class)],
            'eventId' => ['nullable', 'integer', 'exists:events,id'],
            'points' => ['nullable', 'integer', 'gte:0'],
            'moderationNotes' => ['nullable', 'string', 'max:255'],
            'lockVisibility' => ['nullable', 'boolean'],
            'hideBody' => ['nullable', 'boolean'],
        ]);

        $stopovers = $status->checkin->trip->stopovers;
        $newOrigin = $stopovers->firstWhere('id', $validated['origin']);
        $newDestination = $stopovers->firstWhere('id', $validated['destination']);

        if ($newOrigin === null || $newDestination === null) {
            throw ValidationException::withMessages([
                'origin' => $newOrigin === null ? 'The given origin is not a stopover of this trip.' : [],
                'destination' => $newDestination === null ? 'The given destination is not a stopover of this trip.' : [],
            ]);
        }

        $newDeparture = $newOrigin->departure_planned ?? $newOrigin->arrival_planned;
        $newArrival = $newDestination->arrival_planned ?? $newDestination->departure_planned;

        $distanceInMeters = new LocationController(
            trip: $status->checkin->trip,
            origin: $newOrigin,
            destination: $newDestination,
        )->calculateDistance();

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
            'duration' => app(CheckinService::class)->calculateCheckinDuration($status->checkin, false),
        ]);

        StatusUpdateEvent::dispatch($status->refresh());

        $payload = [
            'visibility' => $validated['visibility'],
            'event_id' => $validated['eventId'] ?? null,
            'moderation_notes' => $validated['moderationNotes'] ?? null,
        ];

        if (array_key_exists('body', $validated)) {
            $payload['body'] = $validated['body'];
        }
        if (array_key_exists('business', $validated) && $validated['business'] !== null) {
            $payload['business'] = $validated['business'];
        }
        if (array_key_exists('lockVisibility', $validated)) {
            $payload['lock_visibility'] = $validated['lockVisibility'] ?? false;
        }
        if (array_key_exists('hideBody', $validated)) {
            $payload['hide_body'] = $validated['hideBody'] ?? false;
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
