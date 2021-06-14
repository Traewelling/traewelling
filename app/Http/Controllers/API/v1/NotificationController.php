<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\API\ResponseController;
use App\Http\Controllers\NotificationController as NotificationBackend;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
     * @TODO make this json-only (remove render)
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse {
        if ($request->get('render')) {
            $notificationResponse = NotificationBackend::renderLatest();
        } else {
            $notificationResponse = NotificationBackend::latest();
        }
        return $this->sendv1Response($notificationResponse);
    }
}
