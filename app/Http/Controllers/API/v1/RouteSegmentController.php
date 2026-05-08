<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1;

use App\Dto\Coordinate;
use App\Enum\BRouterProfile;
use App\Enum\SegmentPathType;
use App\Exceptions\BRouterException;
use App\Http\Resources\RouteSegmentResource;
use App\Jobs\AssignRouteSegmentToStopovers;
use App\Jobs\DeleteRouteSegment;
use App\Jobs\UpgradeRouteSegmentAssignments;
use App\Models\RouteSegment;
use App\Models\Station;
use App\Models\StationIdentifier;
use App\Models\Stopover;
use App\Repositories\TripRepository;
use App\Services\BRouterService;
use App\Services\GeoService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;
use Traewelling\GooglePolyline\PolylineTranscoder;

class RouteSegmentController extends Controller
{
    #[OA\Get(
        path: '/route-segments',
        operationId: 'listRouteSegments',
        summary: 'List route segments for a given station pair (admin only).',
        tags: ['Polyline'],
        parameters: [
            new OA\Parameter(name: 'from_station_id', in: 'query', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'to_station_id', in: 'query', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: self::OA_DESC_SUCCESS,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/RouteSegmentResource'),
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: self::OA_DESC_UNAUTHENTICATED),
            new OA\Response(response: 403, description: self::OA_DESC_FORBIDDEN),
            new OA\Response(response: 422, description: self::OA_DESC_UNPROCESSABLE),
        ],
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', RouteSegment::class);

        $validated = $request->validate([
            'from_station_id' => ['required', 'integer', 'exists:train_stations,id'],
            'to_station_id' => ['required', 'integer', 'exists:train_stations,id'],
        ]);

        $segments = RouteSegment::with(['fromIdentifier', 'toIdentifier'])
            ->where('from_station_id', $validated['from_station_id'])
            ->where('to_station_id', $validated['to_station_id'])
            ->orderBy('duration')
            ->get();

        return RouteSegmentResource::collection($segments);
    }

    #[OA\Get(
        path: '/route-segments/{id}',
        operationId: 'getRouteSegment',
        summary: 'Get a single route segment with station names and counts (admin only).',
        tags: ['Polyline'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: self::OA_DESC_SUCCESS,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/RouteSegmentResource'),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: self::OA_DESC_UNAUTHENTICATED),
            new OA\Response(response: 403, description: self::OA_DESC_FORBIDDEN),
            new OA\Response(response: 404, description: self::OA_DESC_NOT_FOUND),
        ],
    )]
    public function show(string $id): RouteSegmentResource
    {
        $segment = RouteSegment::with(['fromStation', 'toStation', 'fromIdentifier', 'toIdentifier'])
            ->withCount('trips')
            ->findOrFail($id);
        $this->authorize('view', $segment);

        return new RouteSegmentResource($segment);
    }

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    #[OA\Post(
        path: '/route-segments',
        operationId: 'createRouteSegment',
        summary: 'Create a straight-line route segment between two stations (admin only).',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['from_station_id', 'to_station_id'],
                properties: [
                    new OA\Property(property: 'from_station_id', type: 'integer', example: 8000105),
                    new OA\Property(property: 'to_station_id', type: 'integer', example: 8000261),
                    new OA\Property(
                        property: 'stopover_id',
                        description: 'If provided, the new segment is assigned to this stopover and the duration is derived from the timetable.',
                        type: 'integer',
                        example: 42,
                        nullable: true,
                    ),
                    new OA\Property(
                        property: 'from_identifier_id',
                        description: 'UUID of the StationIdentifier for the origin. Must be provided together with to_identifier_id.',
                        type: 'string',
                        format: 'uuid',
                        nullable: true,
                    ),
                    new OA\Property(
                        property: 'to_identifier_id',
                        description: 'UUID of the StationIdentifier for the destination. Must be provided together with from_identifier_id.',
                        type: 'string',
                        format: 'uuid',
                        nullable: true,
                    ),
                ],
            ),
        ),
        tags: ['Polyline'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Route segment created successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/RouteSegmentResource',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: self::OA_DESC_UNAUTHENTICATED),
            new OA\Response(response: 403, description: self::OA_DESC_FORBIDDEN),
            new OA\Response(response: 404, description: self::OA_DESC_NOT_FOUND),
            new OA\Response(response: 422, description: self::OA_DESC_UNPROCESSABLE),
        ],
    )]
    public function store(Request $request, TripRepository $tripRepository, GeoService $geoService): JsonResponse
    {
        $this->authorize('create', RouteSegment::class);

        $validated = $request->validate([
            'from_station_id' => ['required', 'integer', 'exists:train_stations,id'],
            'to_station_id' => ['required', 'integer', 'exists:train_stations,id', 'different:from_station_id'],
            'stopover_id' => ['nullable', 'integer', 'exists:train_stopovers,id'],
            'from_identifier_id' => ['nullable', 'string', 'uuid', 'exists:station_identifiers,id', 'required_with:to_identifier_id'],
            'to_identifier_id' => ['nullable', 'string', 'uuid', 'exists:station_identifiers,id', 'required_with:from_identifier_id'],
        ]);

        $fromStation = Station::findOrFail($validated['from_station_id']);
        $toStation = Station::findOrFail($validated['to_station_id']);

        $fromIdentifier = isset($validated['from_identifier_id'])
            ? StationIdentifier::find($validated['from_identifier_id'])
            : null;
        $toIdentifier = isset($validated['to_identifier_id'])
            ? StationIdentifier::find($validated['to_identifier_id'])
            : null;

        $fromLat = ($fromIdentifier?->latitude !== null) ? $fromIdentifier->latitude : $fromStation->latitude;
        $fromLon = ($fromIdentifier?->longitude !== null) ? $fromIdentifier->longitude : $fromStation->longitude;
        $toLat = ($toIdentifier?->latitude !== null) ? $toIdentifier->latitude : $toStation->latitude;
        $toLon = ($toIdentifier?->longitude !== null) ? $toIdentifier->longitude : $toStation->longitude;

        $encodedPolyline = new PolylineTranscoder()->encodePolyline([
            [$fromLon, $fromLat],
            [$toLon, $toLat],
        ]);

        $distanceInMeters = (int) $geoService->getDistance(
            new Coordinate($fromLat, $fromLon),
            new Coordinate($toLat, $toLon),
        );

        $duration = null;
        $pathType = null;
        $stopover = null;

        if (isset($validated['stopover_id'])) {
            $stopover = Stopover::with('trip.stopovers')->findOrFail($validated['stopover_id']);
            $next = $this->findNextStopover($stopover);

            if ($stopover->train_station_id !== $validated['from_station_id']) {
                throw ValidationException::withMessages([
                    'from_station_id' => ['The from_station_id does not match the stopover\'s station.'],
                ]);
            }

            if ($next === null || $next->train_station_id !== $validated['to_station_id']) {
                throw ValidationException::withMessages([
                    'to_station_id' => ['The to_station_id does not match the next stopover\'s station in this trip.'],
                ]);
            }

            $duration = $this->deriveDurationFromPair($stopover, $next);
            $pathType = $stopover->trip->category->getSegmentPathType();

            if ($duration !== null) {
                // When identifier IDs are explicitly requested, only reuse an existing segment
                // if it already has those exact identifiers — never fall back to station-to-station.
                // This allows upgrading an existing station-based assignment to identifier-based.
                if ($fromIdentifier !== null && $toIdentifier !== null) {
                    $existingSegment = RouteSegment::where('from_identifier_id', $fromIdentifier->id)
                        ->where('to_identifier_id', $toIdentifier->id)
                        ->where(fn ($q) => $q->where('path_type', $pathType)->orWhereNull('path_type'))
                        ->first();
                } else {
                    $existingSegment = $tripRepository->getRouteSegmentBetweenStops($stopover, $next, $duration, $pathType);
                }

                if ($existingSegment !== null) {
                    $tripRepository->setRouteSegmentForStop($stopover, $existingSegment);

                    return new RouteSegmentResource($existingSegment)
                        ->response()
                        ->setStatusCode(200);
                }
            }
        }

        $segment = $tripRepository->createRouteSegment(
            fromStation: $fromStation,
            toStation: $toStation,
            encodedPolyline: $encodedPolyline,
            duration: $duration,
            pathType: $pathType,
            distanceInMeters: $distanceInMeters,
            fromIdentifier: $fromIdentifier,
            toIdentifier: $toIdentifier,
        );

        if ($stopover !== null) {
            $tripRepository->setRouteSegmentForStop($stopover, $segment);
        }

        if ($fromIdentifier !== null && $toIdentifier !== null) {
            UpgradeRouteSegmentAssignments::dispatch($segment->id);
        }

        return new RouteSegmentResource($segment)
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Post(
        path: '/route-segments/{id}/assign-stopovers',
        operationId: 'assignRouteSegmentToStopovers',
        summary: 'Dispatch a background job that assigns this segment to all matching unassigned stopovers (admin only).',
        tags: ['Polyline'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 202, description: 'Job dispatched successfully.'),
            new OA\Response(response: 401, description: self::OA_DESC_UNAUTHENTICATED),
            new OA\Response(response: 403, description: self::OA_DESC_FORBIDDEN),
            new OA\Response(response: 404, description: self::OA_DESC_NOT_FOUND),
        ],
    )]
    #[OA\Delete(
        path: '/route-segments/{id}',
        operationId: 'deleteRouteSegment',
        summary: 'Delete a route segment (admin only). All stopovers using this segment are reassigned to another matching segment if available, otherwise their assignment is cleared.',
        tags: ['Polyline'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 202, description: 'Deletion job dispatched. Stopovers will be reassigned and the segment deleted in the background.'),
            new OA\Response(response: 401, description: self::OA_DESC_UNAUTHENTICATED),
            new OA\Response(response: 403, description: self::OA_DESC_FORBIDDEN),
            new OA\Response(response: 404, description: self::OA_DESC_NOT_FOUND),
        ],
    )]
    public function destroy(string $id): JsonResponse
    {
        $segment = RouteSegment::findOrFail($id);
        $this->authorize('delete', $segment);

        DeleteRouteSegment::dispatch($segment);

        return response()->json([], 202);
    }

    public function assignStopovers(string $id): JsonResponse
    {
        $segment = RouteSegment::findOrFail($id);
        $this->authorize('assignStopovers', $segment);

        AssignRouteSegmentToStopovers::dispatch($segment->id);

        return response()->json([], 202);
    }

    #[OA\Post(
        path: '/route-segments/{id}/brouter-preview',
        operationId: 'brouterPreviewRouteSegment',
        summary: 'Request a BRouter route preview for the given waypoints (admin only).',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['waypoints'],
                properties: [
                    new OA\Property(
                        property: 'waypoints',
                        type: 'array',
                        items: new OA\Items(
                            required: ['lat', 'lng'],
                            properties: [
                                new OA\Property(property: 'lat', type: 'number'),
                                new OA\Property(property: 'lng', type: 'number'),
                            ],
                            type: 'object',
                        ),
                    ),
                    new OA\Property(property: 'path_type', type: 'string', nullable: true),
                ],
            ),
        ),
        tags: ['Polyline'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'BRouter coordinates and distance.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'coordinates',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'lat', type: 'number'),
                                    new OA\Property(property: 'lng', type: 'number'),
                                ],
                                type: 'object',
                            ),
                        ),
                        new OA\Property(property: 'distance', description: 'Distance in meters', type: 'integer'),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: self::OA_DESC_UNAUTHENTICATED),
            new OA\Response(response: 403, description: self::OA_DESC_FORBIDDEN),
            new OA\Response(response: 404, description: self::OA_DESC_NOT_FOUND),
            new OA\Response(response: 422, description: self::OA_DESC_UNPROCESSABLE),
        ],
    )]
    public function brouterPreview(Request $request, string $id, BRouterService $brouter): JsonResponse
    {
        $segment = RouteSegment::findOrFail($id);
        $this->authorize('update', $segment);

        $validated = $request->validate([
            'waypoints' => ['required', 'array', 'min:2'],
            'waypoints.*.lat' => ['required', 'numeric'],
            'waypoints.*.lng' => ['required', 'numeric'],
            'path_type' => ['nullable', 'string'],
        ]);

        $waypoints = array_map(
            static fn (array $w) => new Coordinate((float) $w['lat'], (float) $w['lng']),
            $validated['waypoints'],
        );

        $pathType = isset($validated['path_type']) ? SegmentPathType::tryFrom($validated['path_type']) : null;
        $profile = $pathType?->getBRouterProfile() ?? BRouterProfile::RAIL;

        try {
            $route = $brouter->getRoute($waypoints, $profile);
        } catch (BRouterException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json([
            'coordinates' => array_map(
                static fn (Coordinate $c) => ['lat' => $c->latitude, 'lng' => $c->longitude],
                $route->coordinates,
            ),
            'distance' => $route->distanceInMeters,
        ]);
    }

    #[OA\Post(
        path: '/route-segments/{id}/polyline',
        operationId: 'applyPolylineToRouteSegment',
        summary: 'Save custom waypoints and regenerate the segment\'s polyline via BRouter (admin only).',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['waypoints'],
                properties: [
                    new OA\Property(
                        property: 'waypoints',
                        type: 'array',
                        items: new OA\Items(
                            required: ['lat', 'lng'],
                            properties: [
                                new OA\Property(property: 'lat', type: 'number'),
                                new OA\Property(property: 'lng', type: 'number'),
                            ],
                            type: 'object',
                        ),
                    ),
                ],
            ),
        ),
        tags: ['Polyline'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Segment updated successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'polyline', type: 'string'),
                        new OA\Property(property: 'distance', description: 'Distance in meters', type: 'integer'),
                        new OA\Property(
                            property: 'customWaypoints',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'lat', type: 'number'),
                                    new OA\Property(property: 'lng', type: 'number'),
                                ],
                                type: 'object',
                            ),
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: self::OA_DESC_UNAUTHENTICATED),
            new OA\Response(response: 403, description: self::OA_DESC_FORBIDDEN),
            new OA\Response(response: 404, description: self::OA_DESC_NOT_FOUND),
            new OA\Response(response: 422, description: self::OA_DESC_UNPROCESSABLE),
        ],
    )]
    public function applyPolyline(Request $request, string $id, BRouterService $brouter): JsonResponse
    {
        $segment = RouteSegment::findOrFail($id);
        $this->authorize('update', $segment);

        $validated = $request->validate([
            'waypoints' => ['required', 'array', 'min:2'],
            'waypoints.*.lat' => ['required', 'numeric'],
            'waypoints.*.lng' => ['required', 'numeric'],
        ]);

        $waypointDtos = array_map(
            static fn (array $w) => new Coordinate((float) $w['lat'], (float) $w['lng']),
            $validated['waypoints'],
        );

        $pathType = $segment->path_type !== null ? SegmentPathType::tryFrom($segment->path_type) : null;
        $profile = $pathType?->getBRouterProfile() ?? BRouterProfile::RAIL;

        try {
            $route = $brouter->getRoute($waypointDtos, $profile);
        } catch (BRouterException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        $transcoder = new PolylineTranscoder();
        $encodedPolyline = $transcoder->encodePolyline(
            array_map(static fn (Coordinate $c) => [$c->longitude, $c->latitude], $route->coordinates),
            5,
        );

        $customWaypoints = array_map(
            static fn (Coordinate $c) => ['lat' => $c->latitude, 'lng' => $c->longitude],
            $waypointDtos,
        );

        $segment->update([
            'polyline' => $encodedPolyline,
            'polyline_precision' => 5,
            'distance' => $route->distanceInMeters,
            'custom_waypoints' => $customWaypoints,
        ]);

        return response()->json([
            'polyline' => $encodedPolyline,
            'distance' => $route->distanceInMeters,
            'customWaypoints' => $customWaypoints,
        ]);
    }

    /**
     * Return the stopover that immediately follows the given one in the same trip.
     * (ordered by arrival_planned, then departure_planned).
     */
    private function findNextStopover(Stopover $stopover): ?Stopover
    {
        $stopovers = $stopover->trip->stopovers;
        $index = $stopovers->search(fn (Stopover $s) => $s->id === $stopover->id);

        if ($index === false) {
            return null;
        }

        return $stopovers->get($index + 1);
    }

    /**
     * Derive the segment duration in seconds from a consecutive stopover pair.
     */
    private function deriveDurationFromPair(Stopover $from, Stopover $to): ?int
    {
        $startTime = $from->departure_planned ?? $from->arrival_planned;
        $endTime = $to->arrival_planned ?? $to->departure_planned;

        if ($startTime === null || $endTime === null || !$startTime->isValid() || !$endTime->isValid()) {
            return null;
        }

        return (int) round($startTime->diffInSeconds($endTime));
    }
}
