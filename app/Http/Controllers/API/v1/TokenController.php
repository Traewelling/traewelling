<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Backend\User\TokenController as BackendTokenController;
use App\Http\Resources\TokenResource;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class TokenController extends Controller
{
    #[OA\Get(
        path: '/v1/security/tokens',
        operationId: 'getTokens',
        description: 'Get all active API tokens for the authenticated user',
        summary: 'Get active API tokens',
        security: [['passport' => ['scope:extra-terminate-sessions']]],
        tags: ['Security'],
        responses: [
            new OA\Response(
                response: 200,
                description: Controller::OA_DESC_SUCCESS,
                content: new OA\JsonContent(
                    type: 'object',
                    required: ['data'],
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/TokenResource')),
                    ]
                ),
            ),
        ]
    )]
    public function index(): AnonymousResourceCollection
    {
        return TokenResource::collection(BackendTokenController::index(user: auth()->user()));
    }

    #[OA\Post(
        path: '/v1/security/tokens',
        operationId: 'createToken',
        description: 'Create a new API token for the authenticated user. Requires a personal access token, third-party OAuth application tokens are not accepted.',
        summary: 'Create API token',
        security: [['token' => []]],
        tags: ['Security'],
        responses: [
            new OA\Response(
                response: 201,
                description: Controller::OA_DESC_SUCCESS,
                content: new OA\JsonContent(
                    type: 'object',
                    required: ['data'],
                    properties: [
                        new OA\Property(property: 'data', type: 'object', required: ['token'], properties: [
                            new OA\Property(property: 'token', description: 'The newly created API token', type: 'string', example: 'abc123def456'),
                        ]),
                    ]
                ),
            ),
        ]
    )]
    public function createToken(Request $request): JsonResponse
    {
        $token = BackendTokenController::createPersonalAccessToken(auth()->user());

        return $this->sendResponse(['token' => $token], 201);
    }

    #[OA\Delete(
        path: '/v1/security/tokens/{tokenId}',
        operationId: 'revokeToken',
        description: 'Revoke a specific API token for the authenticated user',
        summary: 'Revoke API token',
        security: [['passport' => ['scope:extra-terminate-sessions']]],
        tags: ['Security'],
        parameters: [
            new OA\Parameter(
                name: 'tokenId',
                in: 'path',
                description: 'The ID of the token to revoke',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(response: 204, description: Controller::OA_DESC_NO_CONTENT),
            new OA\Response(response: 403, description: Controller::OA_DESC_FORBIDDEN),
            new OA\Response(response: 404, description: Controller::OA_DESC_NOT_FOUND),
        ]
    )]
    public function revokeToken(Request $request, string $tokenId): JsonResponse
    {
        try {
            BackendTokenController::revokeToken($tokenId, auth()->user());

            return $this->sendResponse(null, 204);
        } catch (AuthorizationException) {
            return $this->sendError(null, 403);
        }
    }

    #[OA\Delete(
        path: '/v1/security/tokens',
        operationId: 'revokeAllTokens',
        description: 'Revoke all API tokens for the authenticated user',
        summary: 'Revoke all API tokens',
        security: [['passport' => ['scope:extra-terminate-sessions']]],
        tags: ['Security'],
        responses: [new OA\Response(response: 204, description: Controller::OA_DESC_NO_CONTENT)]
    )]
    public function revokeAllTokens(): JsonResponse
    {
        BackendTokenController::revokeAllTokens(user: auth()->user());

        return $this->sendResponse(null, 204);
    }
}
