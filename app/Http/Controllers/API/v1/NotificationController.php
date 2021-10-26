<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\API\ResponseController;
use App\Http\Controllers\Backend\NotificationController as NotificationBackend;
use App\Http\Controllers\NotificationController as oldNotificationBackend;
use App\Http\Resources\UserNotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationController extends ResponseController
{
    /**
     * Get the amount of (unread) messages
     * @return JsonResponse
     */
    public function count(): JsonResponse {
        return $this->sendv1Response(NotificationBackend::count());
    }

    /**
     * Get all latest Messages
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection {
        return UserNotificationResource::collection(NotificationBackend::latest());
    }

    /**
     * @param $notificationId
     *
     * @return UserNotificationResource
     */
    public function update($notificationId): UserNotificationResource {
        return new UserNotificationResource(NotificationBackend::toggleReadState($notificationId));
    }

    public function readAll(): AnonymousResourceCollection {
        NotificationBackend::readAll();
        return UserNotificationResource::collection(NotificationBackend::latest());
    }
}
