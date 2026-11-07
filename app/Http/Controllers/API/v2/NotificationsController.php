<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v2;

use App\Http\Controllers\Controller;
use App\Http\Resources\CursorPaginationMetaResource;
use App\Http\Resources\LinksResource;
use App\Http\Resources\UserNotificationResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class NotificationsController extends Controller
{
    #[OA\Get(
        path: '/v2/notifications',
        operationId: 'listNotificationsV2',
        summary: 'Get cursor paginated notifications for authenticated user',
        security: [['passport' => ['read-notifications']], ['token' => []]],
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(name: 'cursor', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    required: ['data', 'links', 'meta'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: UserNotificationResource::class),
                        ),
                        new OA\Property(property: 'links', ref: LinksResource::class),
                        new OA\Property(property: 'meta', ref: CursorPaginationMetaResource::class),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ],
    )]
    public function index(): AnonymousResourceCollection
    {
        return UserNotificationResource::collection(
            Auth::user()->notifications()->orderByDesc('id')->cursorPaginate(15)
        );
    }
}
