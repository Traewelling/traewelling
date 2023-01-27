<?php

namespace App\Http\Controllers\Backend\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Http\Controllers\AccessTokenController as ControllersAccessTokenController;
use Nyholm\Psr7\Response as Psr7Response;
use Psr\Http\Message\ServerRequestInterface;

class AccessTokenController extends ControllersAccessTokenController
{
    public function issueToken(ServerRequestInterface $requestInterface)
    {
        return $this->withErrorHandling(function () use ($requestInterface) {
            return $this->extendResponseWithWebhookData(
                $this->server->respondToAccessTokenRequest($requestInterface, new Psr7Response)
            );
        });
    }

    function extendResponseWithWebhookData(Psr7Response $response)
    {
        $body = $response->getBody();
        $data = json_decode($body, true);
        $data['webhook'] = ['url' => 'https://example.com', 'secret' => 'uwu', 'id' => '1'];
        return new Response(
            $data,
            $response->getStatusCode(),
            $response->getHeaders()
        );
    }
}
