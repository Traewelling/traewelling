<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Resources\UserNotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class NotificationsController extends Controller
{
    #[OA\Get(
        path: '/v1/notifications/unread/count',
        operationId: 'getUnreadCount',
        description: 'Returns count of unread notifications of a authenticated user',
        summary: 'Get count of unread notifications for authenticated user',
        security: [
            ['passport' => []],
            ['token' => []],
            ['passport' => ['read-notifications']],
            ['token' => []],
        ],
        tags: ['Notifications'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [new OA\Property(property: 'data', type: 'integer', example: 2)],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ],
    )]
    public function getUnreadCount(): JsonResponse
    {
        return $this->sendResponse(Auth::user()->unreadNotifications->count());
    }

    #[OA\Get(
        path: '/v1/notifications',
        operationId: 'listNotifications',
        description: 'Returns paginated notifications of a authenticated',
        summary: 'Get paginated notifications for authenticated user',
        security: [
            ['passport' => []],
            ['token' => []],
            ['passport' => ['read-notifications']],
            ['token' => []],
        ],
        tags: ['Notifications'],
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
                            items: new OA\Items(ref: '#/components/schemas/Notification'),
                        ),
                        new OA\Property(property: 'links', ref: '#/components/schemas/Links'),
                        new OA\Property(
                            property: 'meta',
                            ref: '#/components/schemas/PaginationMeta',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ],
    )]
    public function listNotifications(): AnonymousResourceCollection
    {
        return UserNotificationResource::collection(Auth::user()->notifications()->simplePaginate(15));
    }

    #[OA\Put(
        path: '/v1/notifications/read/{id}',
        operationId: 'markAsRead',
        summary: 'Mark notification as read',
        security: [['passport' => ['write-notifications']], ['token' => []]],
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID of notification',
                in: 'path',
                schema: new OA\Schema(type: 'string'),
                example: 'cbf6054e-9c00-4b1f-ab37-7eb18ac8419f',
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Notification',
                            type: 'object',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'Notification not found'),
        ],
    )]
    public function markAsRead(string $notificationId): JsonResponse
    {
        $notification = Auth::user()->notifications()->where('id', $notificationId)->first();

        if ($notification === null) {
            return $this->sendError();
        }

        $notification->markAsRead();

        return $this->sendResponse(new UserNotificationResource($notification));
    }

    #[OA\Put(
        path: '/v1/notifications/unread/{id}',
        operationId: 'markAsUnread',
        summary: 'Mark notification as unread',
        security: [['passport' => ['write-notifications']], ['token' => []]],
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID of notification',
                in: 'path',
                schema: new OA\Schema(type: 'string'),
                example: 'cbf6054e-9c00-4b1f-ab37-7eb18ac8419f',
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Notification',
                            type: 'object',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'Notification not found'),
        ],
    )]
    public function markAsUnread(string $notificationId): JsonResponse
    {
        $notification = Auth::user()->notifications()->where('id', $notificationId)->first();

        if ($notification === null) {
            return $this->sendError();
        }

        $notification->markAsUnread();

        return $this->sendResponse(new UserNotificationResource($notification));
    }

    #[OA\Put(
        path: '/v1/notifications/read/all',
        operationId: 'markAllAsRead',
        summary: 'Mark all notification as read',
        security: [['passport' => ['write-notifications']], ['token' => []]],
        tags: ['Notifications'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    required: ['status'],
                    properties: [new OA\Property(property: 'status', type: 'string', example: 'success')],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ],
    )]
    public function markAllAsRead(): JsonResponse
    {
        Auth::user()->unreadNotifications->markAsRead();

        return $this->sendResponse();
    }
}
