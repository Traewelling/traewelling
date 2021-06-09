<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\API\ResponseController;
use App\Http\Controllers\NotificationController as NotificationBackend;
use Illuminate\Http\JsonResponse;

class NotificationController extends ResponseController
{
    /**
     * Get the amount of (unread) messages
     * @return JsonResponse
     */
    public function count():JsonResponse {
        return $this->sendv1Response(NotificationBackend::count());
    }

    /**
     * Get all latest Messages
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse {
        $notificationResponse = NotificationBackend::latest();
        return $this->sendResponse($notificationResponse);
    }
}
