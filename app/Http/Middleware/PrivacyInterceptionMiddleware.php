<?php

namespace App\Http\Middleware;

use App\Services\PrivacyPolicyService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * used in frontend
 */
class PrivacyInterceptionMiddleware
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

        if ($agreement === null) {
            Log::critical('No privacy agreement found!');

            return $next($request);
        }

        // If the last execution is newer than the ack, please redirect me.
        $user = auth()->user();

        $ack = $this->privacyPolicyService->hasUserAcceptedPolicy($user);

        if (!$ack) {
            if ($request->is('api*')) {

                return response()->json(
                    data: [
                        'error' => 'Privacy agreement not yet accepted!',
                        'updated' => $agreement->valid_at,
                        'german' => $agreement->body_md_de,
                        'english' => $agreement->body_md_en,
                    ],
                    status: 406
                );
            }

            return redirect()->route('gdpr.intercept');
        }

        // Otherwise, just keep going.
        return $next($request);
    }
}
