<?php

namespace App\Http\Controllers;

use App\Models\PrivacyAgreement;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrivacyAgreementController extends Controller
{

    public function intercept(): Renderable {
        $agreement = PrivacyAgreement::where('valid_at', '<=', date("Y-m-d H:i:s"))
            ->orderByDesc('valid_at')
            ->take(1)
            ->first();
        $user      = Auth::user();

        return view('privacy-interception', ['agreement' => $agreement, 'user' => $user]);
    }

    /**
     * Accept privacy
     * Accepts the current privacy agreement
     *
     * @group User management
     *
     * @response 200 {
     * "message": "privacy agreement successfully accepted"
     * }
     * @response 400 scenario="Bad Request The parameters are wrong or not given." <<>>
     * @response 401 scenario="Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied." <<>>
     *
     * @param Request $request
     * @return RedirectResponse|JsonResponse
     */
    public function ack(Request $request): RedirectResponse|JsonResponse {
        $user                 = Auth::user();
        $user->privacy_ack_at = now();
        $user->save();
        if ($request->is('api*')) {
            return response()->json(['message' => 'privacy agreement successfully accepted'], 202);
        }

        return redirect()->route('dashboard');
    }
}
