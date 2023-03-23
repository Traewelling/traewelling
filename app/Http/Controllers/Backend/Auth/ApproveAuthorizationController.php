<?php

namespace App\Http\Controllers\Backend\Auth;

use App\Enum\WebhookEvent;
use App\Http\Controllers\Backend\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Http\Controllers\ApproveAuthorizationController as PassportApproveAuthorizationController;

class ApproveAuthorizationController extends PassportApproveAuthorizationController {
    public function approve(Request $request) {
        $webhook = $request->session()->get('webhook');
        $response = parent::approve($request);
        if ($webhook) {
            parse_str(parse_url($response->headers->get("Location"))["query"], $query);
            $code = $query['code'];
            $user = $webhook['user'];
            $client = $webhook['client'];
            $events = WebhookEvent::fromNames($webhook['events']);
            Log::debug("Creating a new webhook creation request", [
                'client_id' => $client->id,
                'user_id' => $user->id,
                'events' => $webhook['events'],
            ]);
            WebhookController::createWebhookRequest(
                $user,
                $client,
                $code,
                $webhook['url'],
                $events
            );
        }
        return $response;
    }
}
