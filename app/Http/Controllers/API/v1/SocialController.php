<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class SocialController extends Controller
{
    private \App\Http\Controllers\Backend\Social\SocialController $backend;

    public function __construct(\App\Http\Controllers\Backend\Social\SocialController $backend)
    {
        parent::__construct();
        $this->backend = $backend;
    }

    #[OA\Delete(
        path: '/security/social',
        operationId: 'deleteSocialProvider',
        description: 'Delete a connected social provider from the authenticated user',
        summary: 'Delete social provider',
        security: [['passport' => ['write:projects', 'read:projects']]],
        tags: ['Security'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['provider'],
                properties: [
                    new OA\Property(
                        property: 'provider',
                        description: 'The social provider to delete',
                        type: 'string',
                        enum: ['mastodon'],
                    ),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 204, description: Controller::OA_DESC_NO_CONTENT),
            new OA\Response(response: 400, description: Controller::OA_DESC_BAD_REQUEST),
            new OA\Response(response: 404, description: Controller::OA_DESC_NOT_FOUND),
            new OA\Response(response: 406, description: Controller::OA_DESC_NOT_ACCEPTABLE),
        ]
    )]
    public function destroyProvider(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'provider' => ['required', Rule::in(['mastodon'])],
        ]);

        $user = auth()->user();

        if ($user->password === null && $user->socialProfile->mastodon_id === null) {
            return $this->sendError(__('controller.social.delete-set-password'), 406);
        }

        if ($user->email === null && $user->socialProfile->mastodon_id === null) {
            return $this->sendError(__('controller.social.delete-set-email'), 406);
        }

        if ($user->socialProfile === null) {
            return $this->sendError(__('controller.social.delete-never-connected'), 404);
        }

        if ($validated['provider'] === 'mastodon') {
            $this->backend->destroyMastodon($user);
        }

        return $this->sendResponse(null, 204);
    }
}
