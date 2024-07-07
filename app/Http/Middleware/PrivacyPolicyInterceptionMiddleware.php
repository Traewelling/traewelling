<?php

namespace App\Http\Middleware;

use App\Http\Controllers\API\v1\Controller;
use App\Models\PrivacyAgreement;
use App\Services\PrivacyPolicyService;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PrivacyPolicyInterceptionMiddleware extends Controller
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed {
        $agreement = PrivacyPolicyService::getCurrentPrivacyPolicy();

        $user = auth()->user();
        if ($user === null) {
            return $next($request);
        }

        if ($agreement === null) {
            Log::critical('No privacy agreement found!');
            return $next($request);
        }

        if (is_null($user->privacy_ack_at) || $agreement->valid_at->isAfter($user->privacy_ack_at)) {
            $agreement = PrivacyAgreement::where('valid_at', '<=', Carbon::now()->toIso8601String())
                                         ->orderByDesc('valid_at')
                                         ->take(1)
                                         ->first();
            return $this->sendError(
                error:      'Privacy agreement not yet accepted!',
                code:       406,
                additional: [
                                'policy'     => route(name: 'api.v1.getPrivacyPolicy'),
                                'validFrom'  => $agreement->valid_at,
                                'acceptedAt' => $user->privacy_ack_at
                            ]
            );
        }

        // Otherwise, just keep going.
        return $next($request);
    }
}
