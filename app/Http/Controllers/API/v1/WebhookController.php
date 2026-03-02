<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\API\v1\Controller as APIController;
use App\Http\Resources\WebhookResource;
use App\Models\Webhook;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class WebhookController extends Controller
{
    #[OA\Get(
        path: '/webhooks',
        operationId: 'getWebhooks',
        description: 'Returns all webhooks which are created for the current user and which the current authorized applicaton has access to.',
        summary: 'Get webhooks for current user and current application.',
        security: [['passport' => []], ['token' => []]],
        tags: ['Webhooks'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/WebhookResource'),
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ],
    )]
    public function index(): AnonymousResourceCollection
    {
        $currentClient = APIController::getCurrentOAuthClient();

        $query = Webhook::where('user_id', auth()->id());
        if ($currentClient !== null) { // null = Traewelling itself or personal access token
            $query->where('oauth_client_id', '=', $currentClient->id);
        }

        return WebhookResource::collection($query->get());
    }

    #[OA\Get(
        path: '/webhooks/{id}',
        operationId: 'getSingleWebhook',
        description: 'Returns a single webhook Object, if user and application is authorized to see it',
        summary: 'Get single webhook',
        security: [['passport' => []], ['token' => []]],
        tags: ['Webhooks'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Webhook-ID',
                in: 'path',
                schema: new OA\Schema(type: 'integer'),
                example: 1337,
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/WebhookResource')],
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(
                response: 404,
                description: 'No webhook found or unauthorized for this id',
            ),
        ],
    )]
    public function show(int $webhookId): WebhookResource|JsonResponse
    {
        $webhook = $this->getWebhookForUserAndCurrentClient($webhookId);
        if ($webhook == null) {
            return response()->json(null, 404);
        }

        return new WebhookResource($webhook);
    }

    #[OA\Delete(
        path: '/webhooks/{id}',
        operationId: 'deleteWebhook',
        description: '',
        summary: 'Delete a webhook if the user and application is authorized to do',
        security: [['passport' => []], ['token' => []]],
        tags: ['Webhooks'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Status-ID',
                in: 'path',
                schema: new OA\Schema(type: 'integer'),
                example: 1337,
            ),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Webhook deleted.'),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 404, description: 'No webhook found for this id'),
            new OA\Response(
                response: 403,
                description: 'User or application not authorized to delete this webhook',
            ),
        ],
    )]
    public function destroy(int $webhookId): JsonResponse
    {
        try {
            $webhook = $this->getWebhookForUserAndCurrentClient($webhookId);
            if ($webhook == null) {
                return response()->json(null, 404);
            }

            $this->authorize('delete', $webhook);
            $webhook->delete();

            return response()->json(null, 204);
        } catch (AuthorizationException) {
            return $this->sendError('You are not allowed to delete this webhook', 403);
        }
    }

    /**
     * @return Webhook|null null if not found or not authorized for client
     */
    private function getWebhookForUserAndCurrentClient(int $webhookId): ?Webhook
    {
        $currentClient = APIController::getCurrentOAuthClient();

        $query = Webhook::where('user_id', auth()->id());
        if ($currentClient !== null) { // null = Traewelling itself or personal access token
            $query->where('oauth_client_id', $currentClient->id);
        }

        return $query->where('id', '=', $webhookId)->first();
    }
}
