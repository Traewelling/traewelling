<?php

namespace App\Http\Middleware;

use App\Models\PrivacyAgreement;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * used in frontend
 */
class PrivacyInterceptionMiddleware
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
        $agreement = PrivacyAgreement::where('valid_at', '<=', Carbon::now()->toIso8601String())
                                     ->orderByDesc('valid_at')
                                     ->first();

        if ($agreement === null) {
            Log::critical('No privacy agreement found!');
            return $next($request);
        }

        // If the last execution is newer than the ack, please redirect me.
        $user = auth()->user();
        if (is_null($user->privacy_ack_at) || $agreement->valid_at->isAfter($user->privacy_ack_at)) {
            if ($request->is('api*')) {
                $agreement = PrivacyAgreement::where('valid_at', '<=', Carbon::now()->toIso8601String())
                                             ->orderByDesc('valid_at')
                                             ->take(1)
                                             ->first();
                return response()->json(
                    data:   [
                                'error'   => 'Privacy agreement not yet accepted!',
                                'updated' => $agreement->valid_at,
                                'german'  => $agreement->body_md_de,
                                'english' => $agreement->body_md_en
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
