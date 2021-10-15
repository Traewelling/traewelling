<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\API\ResponseController;
use App\Http\Controllers\Backend\NotificationController as NotificationBackend;
use App\Http\Resources\UserNotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\ItemNotFoundException;

class NotificationsController extends ResponseController
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
     * @param string $notificationId
     *
     * @return UserNotificationResource
     */
    public function update(string $notificationId): UserNotificationResource {
        try {
            return new UserNotificationResource(NotificationBackend::toggleReadState($notificationId));
        } catch (ItemNotFoundException) {
            abort(404);
        }
    }

    public function readAll(): AnonymousResourceCollection {
        NotificationBackend::readAll();
        return UserNotificationResource::collection(NotificationBackend::latest());
    }
}
