<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Resources\UserAuthResource;
use App\Providers\AuthServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'BearerTokenResponse',
    title: 'BearerTokenResponse',
    required: ['token', 'expires_at'],
    properties: [
        new OA\Property(property: 'token', description: "Bearer Token. Use in Authentication-Header with prefix 'Bearer '. (space is needed)", type: 'string', example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...'),
        new OA\Property(property: 'expires_at', description: 'End of life for this token.', type: 'string', example: '2023-10-19T15:15:06+02:00'),
    ],
)]
class AuthController extends Controller
{
    /**
     * @api v1
     */
    #[OA\Post(
        path: '/v1/auth/logout',
        operationId: 'logoutUser',
        summary: 'Logout & invalidate current bearer token',
        security: [['passport' => []], ['token' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    required: ['status'],
                    properties: [new OA\Property(property: 'status', example: 'success')],
                ),
            ),
            new OA\Response(response: 500, description: 'Error during revoke'),
        ],
    )]
    public function logout(Request $request): JsonResponse
    {
        $isUser = $request->user()->token()->revoke();
        if ($isUser) {
            return $this->sendResponse();
        }

        return $this->sendResponse('unknown', 500);
    }

    /**
     * @api v1
     */
    #[OA\Get(
        path: '/v1/auth/user',
        operationId: 'getAuthenticatedUser',
        description: 'Get all profile information about the authenticated user',
        summary: 'Get authenticated user information',
        security: [['passport' => []], ['token' => []]],
        tags: ['Auth', 'User'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/UserAuthResource',
                            type: 'object',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ],
    )]
    public function user(Request $request): UserAuthResource
    {
        $request->user()->loadMissing(['home', 'roles']);

        return new UserAuthResource($request->user());
    }

    /**
     * @api v1
     */
    #[OA\Post(
        path: '/v1/auth/refresh',
        operationId: 'refreshToken',
        description: 'This request issues a new Bearer-Token with a new expiration date while also revoking the old token.',
        summary: 'Refresh Bearer Token',
        security: [['passport' => []], ['token' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/BearerTokenResponse',
                            type: 'object',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ],
    )]
    public function refresh(Request $request): JsonResponse
    {
        $oldToken = $request->user()->token();
        $newToken = $request->user()->createToken('token', array_keys(AuthServiceProvider::$scopes));
        $oldToken->revoke();

        return $this->sendResponse([
            'token' => $newToken->accessToken,
            'expires_at' => $newToken->token->expires_at->toIso8601String(),
        ])
            ->header('Authorization', $newToken->accessToken);
    }
}
