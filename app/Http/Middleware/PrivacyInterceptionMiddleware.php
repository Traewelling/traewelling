<?php

namespace App\Http\Middleware;

use App\PrivacyAgreement;
use Closure;
use Illuminate\Support\Facades\Auth;

class PrivacyInterceptionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        $agreement = PrivacyAgreement::where('valid_at', '<=', date("Y-m-d H:i:s"))->orderByDesc('valid_at')->first();

        // Wenn die letzte Ausführung neuer ist als das Ack, redirecte mich bitte.
        if($user->privacy_ack_at <= $agreement->valid_at) {
            return redirect()->route('gdpr.intercept');
        }

        // Sonst mach einfach weiter.
        return $next($request);
    }
}
