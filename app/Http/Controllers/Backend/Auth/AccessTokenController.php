<?php

namespace App\Http\Controllers\Backend\Auth;

use App\Http\Controllers\Backend\WebhookController;
use Laravel\Passport\Exceptions\OAuthServerException;
use Laravel\Passport\Http\Controllers\AccessTokenController as PassportAccessTokenController;
use Nyholm\Psr7\Response as Psr7Response;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class AccessTokenController extends PassportAccessTokenController
{
    /**
     * @throws OAuthServerException
     */
    public function issueToken(ServerRequestInterface $requestInterface) {
        return $this->withErrorHandling(function() use ($requestInterface) {
            return $this->extendResponseWithWebhookData(
                $requestInterface,
                $this->server->respondToAccessTokenRequest($requestInterface, new Psr7Response)
            );
        });
    }

    protected function extendResponseWithWebhookData(ServerRequestInterface $requestInterface, Psr7Response $response): Psr7Response {
        // Skip webhook stuff on error
        if ($response->getStatusCode() > 299 || $response->getStatusCode() < 200) {
            return $response;
        }
        $body = $requestInterface->getParsedBody();
        // Only create webhook on authorization code grant type.
        if ($body['grant_type'] != 'authorization_code' ) {
            return $response;
        }

        $code = $body['code'];

        $request = WebhookController::findWebhookRequest($code);
        if ($request === null) {
            return $response;
        }

        if ($request->revoked || $request->isExpired()) {
            throw new BadRequestException('Webhook creation request has been revoked.', 419);
        }

        $webhook         = WebhookController::createWebhook($request);
        $body            = $response->getBody();
        $data            = json_decode($body, true);
        $data['webhook'] = [
            'id'     => $webhook->id,
            'secret' => $webhook->secret,
            'url'    => $webhook->url,
        ];
        return $response->withBody(Stream::create(json_encode($data)));
    }
}
