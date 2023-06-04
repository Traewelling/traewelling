<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class NotificationController extends Controller
{

    public static function toggleReadState($notificationId): JsonResponse {
        $notification = Auth::user()->notifications->where('id', $notificationId)->first();

        // Might have cached the html property and would then try to shove it in the DB, mostly
        // happened during tests.
        if (isset($notification->html)) {
            unset($notification->html);
        }

        if ($notification->read_at == null) { // old state = unread
            $notification->markAsRead();
            return Response::json($notification, 201); // new state = read, 201=created
        }

        // old state = read
        $notification->markAsUnread();
        return Response::json($notification, 202); // new state = unread, 202=accepted
    }

    public static function readAll(): void {
        Auth::user()->unreadNotifications->markAsRead();
    }
}
