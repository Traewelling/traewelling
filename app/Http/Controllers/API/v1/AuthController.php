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
    properties: [
        new OA\Property(property: 'token', type: 'string', description: "Bearer Token. Use in Authentication-Header with prefix 'Bearer '. (space is needed)", example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...'),
        new OA\Property(property: 'expires_at', type: 'string', description: 'End of life for this token.', example: '2023-10-19T15:15:06+02:00'),
    ],
)]
class AuthController extends Controller
{
    /**
     * @api v1
     */
    #[OA\Post(
        path: '/auth/logout',
        operationId: 'logoutUser',
        tags: ['Auth'],
        summary: 'Logout & invalidate current bearer token',
        security: [['passport' => []], ['token' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
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
     * @return UserAuthResource
     *
     * @api v1
     */
    #[OA\Get(
        path: '/auth/user',
        operationId: 'getAuthenticatedUser',
        tags: ['Auth', 'User'],
        summary: 'Get authenticated user information',
        description: 'Get all profile information about the authenticated user',
        security: [['passport' => []], ['token' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            ref: '#/components/schemas/UserAuthResource',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ],
    )]
    public function user(Request $request)
    {
        return new UserAuthResource($request->user());
    }

    /**
     * @api v1
     */
    #[OA\Post(
        path: '/auth/refresh',
        operationId: 'refreshToken',
        tags: ['Auth'],
        summary: 'Refresh Bearer Token',
        description: 'This request issues a new Bearer-Token with a new expiration date while also revoking the old token.',
        security: [['passport' => []], ['token' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            ref: '#/components/schemas/BearerTokenResponse',
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
