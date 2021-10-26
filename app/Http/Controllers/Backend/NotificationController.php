<?php

namespace App\Http\Controllers\Backend;

use App\Exceptions\ShouldDeleteNotificationException;
use App\Http\Controllers\Controller;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * @param $notificationId
     *
     * @return DatabaseNotification
     * @api v1
     */
    public static function toggleReadState($notificationId): DatabaseNotification {
        $notification = Auth::user()->notifications->where('id', $notificationId)->first();

        if ($notification->read_at === null) {
            $notification->markAsRead();
            return $notification;
        }

        $notification->markAsUnread();
        return $notification;
    }

    /**
     * Show all 20 latest notifications
     *
     * @api v1
     */
    public static function latest(): DatabaseNotificationCollection {
        return Auth::user()->notifications
            ->take(20)->map(function($notification) {
                try {
                    $notification->type::detail($notification);
                    return $notification;
                } catch (ShouldDeleteNotificationException) {
                    $notification->delete();
                    return null;
                }
            })
            // We don't need empty notifications
            ->filter(function($notificationOrNull) {
                return $notificationOrNull !== null;
            })
            ->values();
    }

    /**
     * mark all notifications as read
     *
     * @api v1
     */
    public static function readAll(): void {
        Auth::user()->unreadNotifications->markAsRead();
    }

    /**
     * Return current unread notifications count
     * @return int
     */
    public static function count(): int {
        return Auth::user()->unreadNotifications->count();
    }
}
