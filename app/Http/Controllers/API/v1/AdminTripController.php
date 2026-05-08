<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1;

use App\Http\Resources\AdminTripResource;
use App\Jobs\RefreshPolyline;
use App\Models\Trip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class AdminTripController extends Controller
{
    #[OA\Get(
        path: '/admin/trips/{id}',
        operationId: 'getAdminTrip',
        description: 'Admin only. Returns full trip details including stopovers with route segment info and checkins.',
        summary: 'Get trip details',
        security: [['passport' => []], ['token' => []]],
        tags: ['Admin'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Trip details',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: AdminTripResource::class)],
                ),
            ),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
        ],
    )]
    public function show(int $id): AdminTripResource|JsonResponse
    {
        $this->authorize('adminViewAny', Trip::class);

        $trip = Trip::with([
            'operator',
            'user',
            'stopovers.station',
            'stopovers.routeSegment',
            'checkins.status.user',
            'checkins.status.checkin.originStopover.station',
            'checkins.status.checkin.destinationStopover.station',
        ])->findOrFail($id);

        return new AdminTripResource($trip);
    }

    #[OA\Post(
        path: '/admin/trips/{id}/reroute',
        operationId: 'rerouteAdminTrip',
        description: 'Admin only. Dispatches a background job to recalculate the polyline for the given trip.',
        summary: 'Dispatch reroute job',
        security: [['passport' => []], ['token' => []]],
        tags: ['Admin'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Job dispatched'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
        ],
    )]
    public function reroute(int $id): Response|JsonResponse
    {
        $this->authorize('adminViewAny', Trip::class);

        $trip = Trip::findOrFail($id);
        RefreshPolyline::dispatch($trip);

        return response()->noContent();
    }
}
