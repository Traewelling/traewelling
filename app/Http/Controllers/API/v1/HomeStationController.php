<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1;

use App\Http\Resources\StationResource;
use App\Repositories\StationRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class HomeStationController extends Controller
{
    public function __construct(private readonly StationRepository $stationRepository)
    {
        parent::__construct();
    }

    #[OA\Put(
        path: '/v1/station/{id}/home',
        operationId: 'setHomeStation',
        summary: 'Set a station as home station',
        security: [['passport' => ['create-statuses']], ['token' => []]],
        tags: ['Checkin'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'UUID of the station. The legacy Träwelling-ID is still accepted.',
                in: 'path',
                required: true,
                example: '00000000-0000-0000-0000-000000000000',
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: self::OA_DESC_SUCCESS,
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [new OA\Property(property: 'data', ref: StationResource::class)],
                    type: 'object',
                ),
            ),
            new OA\Response(response: 400, description: self::OA_DESC_BAD_REQUEST),
            new OA\Response(response: 401, description: self::OA_DESC_UNAUTHENTICATED),
            new OA\Response(response: 404, description: self::OA_DESC_NOT_FOUND),
            new OA\Response(response: 500, description: 'Unknown error'),
        ],
    )]
    public function setHome(string $stationId): JsonResponse
    {
        try {
            $station = $this->stationRepository->getByIdOrUuid($stationId);
            if ($station === null) {
                return $this->sendError('The station could not be found');
            }

            auth()->user()?->update([
                'home_id' => $station->id,
            ]);

            return $this->sendResponse(
                data: new StationResource($station),
            );
        } catch (Exception $exception) {
            report($exception);

            return $this->sendError('Unknown error', 500);
        }
    }

    #[OA\Delete(
        path: '/v1/station/home',
        operationId: 'deleteHomeStation',
        summary: 'Remove the home station of the authenticated user',
        security: [['passport' => ['create-statuses']], ['token' => []]],
        tags: ['Checkin'],
        responses: [
            new OA\Response(response: 204, description: self::OA_DESC_NO_CONTENT),
            new OA\Response(response: 401, description: self::OA_DESC_UNAUTHENTICATED),
            new OA\Response(response: 500, description: 'Unknown error'),
        ],
    )]
    public function deleteHome(): JsonResponse
    {
        try {
            auth()->user()?->update([
                'home_id' => null,
            ]);

            return $this->sendResponse(null, 204);
        } catch (Exception $exception) {
            report($exception);

            return $this->sendError('Unknown error', 500);
        }
    }
}
