<?php

namespace App\Http\Middleware;

use App\Models\PrivacyAgreement;
use Closure;
use Illuminate\Http\Request;
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
            if ($request->is('api*')) {
                $agreement = PrivacyAgreement::where('valid_at', '<=', date("Y-m-d H:i:s"))
                    ->orderByDesc('valid_at')->take(1)->first();
                return response()->json(['error'   => 'Privacy agreement not yet accepted!',
                                         'updated' => $agreement->valid_at,
                                         'german'  => $agreement->body_md_de,
                                         'english' => $agreement->body_md_en], 406);
            }
            return redirect()->route('gdpr.intercept');
        }

        // Sonst mach einfach weiter.
        return $next($request);
    }
}
