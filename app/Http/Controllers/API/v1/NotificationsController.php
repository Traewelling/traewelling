<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Backend\NotificationController as NotificationBackend;
use App\Http\Resources\UserNotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationsController extends Controller
{
    /**
     * Get the amount of (unread) messages
     * @return JsonResponse
     */
    public function getUnreadCount(): JsonResponse {
        return $this->sendResponse(NotificationBackend::count());
    }

    /**
     * Get all latest Messages
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse {
        return $this->sendResponse([
                                       'notifications' => UserNotificationResource::collection(Auth::user()->notifications()->simplePaginate()),
                                   ]);
    }

    /**
     * @param string $notificationId
     *
     * @return UserNotificationResource
     */
    public function update(string $notificationId): UserNotificationResource {
        $notification = Auth::user()->notifications()->where('id', $notificationId)->firstOrFail();
        return new UserNotificationResource(NotificationBackend::toggleReadState($notification));
    }

    /**
     * @param string $notificationId
     *
     * @return UserNotificationResource
     */
    public function read(string $notificationId): UserNotificationResource {
        $notification = Auth::user()->notifications()->where('id', $notificationId)->firstOrFail();
        return new UserNotificationResource(NotificationBackend::toggleReadState($notification));
    }

    /**
     * @param string $notificationId
     *
     * @return UserNotificationResource
     */
    public function unread(string $notificationId): UserNotificationResource {
        $notification = Auth::user()->notifications()->where('id', $notificationId)->firstOrFail();
        return new UserNotificationResource(NotificationBackend::toggleReadState($notification));

    }

    public function readAll(): JsonResponse {
        NotificationBackend::readAll();
        return $this->index();
    }
}
