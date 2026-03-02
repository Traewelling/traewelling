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
        path: '/notifications/unread/count',
        operationId: 'getUnreadCount',
        tags: ['Notifications'],
        summary: 'Get count of unread notifications for authenticated user',
        description: 'Returns count of unread notifications of a authenticated user',
        security: [
            ['passport' => []],
            ['token' => []],
            ['passport' => ['read-notifications']],
            ['token' => []],
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
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
        path: '/notifications',
        operationId: 'listNotifications',
        tags: ['Notifications'],
        summary: 'Get paginated notifications for authenticated user',
        description: 'Returns paginated notifications of a authenticated',
        security: [
            ['passport' => []],
            ['token' => []],
            ['passport' => ['read-notifications']],
            ['token' => []],
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
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
        path: '/notifications/read/{id}',
        operationId: 'markAsRead',
        tags: ['Notifications'],
        summary: 'Mark notification as read',
        security: [['passport' => ['write-notifications']], ['token' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID of notification',
                example: 'cbf6054e-9c00-4b1f-ab37-7eb18ac8419f',
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            ref: '#/components/schemas/Notification',
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
        path: '/notifications/unread/{id}',
        operationId: 'markAsUnread',
        tags: ['Notifications'],
        summary: 'Mark notification as unread',
        security: [['passport' => ['write-notifications']], ['token' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID of notification',
                example: 'cbf6054e-9c00-4b1f-ab37-7eb18ac8419f',
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            ref: '#/components/schemas/Notification',
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
        path: '/notifications/read/all',
        operationId: 'markAllAsRead',
        tags: ['Notifications'],
        summary: 'Mark all notification as read',
        security: [['passport' => ['write-notifications']], ['token' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
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
