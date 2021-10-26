<?php

namespace App\Http\Controllers\Backend;

use App\Exceptions\ShouldDeleteNotificationException;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * @param $notificationId
     *
     * @return
     * @api v1
     */
    public static function toggleReadState($notificationId) {
        $notification = Auth::user()->notifications->where('id', $notificationId)->first();


        if ($notification->read_at === null) {
            $notification->markAsRead();
            return $notification;
        }

        $notification->markAsUnread();
        return $notification;
    }

    /**
     * @api v1
     */
    public static function latest() {
        return Auth::user()->notifications
            ->take(10)->map(function($notification) {
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
}
