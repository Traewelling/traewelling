<?php

namespace App\Http\Middleware;

use App\Http\Controllers\API\v1\Controller;
use App\Services\PrivacyPolicyService;
use Closure;
use Illuminate\Http\Request;

class PrivacyPolicyInterceptionMiddleware extends Controller
{
    public function __construct(
        private readonly PrivacyPolicyService $privacyPolicyService,
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $agreement = $this->privacyPolicyService->getPrivacyPolicy();

        $user = auth()->user();
        if ($user === null) {
            return $next($request);
        }

        if ($agreement === null) {

            return $next($request);
        }

        $ack = $this->privacyPolicyService->hasUserAcceptedPolicy($user);

        if (!$ack) {
            $lastPolicy = $this->privacyPolicyService->getLastAcceptedPolicy($user);

            return $this->sendError(
                error: 'Privacy agreement not yet accepted!',
                code: 406,
                additional: [
                    'policy' => route(name: 'api.v1.getPrivacyPolicy'),
                    'validFrom' => $agreement->valid_at,
                    'acceptedAt' => $lastPolicy?->accepted_at,
                ]
            );
        }

        // Otherwise, just keep going.
        return $next($request);
    }
}
