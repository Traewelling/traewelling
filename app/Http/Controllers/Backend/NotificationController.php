<?php

namespace App\Http\Controllers\Backend;

use App\Exceptions\ShouldDeleteNotificationException;
use App\Http\Controllers\Controller;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

abstract class NotificationController extends Controller
{
    /**
     * @param DatabaseNotification $notification
     *
     * @return DatabaseNotification
     * @api v1
     */
    public static function toggleReadState(DatabaseNotification $notification): DatabaseNotification {
        if ($notification->read_at === null) {
            return self::readMessage($notification);
        }

        return self::unreadMessage($notification);
    }

    /**
     * @param DatabaseNotification $notification
     *
     * @return DatabaseNotification
     * @api v1
     */
    public static function readMessage(DatabaseNotification $notification): DatabaseNotification {
        $notification->markAsRead();
        return $notification;
    }

    /**
     * @param DatabaseNotification $notification
     *
     * @return DatabaseNotification
     * @api v1
     */
    public static function unreadMessage(DatabaseNotification $notification): DatabaseNotification {
        $notification->markAsUnread();
        return $notification;
    }

    /**
     * Show all 20 latest notifications
     *
     * @api v1
     */
    public static function latest(int $limit = 20) {
        return Auth::user()
                   ->notifications()
            //TODO: Delete statuses if status or user is deleted (maybe create another table for relations)
                   ->paginate($limit);
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
