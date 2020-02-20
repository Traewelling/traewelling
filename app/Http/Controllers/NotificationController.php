<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class NotificationController extends Controller {
    
    public function latest() {
        return Auth::user()->notifications
            ->take(10)
            ->map(function($notification) {
                $notification->html = $notification->type::render($notification);
                return $notification;
            });
    }
}
