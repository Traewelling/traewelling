<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Backend\IcsTokenController as BackendIcsController;
use App\Http\Resources\IcsEntryResource;
use App\Models\IcsToken;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class IcsController extends Controller
{
    #[OA\Post(
        path: '/v1/ics-tokens',
        operationId: 'createIcsToken',
        description: 'Create a new ICS token for the authenticated user',
        summary: 'Create ICS token',
        security: [['oauth2_security_example' => ['write:projects', 'read:projects']]],
        tags: ['ICS Tokens'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(
                        property: 'name',
                        description: 'The name of the ICS token',
                        type: 'string',
                        example: 'My ICS Token',
                    ),
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Created - ICS token created successfully',
                content: new OA\JsonContent(
                    type: 'object',
                    required: ['data'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            description: 'The URL to access the ICS feed with the created token',
                            type: 'object',
                            required: ['url'],
                            properties: [
                                new OA\Property(
                                    property: 'url',
                                    description: 'The URL to access the ICS feed with the created token',
                                    type: 'string',
                                    format: 'uri',
                                    example: 'https://example.com/ics?user_id=1&token=abcd1234&limit=10000&from=2010-01-01&until=2030-12-31',
                                ),
                            ],
                        ),
                    ],
                )
            ),
            new OA\Response(response: 422, description: 'Unprocessable Entity - validation error'),
        ]
    )]
    public function createIcsToken(Request $request): JsonResponse
    {
        $validated = $request->validate(['name' => ['required', 'max:255']]);

        $icsToken = BackendIcsController::createIcsToken(user: auth()->user(), name: $validated['name']);

        return $this->sendResponse(
            data: [
                'url' => route('ics', [
                    'user_id' => $icsToken->user_id,
                    'token' => $icsToken->token,
                    'limit' => 10000,
                    'from' => '2010-01-01',
                    'until' => '2030-12-31',
                ]),
            ],
            code: 201,
        );
    }

    #[OA\Delete(
        path: '/v1/ics-tokens/{tokenId}',
        operationId: 'revokeIcsToken',
        description: 'Revoke an ICS token of the authenticated user',
        summary: 'Revoke ICS token',
        security: [['oauth2_security_example' => ['write:projects', 'read:projects']]],
        tags: ['ICS Tokens'],
        parameters: [
            new OA\Parameter(
                name: 'tokenId',
                in: 'path',
                description: 'The unique identifier of the ICS token to revoke',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 204, description: 'No Content - token revoked successfully'),
            new OA\Response(response: 404, description: 'Not Found - token not found or does not belong to the authenticated user'),
        ]
    )]
    public function revokeIcsToken(Request $request, int $tokenId): JsonResponse
    {
        try {
            BackendIcsController::revokeIcsToken(user: auth()->user(), tokenId: $tokenId);

            return $this->sendResponse(code: 204);
        } catch (ModelNotFoundException) {
            return $this->sendError();
        }
    }

    #[OA\Get(
        path: '/v1/ics-tokens',
        operationId: 'getIcsTokens',
        description: 'Get all ICS tokens of the authenticated user',
        summary: 'Get ICS tokens',
        security: [['oauth2_security_example' => ['write:projects', 'read:projects']]],
        tags: ['ICS Tokens'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'success',
                content: new OA\JsonContent(
                    type: 'object',
                    required: ['data'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            description: 'The list of ICS tokens belonging to the authenticated user',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/IcsEntryResource')
                        ),
                    ]
                )
            ),
        ]
    )]
    public function getIcsTokens(): AnonymousResourceCollection
    {
        return IcsEntryResource::collection(IcsToken::where('user_id', auth()->user()->id)->get());
    }
}
