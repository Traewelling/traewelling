<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1;

use App\Http\Resources\WebhookStatsResource;
use App\Services\OAuth\ApplicationService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Applications', description: 'Manage OAuth applications')]
class ApplicationController extends Controller
{
    public function __construct(
        private readonly ApplicationService $applicationService,
    ) {}

    #[OA\Get(
        path: '/applications/{clientId}/webhook-stats',
        operationId: 'getApplicationWebhookStats',
        description: 'Returns webhook call log statistics for the last 7 days for a given OAuth application. Only the application owner or admins can access it.',
        summary: 'Get webhook call statistics for an application',
        security: [['passport' => []], ['token' => []]],
        tags: ['Applications'],
        parameters: [
            new OA\Parameter(
                name: 'clientId',
                description: 'OAuth Client ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: self::OA_DESC_SUCCESS,
                content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: WebhookStatsResource::class)])
            ),
            new OA\Response(response: 401, description: self::OA_DESC_UNAUTHENTICATED),
            new OA\Response(response: 404, description: self::OA_DESC_NOT_FOUND),
        ],
    )]
    public function webhookStats(int $clientId): WebhookStatsResource|JsonResponse
    {
        $client = $this->applicationService->findForUserOrAdmin($clientId, auth()->user());
        if ($client === null) {
            return response()->json(null, 404);
        }

        return new WebhookStatsResource($this->applicationService->getWebhookStats($client));
    }
}
