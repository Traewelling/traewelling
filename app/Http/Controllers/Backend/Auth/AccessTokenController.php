<?php

namespace App\Http\Controllers\Backend\Auth;

use App\Http\Controllers\Backend\WebhookController;
use GuzzleHttp\Psr7\Utils as Psr7Utils;
use Laravel\Passport\Exceptions\OAuthServerException;
use Laravel\Passport\Http\Controllers\AccessTokenController as PassportAccessTokenController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Response;

class AccessTokenController extends PassportAccessTokenController
{
    /**
     * @throws OAuthServerException
     */
    public function issueToken(ServerRequestInterface $requestInterface, ResponseInterface $psrResponse): Response
    {
        return $this->withErrorHandling(function () use ($requestInterface, $psrResponse) {
            return $this->convertResponse(
                $this->extendResponseWithWebhookData(
                    $requestInterface,
                    $this->server->respondToAccessTokenRequest($requestInterface, $psrResponse)
                )
            );
        });
    }

    protected function extendResponseWithWebhookData(ServerRequestInterface $requestInterface, ResponseInterface $response): ResponseInterface
    {
        // Skip webhook stuff on error
        if ($response->getStatusCode() > 299 || $response->getStatusCode() < 200) {
            return $response;
        }
        $body = $requestInterface->getParsedBody();
        // Only create webhook on authorization code grant type.
        if ($body['grant_type'] != 'authorization_code') {
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

        $webhook = WebhookController::createWebhook($request);
        $body = $response->getBody();
        $data = json_decode($body, true);
        $data['webhook'] = [
            'id' => $webhook->id,
            'secret' => $webhook->secret,
            'url' => $webhook->url,
        ];

        return $response->withBody(Psr7Utils::streamFor((string) json_encode($data)));
    }
}
