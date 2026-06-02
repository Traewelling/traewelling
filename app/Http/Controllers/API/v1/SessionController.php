<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Backend\User\SessionController as SessionBackend;
use App\Http\Resources\SessionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class SessionController extends Controller
{
    #[OA\Get(
        path: '/security/sessions',
        operationId: 'getSessions',
        description: 'Get all active sessions for the authenticated user',
        summary: 'Get active sessions',
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
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/SessionResource')),
                    ]
                ),
            ),
        ]
    )]
    public function index(): AnonymousResourceCollection
    {
        return SessionResource::collection(SessionBackend::index(user: auth()->user()));
    }

    #[OA\Delete(
        path: '/security/sessions',
        operationId: 'deleteAllSessions',
        description: 'Delete all active sessions for the authenticated user',
        summary: 'Delete all sessions',
        security: [['passport' => ['scope:extra-terminate-sessions']]],
        tags: ['Security'],
        responses: [new OA\Response(response: 204, description: Controller::OA_DESC_NO_CONTENT)]
    )]
    public function deleteAllSessions(): JsonResponse
    {
        SessionBackend::deleteAllSessionsFor(user: auth()->user());

        return $this->sendResponse(null, 204);
    }
}
