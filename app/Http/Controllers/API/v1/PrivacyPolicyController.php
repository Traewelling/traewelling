<?php

namespace App\Http\Controllers\API\v1;

use App\Exceptions\AlreadyAcceptedException;
use App\Http\Resources\PrivacyPolicyResource;
use App\Services\PrivacyPolicyService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class PrivacyPolicyController extends Controller
{
    #[OA\Get(
        path: '/static/privacy',
        tags: ['Settings'],
        summary: 'Get the current privacy policy',
        description: 'Get the current privacy policy',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'validFrom',
                                    example: '2022-01-05T16:26:14.000000Z',
                                ),
                                new OA\Property(
                                    property: 'en',
                                    example: 'This is the english privacy policy',
                                ),
                                new OA\Property(
                                    property: 'de',
                                    example: 'Dies ist die deutsche Datenschutzerklärung',
                                ),
                            ],
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function getPrivacyPolicy(): PrivacyPolicyResource
    {
        return new PrivacyPolicyResource(PrivacyPolicyService::getCurrentPrivacyPolicy());
    }

    #[OA\Post(
        path: '/settings/acceptPrivacy',
        operationId: 'acceptPrivacyPolicy',
        tags: ['Settings'],
        summary: 'Accept the current privacy policy',
        description: 'Accept the current privacy policy',
        security: [['passport' => []], ['token' => []]],
        responses: [
            new OA\Response(response: 204, description: 'Success'),
            new OA\Response(response: 400, description: 'Already accepted'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ],
    )]
    public function acceptPrivacyPolicy(): JsonResponse
    {
        try {
            PrivacyPolicyService::acceptPrivacyPolicy(user: auth()->user());
        } catch (AlreadyAcceptedException $exception) {
            $error = strtr('User already accepted privacy policy (valid from ptime) at utime', [
                'ptime' => $exception->getPrivacyValidity(),
                'utime' => $exception->getUserAccepted(),
            ]);

            return $this->sendError(error: $error, code: 409);
        }

        return $this->sendResponse(code: 204);
    }
}
