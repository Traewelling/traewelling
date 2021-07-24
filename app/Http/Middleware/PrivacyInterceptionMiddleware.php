<?php

namespace App\Http\Middleware;

use App\Models\PrivacyAgreement;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PrivacyInterceptionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $agreement = PrivacyAgreement::where('valid_at', '<=', Carbon::now()->toIso8601String())
                                     ->orderByDesc('valid_at')
                                     ->first();

        if ($agreement == null) {
            Log::critical('No privacy agreement found!');
            return $next($request);
        }

        // If the last execution is newer than the ack, please redirect me.
        $user = auth()->user();
        if ($user->privacy_ack_at == null || $agreement->valid_at->isAfter($user->privacy_ack_at)) {
            if ($request->is('api*')) {
                $agreement = PrivacyAgreement::where('valid_at', '<=', Carbon::now()->toIso8601String())
                                             ->orderByDesc('valid_at')
                                             ->take(1)
                                             ->first();
                return response()->json([
                                            'error'   => 'Privacy agreement not yet accepted!',
                                            'updated' => $agreement->valid_at,
                                            'german'  => $agreement->body_md_de,
                                            'english' => $agreement->body_md_en
                                        ], 406);
            }
            return redirect()->route('gdpr.intercept');
        }

        // Otherwise, just keep going.
        return $next($request);
    }
}
