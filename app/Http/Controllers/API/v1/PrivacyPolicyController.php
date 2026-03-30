<?php

namespace App\Http\Controllers\API\v1;

use App\Exceptions\AcceptingOldPrivacyPolicyException;
use App\Exceptions\AlreadyAcceptedException;
use App\Http\Resources\PrivacyPolicyResource;
use App\Services\PrivacyPolicyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PrivacyPolicyController extends Controller
{
    public function __construct(
        private readonly PrivacyPolicyService $privacyPolicyService
    ) {}

    #[OA\Get(
        path: '/privacy-policies/current',
        operationId: PrivacyPolicyController::class,
        description: 'Get the current privacy policy',
        summary: 'Get the current privacy policy',
        tags: ['Privacy Policy'],
        parameters: [
            new OA\Parameter(
                name: 'validAt',
                description: 'Get the privacy policy valid at a specific time (defaults to now)',
                in: 'query',
                required: false,
                example: '2022-01-05T16:26:14.000000Z',
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: PrivacyPolicyResource::class,
                            type: 'object',
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function getPrivacyPolicy(Request $request): PrivacyPolicyResource
    {
        $validated = $request->validate(['validAt' => 'date']);

        return new PrivacyPolicyResource($this->privacyPolicyService->getPrivacyPolicy($validated['validAt'] ?? null));
    }

    #[OA\Post(
        path: '/privacy-policies/accept',
        operationId: 'acceptPrivacyPolicy',
        description: 'Accept the current privacy policy',
        summary: 'Accept the current privacy policy',
        security: [['passport' => []], ['token' => []]],
        tags: ['Privacy Policy'],
        parameters: [
            new OA\Parameter(
                name: 'validAt',
                description: 'Get the privacy policy valid at a specific time (defaults to now)',
                in: 'query',
                required: false,
                example: '2022-01-05T16:26:14.000000Z',
            ),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Success'),
            new OA\Response(response: 400, description: 'Already accepted'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 409, description: 'Conflict - User already accepted privacy policy'),
        ],
    )]
    public function acceptPrivacyPolicy(Request $request): JsonResponse
    {
        $validated = $request->validate(['validAt' => 'date']);

        try {
            $this->privacyPolicyService->acceptPrivacyPolicy(user: auth()->user(), validAt: $validated['validAt'] ?? null);
        } catch (AlreadyAcceptedException $exception) {
            $error = strtr('User already accepted privacy policy (valid from ptime) at utime', [
                'ptime' => $exception->getPrivacyValidity(),
                'utime' => $exception->getUserAccepted(),
            ]);

            return $this->sendError(error: $error, code: 409);
        } catch (AcceptingOldPrivacyPolicyException $e) {
            $error = strtr('Trying to accept an obsolete privacy policy (old: otime, current: ntime)', [
                'otime' => $e->oldValidAt->toIso8601String(),
                'ntime' => $e->oldValidAt->toIso8601String(),
            ]);

            return $this->sendError(error: $error, code: 409);
        }

        return $this->sendResponse(code: 204);
    }
}
