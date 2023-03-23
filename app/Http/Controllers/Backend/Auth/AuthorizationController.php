<?php

namespace App\Http\Controllers\Backend\Auth;

use App\Models\OAuthClient;
use App\Models\Webhook;
use App\Repositories\OAuthClientRepository;
use App\Rules\AuthorizedWebhookURL;
use App\Rules\StringifiedWebhookEvents;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Http\Controllers\AuthorizationController as PassportAuthorizationController;
use Psr\Http\Message\ServerRequestInterface;
use Laravel\Passport\TokenRepository;
use Illuminate\Support\Str;
use League\OAuth2\Server\Exception\OAuthServerException;
use Spatie\ValidationRules\Rules\Delimited;

class AuthorizationController extends PassportAuthorizationController {
    // most of this is based on passports original code
    // see: https://github.com/laravel/passport/blob/11.x/src/Http/Controllers/AuthorizationController.php
    /**
     * Authorize a client to access the user's account.
     *
     * @param  \Psr\Http\Message\ServerRequestInterface  $psrRequest
     * @param  \Illuminate\Http\Request  $request
     * @param  \Laravel\Passport\ClientRepository  $clients
     * @param  \Laravel\Passport\TokenRepository  $tokens
     * @return \Illuminate\Http\Response
     */
    public function authorize(
        ServerRequestInterface $psrRequest,
        Request $request,
        ClientRepository $_,
        TokenRepository $tokens
    ) {
        $clients = new OAuthClientRepository();

        $authRequest = $this->withErrorHandling(function () use ($psrRequest) {
            return $this->server->validateAuthorizationRequest($psrRequest);
        });

        $client = $clients->find($authRequest->getClient()->getIdentifier());

        $webhook = $this->withErrorHandling(function () use ($request, $client) {
            return $this->parseWebhookExtensions($request, $client);
        });

        if ($this->guard->guest()) {
            return $request->get('prompt') === 'none'
                ? $this->denyRequest($authRequest)
                : $this->promptForLogin($request);
        }

        if (
            $request->get('prompt') === 'login' &&
            !$request->session()->get('promptedForLogin', false)
        ) {
            $this->guard->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return $this->promptForLogin($request);
        }

        $request->session()->forget('promptedForLogin');

        $scopes = $this->parseScopes($authRequest);
        $user = $request->user();

        if (
            $request->get('prompt') !== 'consent' &&
            $webhook == null &&
            ($client->skipsAuthorization() || $this->hasValidToken($tokens, $user, $client, $scopes))
        ) {
            return $this->approveRequest($authRequest, $user);
        }

        if ($request->get('prompt') === 'none') {
            return $this->denyRequest($authRequest, $user);
        }

        $request->session()->put('authToken', $authToken = Str::random());
        $request->session()->put('authRequest', $authRequest);
        $request->session()->put('webhook', $webhook);

        $userCount = $client->tokens()
            ->selectRaw('count(distinct user_id) as count')
            ->where('revoked', 0)
            ->union(
                Webhook::select('user_id')
                    ->where('oauth_client_id', $client->id)
            )->value('count');

        return response()->view('auth.authorize', [
            'client' => $client,
            'user' => $user,
            'scopes' => $scopes,
            'request' => $request,
            'authToken' => $authToken,
            'webhook' => $webhook,
            'userCount' => $userCount,
        ]);
    }

    function parseWebhookExtensions(Request $request, OAuthClient $client) {
        if (!$request->has('trwl_webhook_url') && !$request->has('trwl_webhook_events')) {
            return null;
        }

        if (!$client->webhooks_enabled) {
            throw new OAuthServerException(
                "Webhooks are not enabled. Please enable them in the application dashboard if you wish to use them.",
                3,
                'invalid_request',
                400,
                null,
                null
            );
        }

        $validator = Validator::make($request->all(), [
            'trwl_webhook_events' => ['required', new Delimited(new StringifiedWebhookEvents())],
            'trwl_webhook_url' => ['required', 'string', new AuthorizedWebhookURL($client)]
        ]);

        if ($validator->fails()) {
            $error = $validator->errors();
            throw new OAuthServerException($error, 3, 'invalid_request', 400, null, null);
        }
        $data = $validator->valid();
        $events = explode(',', $data['trwl_webhook_events']);
        return [
            'user' => $request->user(),
            'client' => $client,
            'url' => $data['trwl_webhook_url'],
            'events' => $events,
        ];
    }
}
