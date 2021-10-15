<?php

namespace App\Http\Controllers;

use App\Exceptions\ShouldDeleteNotificationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Throwable;

/**
 * @deprecated Content will be moved to the backend/frontend/API packages soon, please don't add new functions here!
 */
class NotificationController extends Controller
{
    /**
     * @deprecated replaced with Backend/NotificationsController
     */
    public static function latest() {
        return Auth::user()->notifications
            ->take(10)->map(function($notification) {
                try {
                    $notification->type::detail($notification);
                    return $notification;
                } catch (ShouldDeleteNotificationException $e) {
                    $notification->delete();
                    return null;
                }
            })
            // We don't need empty notifications
            ->filter(function($notificationOrNull) {
                return $notificationOrNull != null;
            })
            ->values();
    }

    /**
     * @deprecated replaced with new functionality of latest()
     */
    public static function renderLatest(): Collection {
        return Auth::user()->notifications()
                   ->limit(10)
                   ->get()
                   ->map(function($notification) {
                       $notification->html = $notification->type::render($notification);

                       if ($notification->html != null) {
                           return collect([
                                              'notifiable_type' => $notification->notifiable_type,
                                              'notifiable_id'   => $notification->notifiable_id,
                                              'type'            => $notification->type,
                                              'html'            => $notification->html,
                                              'read_at'         => $notification->read_at,
                                          ]);
                       }
                       return null;
                   })
                   ->filter(function($notificationOrNull) {
                       // We don't need empty notifications
                       return $notificationOrNull != null;
                   })
                   ->values();
    }

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
        } else { // old state = read
            $notification->markAsUnread();
            return Response::json($notification, 202); // new state = unread, 202=accepted
        }
    }

    public static function destroy($notificationID): JsonResponse {
        try {
            $notification = Auth::user()->notifications->where('id', $notificationID)->first();
            $notification->delete();
        } catch (Throwable $e) {
            return Response::json([], 404);
        }

        return Response::json($notification, 200);
    }

    public static function readAll(): void {
        Auth::user()->unreadNotifications->markAsRead();
    }

    /**
     * @return int
     */
    public static function count(): int {
        return Auth::user()->notifications->count();
    }
}
