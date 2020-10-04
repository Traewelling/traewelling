<?php

namespace App\Http\Controllers;

use App\Exceptions\ShouldDeleteNotificationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class NotificationController extends Controller
{
    public static function latest()
    {
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
            ->filter(function($notificationOrNull) { return $notificationOrNull != null; })
            ->values();
    }

    public function renderLatest()
    {
        return Auth::user()->notifications
            ->take(10)
            ->map(function($notification) {
                $notification->html = $notification->type::render($notification);

                if ($notification->html != null) {
                    return $notification;
                }
                return null;
            })
            // We don't need empty notifications
            ->filter(function($notificationOrNull) { return $notificationOrNull != null; })
            ->values();
    }

    public static function toggleReadState($notificationId)
    {
        $notification = Auth::user()->notifications->where('id', $notificationId)->first();

        // Might have cached the html property and would then try to shove it in the DB, mostly
        // happened during tests.
        if(isset($notification->html)) {
            unset($notification->html);
        }

        if($notification->read_at == null) { // old state = unread
            $notification->markAsRead();
            return Response::json($notification, 201); // new state = read, 201=created
        } else { // old state = read
            $notification->markAsUnread();
            return Response::json($notification, 202); // new state = unread, 202=accepted
        }
    }
    public static function destroy($notificationID)
    {
        try {
            $notification = Auth::user()->notifications->where('id', $notificationID)->first();
            $notification->delete();
        } catch (\Throwable $e) {
            return Response::json([], 404);
        }

        return Response::json($notification, 200);
    }

    public function readAll()
    {
        Auth::user()->unreadNotifications->markAsRead();
    }
}
