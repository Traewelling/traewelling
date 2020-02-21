<?php

namespace App\Http\Controllers;

use App\Exceptions\ShouldDeleteNotificationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class NotificationController extends Controller {
    
    public function latest() {
        return Auth::user()->notifications
            ->take(10)
            ->map(function($notification) {
                try {
                    $notification->html = $notification->type::render($notification);
                    return $notification;
                } catch (ShouldDeleteNotificationException $e) {
                    $notification->delete();
                    return null;
                }
            })
            // We don't need empty notifications
            ->filter(function($notification_or_null) { return $notification_or_null != null; });
    }
}
