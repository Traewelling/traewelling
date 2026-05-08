<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1;

use App\Enum\StationIdentifierType;
use App\Models\Station;
use App\Repositories\StationRepository;
use App\Services\Checkin\StationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rules\Enum;
use OpenApi\Attributes as OA;

class StationIdentifierController extends Controller
{
    public function __construct(
        private StationRepository $stationRepository,
        private StationService $stationService,
    ) {}

    #[OA\Post(
        path: '/stations/{stationId}/identifiers',
        operationId: 'storeStationIdentifier',
        description: 'Admin only. Manually add an identifier to a station. The `origin` field will be set to `null`.',
        summary: 'Add a station identifier',
        security: [['passport' => ['*']], ['token' => []]],
        tags: ['Stations'],
        parameters: [
            new OA\Parameter(name: 'stationId', description: 'Station ID', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['type', 'identifier'],
                properties: [
                    new OA\Property(property: 'type', ref: '#/components/schemas/StationIdentifierType'),
                    new OA\Property(property: 'identifier', type: 'string', maxLength: 255, example: 'de:08212:1'),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 201, description: 'Identifier created'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Station not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function store(Request $request, int $stationId): Response|JsonResponse
    {
        $station = Station::findOrFail($stationId);
        $this->authorize('update', $station);

        $validated = $request->validate([
            'type' => ['required', new Enum(StationIdentifierType::class)],
            'identifier' => ['required', 'string', 'max:255'],
        ]);

        $this->stationService->createIdentifier(
            $station,
            StationIdentifierType::from($validated['type']),
            $validated['identifier'],
            $request->user(),
        );

        return response()->noContent(201);
    }

    #[OA\Patch(
        path: '/stations/{stationId}/identifiers/{identifierId}',
        operationId: 'updateStationIdentifier',
        description: 'Admin only. Update the type and value of an existing station identifier.',
        summary: 'Update a station identifier',
        security: [['passport' => ['*']], ['token' => []]],
        tags: ['Stations'],
        parameters: [
            new OA\Parameter(name: 'stationId', description: 'Station ID', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'identifierId', description: 'Identifier UUID', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['type', 'identifier'],
                properties: [
                    new OA\Property(property: 'type', ref: '#/components/schemas/StationIdentifierType'),
                    new OA\Property(property: 'identifier', type: 'string', maxLength: 255, example: 'de:08212:1'),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 204, description: 'Updated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Station or identifier not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function update(Request $request, int $stationId, string $identifierId): Response|JsonResponse
    {
        $station = Station::findOrFail($stationId);
        $this->authorize('update', $station);

        $validated = $request->validate([
            'type' => ['required', new Enum(StationIdentifierType::class)],
            'identifier' => ['required', 'string', 'max:255'],
        ]);

        $identifier = $this->stationRepository->getIdentifierForStation($identifierId, $stationId);
        if ($identifier === null) {
            return $this->sendError('Identifier not found for this station', 404);
        }

        $this->stationService->updateIdentifierValues(
            $identifier,
            StationIdentifierType::from($validated['type']),
            $validated['identifier'],
            $request->user(),
        );

        return response()->noContent();
    }

    #[OA\Put(
        path: '/stations/{stationId}/identifiers/{identifierId}/move',
        operationId: 'moveStationIdentifier',
        description: 'Admin only. Move a station identifier to a different station.',
        summary: 'Move a station identifier',
        security: [['passport' => ['*']], ['token' => []]],
        tags: ['Stations'],
        parameters: [
            new OA\Parameter(name: 'stationId', description: 'Source station ID', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'identifierId', description: 'Identifier UUID', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['target_station_id'],
                properties: [
                    new OA\Property(property: 'target_station_id', type: 'integer', example: 42),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 204, description: 'Moved'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Station or identifier not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function move(Request $request, int $stationId, string $identifierId): Response|JsonResponse
    {
        $station = Station::findOrFail($stationId);
        $this->authorize('update', $station);

        $validated = $request->validate([
            'target_station_id' => ['required', 'integer', 'exists:train_stations,id'],
        ]);

        $identifier = $this->stationRepository->getIdentifierForStation($identifierId, $stationId);
        if ($identifier === null) {
            return $this->sendError('Identifier not found for this station', 404);
        }

        $targetStation = Station::findOrFail($validated['target_station_id']);
        $this->authorize('update', $targetStation);

        $this->stationService->moveIdentifier($identifier, $targetStation, $request->user());

        return response()->noContent();
    }
}
