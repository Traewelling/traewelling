<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1;

use App\Http\Requests\StoreOAuthClientRequest;
use App\Http\Resources\OAuthClientResource;
use App\Http\Resources\WebhookStatsResource;
use App\Repositories\OAuthClientRepository;
use App\Services\OAuth\ApplicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Laravel\Passport\ClientRepository;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Applications', description: 'Manage OAuth applications')]
class ApplicationController extends Controller
{
    public function __construct(
        private readonly ApplicationService $applicationService,
        private readonly OAuthClientRepository $oauthClientRepository,
        private readonly ClientRepository $clientRepository,
    ) {}

    #[OA\Get(
        path: '/v1/applications',
        operationId: 'getApplications',
        description: 'Returns all OAuth applications owned by the authenticated user. Requires a personal access token, third-party OAuth application tokens are not accepted.',
        summary: 'List OAuth applications',
        security: [['token' => []]],
        tags: ['Applications'],
        responses: [
            new OA\Response(
                response: 200,
                description: self::OA_DESC_SUCCESS,
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: OAuthClientResource::class))],
                )
            ),
            new OA\Response(response: 401, description: self::OA_DESC_UNAUTHENTICATED),
        ],
    )]
    public function index(): AnonymousResourceCollection
    {
        return OAuthClientResource::collection(
            $this->applicationService->listForUser(auth()->user())
        );
    }

    #[OA\Post(
        path: '/v1/applications',
        operationId: 'createApplication',
        description: 'Create a new OAuth application for the authenticated user. Requires a personal access token — third-party OAuth application tokens are not accepted.',
        summary: 'Create OAuth application',
        security: [['token' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: StoreOAuthClientRequest::class)),
        tags: ['Applications'],
        responses: [
            new OA\Response(
                response: 201,
                description: self::OA_DESC_SUCCESS,
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [new OA\Property(property: 'data', ref: OAuthClientResource::class)]
                )
            ),
            new OA\Response(response: 401, description: self::OA_DESC_UNAUTHENTICATED),
            new OA\Response(response: 422, description: self::OA_DESC_UNPROCESSABLE),
        ],
    )]
    public function store(StoreOAuthClientRequest $request): JsonResponse
    {
        $data = $request->validated();
        $client = $this->oauthClientRepository->create(
            userId: auth()->id(),
            name: $data['name'],
            redirect: $data['redirect'],
            confidential: (bool) ($data['confidential'] ?? true),
            privacyPolicyUrl: $data['privacyPolicyUrl'] ?? null,
            webhooksEnabled: (bool) ($data['webhooksEnabled'] ?? false),
            authorizedWebhookUrl: $data['authorizedWebhookUrl'] ?? null,
        );

        return $this->sendResponse(new OAuthClientResource($client), 201);
    }

    #[OA\Put(
        path: '/v1/applications/{clientId}',
        operationId: 'updateApplication',
        description: 'Update an OAuth application owned by the authenticated user. Requires a personal access token, third-party OAuth application tokens are not accepted.',
        summary: 'Update OAuth application',
        security: [['token' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: StoreOAuthClientRequest::class)),
        tags: ['Applications'],
        parameters: [
            new OA\Parameter(name: 'clientId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: self::OA_DESC_SUCCESS,
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [new OA\Property(property: 'data', ref: OAuthClientResource::class)]
                )
            ),
            new OA\Response(response: 401, description: self::OA_DESC_UNAUTHENTICATED),
            new OA\Response(response: 404, description: self::OA_DESC_NOT_FOUND),
            new OA\Response(response: 422, description: self::OA_DESC_UNPROCESSABLE),
        ],
    )]
    public function update(StoreOAuthClientRequest $request, int $clientId): JsonResponse
    {
        $client = $this->oauthClientRepository->findForUser($clientId, auth()->user());
        if ($client === null) {
            return $this->sendError(null, 404);
        }

        $data = $request->validated();
        $webhooksEnabled = (bool) ($data['webhooksEnabled'] ?? false);

        return $this->sendResponse(
            new OAuthClientResource($this->oauthClientRepository->update(
                $client,
                $data['name'],
                $data['redirect'],
                $data['privacyPolicyUrl'] ?? null,
                $webhooksEnabled || $this->oauthClientRepository->hasWebhooks($client->id),
                $data['authorizedWebhookUrl'] ?? null,
                (bool) ($data['confidential'] ?? true),
            ))
        );
    }

    #[OA\Delete(
        path: '/v1/applications/{clientId}',
        operationId: 'deleteApplication',
        description: 'Delete an OAuth application owned by the authenticated user. Requires a personal access token, third-party OAuth application tokens are not accepted.',
        summary: 'Delete OAuth application',
        security: [['token' => []]],
        tags: ['Applications'],
        parameters: [
            new OA\Parameter(name: 'clientId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: self::OA_DESC_NO_CONTENT),
            new OA\Response(response: 401, description: self::OA_DESC_UNAUTHENTICATED),
            new OA\Response(response: 404, description: self::OA_DESC_NOT_FOUND),
        ],
    )]
    public function destroy(int $clientId): JsonResponse
    {
        $client = $this->oauthClientRepository->findForUser($clientId, auth()->user());
        if ($client === null) {
            return $this->sendError(null, 404);
        }

        $this->clientRepository->delete($client);

        return response()->json(null, 204);
    }

    #[OA\Get(
        path: '/v1/applications/{clientId}/webhook-stats',
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
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [new OA\Property(property: 'data', ref: WebhookStatsResource::class)]
                )
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
