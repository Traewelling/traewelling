<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Resources\SessionResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Jenssegers\Agent\Agent;

class SessionController extends Controller
{
    public static function index(User $user): AnonymousResourceCollection {
        $sessions = $user->sessions->map(function($session) {
            $result = new Agent();
            $result->setUserAgent($session->user_agent);
            $session->platform = $result->platform();

            if ($result->isphone()) {
                $session->device_icon = 'mobile-alt';
            } elseif ($result->isTablet()) {
                $session->device_icon = 'tablet';
            } else {
                $session->device_icon = 'desktop';
            }
            return $session;
        });
        return SessionResource::collection($sessions);
    }

    public static function deleteAllSessionsFor(User $user): void {
        foreach ($user->sessions as $session) {
            $session->delete();
        }
    }
}
