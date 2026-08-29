<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1;

use App\Dto\RouteMap\RouteMapEntryDto;
use App\Enum\Business;
use App\Enum\HafasTravelType;
use App\Http\Requests\RouteMapRequest;
use App\Http\Resources\RouteMapEntryResource;
use App\Services\RouteMap\RouteMapService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class RouteMapController extends Controller
{
    public function __construct(private readonly RouteMapService $routeMapService)
    {
        parent::__construct();
    }

    #[OA\Get(
        path: '/route-map',
        operationId: 'getRouteMap',
        description: 'Returns every stretch of the network the authenticated user has travelled, as encoded '
                     . 'polylines. Stretches are deduplicated. '
                     . 'Stretches without a route segment are returned as a straight line between both stations '
                     . 'and flagged with `approximated`.',
        summary: 'Get the route map of the authenticated user',
        security: [['passport' => ['read-statistics']], ['token' => []]],
        tags: ['Statistics'],
        parameters: [
            new OA\Parameter(
                name: 'from',
                description: 'Only include journeys departing at or after this point in time',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date-time'),
                example: '2024-01-01',
            ),
            new OA\Parameter(
                name: 'until',
                description: 'Only include journeys departing at or before this point in time',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date-time'),
                example: '2024-12-31',
            ),
            new OA\Parameter(
                name: 'travelTypes[]',
                description: 'Only include journeys with these modes of transport. Repeatable, or a comma separated list. Empty means all.',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'array', items: new OA\Items(ref: HafasTravelType::class)),
            ),
            new OA\Parameter(
                name: 'travelPurposes[]',
                description: 'Only include journeys with these purposes of travel (0=private, 1=business, 2=commute). '
                             . 'Repeatable, or a comma separated list. Values are combined with OR, so `1,2` returns '
                             . 'business trips and commutes. Empty means all.',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'array', items: new OA\Items(ref: Business::class)),
            ),
            new OA\Parameter(
                name: 'includeApproximated',
                description: 'Include stretches that have no route segment yet as a straight line between both stations. Defaults to true.',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'boolean', default: true),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: self::OA_DESC_SUCCESS,
                content: new OA\JsonContent(
                    required: ['data', 'meta'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: RouteMapEntryResource::class),
                        ),
                        new OA\Property(
                            property: 'meta',
                            required: ['count', 'approximatedCount', 'distance'],
                            properties: [
                                new OA\Property(property: 'count', description: 'Number of returned stretches', type: 'integer', example: 1337),
                                new OA\Property(property: 'approximatedCount', description: 'How many of them are approximated straight lines', type: 'integer', example: 42),
                                new OA\Property(
                                    property: 'distance',
                                    description: 'Length of the travelled network in meters. Each stretch counts once, no matter how often it was travelled.',
                                    type: 'integer',
                                    example: 1234567,
                                ),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: self::OA_DESC_UNAUTHENTICATED),
            new OA\Response(response: 403, description: self::OA_DESC_FORBIDDEN),
            new OA\Response(response: 422, description: self::OA_DESC_UNPROCESSABLE),
        ],
    )]
    public function show(RouteMapRequest $request): AnonymousResourceCollection
    {
        $entries = $this->routeMapService->getRouteMap(auth()->user(), $request->toFilter());

        return RouteMapEntryResource::collection($entries)->additional([
            'meta' => [
                'count' => $entries->count(),
                'approximatedCount' => $entries->filter(static fn (RouteMapEntryDto $entry) => $entry->approximated)->count(),
                'distance' => (int) $entries->sum(static fn (RouteMapEntryDto $entry) => $entry->distance ?? 0),
            ],
        ]);
    }
}
